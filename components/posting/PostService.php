<?php

namespace app\components\posting;

use app\components\api\HttpStatuses;
use app\components\context\hashtag\exceptions\HashtagLimitException;
use app\components\kafka\KafkaSenderComponent;
use app\components\helpers\Misc;
use app\components\posting\exception\MusicTrackNotFoundException;
use app\domain\kafka\dto\BasePublishingPostDto;
use app\models\hashtag\Hashtag;
use app\repositories\MusicTrackRepository;
use yii\db\Exception;
use app\components\posting\dto\PostDto;
use app\components\posting\exception\AccessRestrictedException;
use app\components\posting\exception\FeedNotFoundException;
use app\repositories\FeedRepository;
use app\components\posting\dto\item\{ItemAudioDto, ItemMusicTrackDto, ItemVideoDto, PostItemDto};
use app\models\feed\Feed;
use app\models\audio\Audio;
use app\models\video\Video;
use app\models\post\Post;
use app\models\post\PostItem;

/**
 * Сервис для создания постов
 */
class PostService
{
    /* @var FeedRepository */
    private FeedRepository $feedRepository;
    private MusicTrackRepository $musicTrackRepository;
    private KafkaSenderComponent $kafkaSender;

    public function __construct(
        FeedRepository $feedRepository,
        MusicTrackRepository $musicTrackRepository,
        KafkaSenderComponent $kafkaSender
    ) {
        $this->feedRepository = $feedRepository;
        $this->musicTrackRepository = $musicTrackRepository;
        $this->kafkaSender = $kafkaSender;
    }

    /**
     * @param PostDto $dto
     * @param bool $hasNewFeed
     * @return Post|null
     * @throws \Exception
     */
    public function create(PostDto $dto, bool $hasNewFeed = false): ?Post
    {
        if ($hasNewFeed === false) {
            $feedDto = $this->feedRepository->getFeedForReadById($dto->getFeedId());
            if ($feedDto === null) {
                throw new FeedNotFoundException('Feed not found');
            }
            if ($feedDto->getUserId() !== $dto->getUserId()) {
                throw new AccessRestrictedException('Access restricted');
            }
            if ($feedDto->getType() === Feed::TYPE_NEWS) {
                throw new AccessRestrictedException('Posting at news feed restricted');
            }
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $post = new Post();
            $post->status = $dto->getStatus();
            $post->feed_id = $dto->getFeedId();
            $post->age = $dto->getAge();
            $post->is_photo = $dto->getIsPhoto();
            $post->create_date = $dto->getCreateDate();
            if ($dto->getPublishDate()) {
                $post->publish_date = $dto->getPublishDate();
            }
            if ($dto->getStatus() !== Post::STATUS_ACTIVE) {
                $post->publish_date = null;
            }
            $post->save();

            $textPost = '';
            foreach ($dto->getItems() as $itemDto) {
                $this->createItem($dto->getUserId(), $post->id, $itemDto, $dto);

                if ($itemDto instanceof ItemVideoDto) {
                    $textPost .= $itemDto->getName() . ' ' . $itemDto->getDescription();
                } else {
                    $textPost .= $itemDto->getText() . ' ';
                }
            }

            if (Hashtag::checkHashtagCount($textPost) === false) {
                throw new HashtagLimitException(
                    HttpStatuses::HTTP_BAD_REQUEST,
                    'Максимальное количество хэштегов: ' . Hashtag::HASHTAG_CONTENT_COUNT_MAX
                );
            }

            $transaction->commit();
        } catch (HashtagLimitException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        try {
            $publishPostDto = new BasePublishingPostDto(
                $post->id,
                $post->status,
                $post->feed_id,
                $post->feed->status,
                (bool) $post->feed->is_private,
            );
            $this->kafkaSender->addPublishingPost($publishPostDto);
        } catch (\Throwable $e) {
            \Yii::error([
                'message' => 'Error: notification about creation content no sent',
                'exception' => $e
            ]);
        }

        return $post; // TODO: Change AR model to DTO for responses
    }

    /**
     * Создание блока поста
     *
     * @param int $userId
     * @param int $postId
     * @param PostItemDto $itemDto
     * @param PostDto $postDTO
     * @return void
     * @throws Exception
     */
    private function createItem(int $userId, int $postId, PostItemDto $itemDto, PostDto $postDTO): void
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $postItem = new PostItem();

            $postItem->post_id = $postId;
            $postItem->position = $itemDto->getPosition();
            $postItem->type = $itemDto->getType();
            $postItem->param = $itemDto->getParam();
            $postItem->link = $itemDto->getLink();

            $postItem->text = Misc::prepareString($itemDto->getText());
            $postItem->extra = Misc::prepareString($itemDto->getExtra());

            $postItem->image = $itemDto->getImage();
            $postItem->width = $itemDto->getImageWidth();
            $postItem->height = $itemDto->getImageHeight();

            $postItem->image_original = $itemDto->getImageOriginal();
            $postItem->width_original = $itemDto->getWidthImageOriginal();
            $postItem->height_original = $itemDto->getHeightImageOriginal();

            if ($itemDto->getType() === PostItem::TYPE_IN_APP_MUSIC) {
                /** @var ItemMusicTrackDto $itemDto */
                if ($this->musicTrackRepository->findActiveById($itemDto->getTrackId()) === null) {
                    throw new MusicTrackNotFoundException('Track not found');
                }
                $postItem->music_track_id = $itemDto->getTrackId();
            }

            $postItem->save();

            switch ($itemDto->getType()) {
                case PostItem::TYPE_AUDIO:
                    /* @var ItemAudioDto $itemDto */
                    $this->saveAudio($itemDto, $postItem->id);
                    break;
                case PostItem::TYPE_VIDEO:
                    /* @var ItemVideoDto $itemDto */
                    $this->saveVideo($itemDto, $postItem->id, $userId, $postDTO);
                    break;
            }

            $transaction->commit();
        } catch (\RuntimeException $e) {
            $transaction->rollBack();
        }
    }

    /**
     * @param ItemAudioDto $audioDto
     * @param int $postItemId
     * @return void
     */
    private function saveAudio(ItemAudioDto $audioDto, int $postItemId): void
    {
        $audio = Audio::findOne(['upload_id' => $audioDto->getTranscodeResponse()->getTaskId()]);
        if (!$audio) {
            $audio = new Audio(
                $audioDto->getName(),
                $audioDto->getArtist(),
                $postItemId
            );
            $audio->upload_id = $audioDto->getTranscodeResponse()->getTaskId();
        }

        $audio->post_item_id = $postItemId;
        $audio->name = Misc::prepareString($audioDto->getName());
        $audio->artist = Misc::prepareString($audioDto->getArtist());
        $audio->public();

        $audio->save();
    }

    /**
     * @param ItemVideoDto $videoDto
     * @param int $postItemId
     * @param int $userId
     * @param PostDto $postDto
     * @return void
     * @throws Exception
     */
    private function saveVideo(ItemVideoDto $videoDto, int $postItemId, int $userId, PostDto $postDto): void
    {
        /** @var ?Video $video */
        $video = Video::findOne(['upload_id' => $videoDto->getTranscodeResponse()->getTaskId()]);

        if ($video === null) {
            // beforeSave()
            //      status = 0 (new)
            //      transcode_status = 0 (transcode_status_new)
            $video = new Video(
                $videoDto->getName(),
                $postItemId
            );
            $video->upload_id = $videoDto->getTranscodeResponse()->getTaskId();
            $video->transcode_status = Video::TRANSCODE_STATUS_NEW;
        }
        // видео из черновиков или отложенный со статусом - новый
        $isNewStatus =  $postDto->getStatus() === Post::STATUS_DRAFT || new \DateTime($postDto->getPublishDate()) > new \DateTime();
        $video->status = $isNewStatus ? Video::STATUS_NEW : Video::STATUS_PROCESSING;

        $transcodeStatus = (int) $videoDto->getTranscodeResponse()->getStatus();

        if ($video->status === Video::STATUS_NEW && !$isNewStatus) {
            $video->status = Video::STATUS_PROCESSING;
        }

        $video->updateStatusByTranscodeStatus($transcodeStatus);

        $video->type = Video::TYPE_USER;
        $video->user_id = $userId;
        $video->post_item_id = $postItemId;
        $video->name = $videoDto->getName();
        $video->description = $videoDto->getDescription()
            ? Misc::prepareString($videoDto->getDescription())
            : Misc::prepareString($video->description);
        if (!$isNewStatus) {
            $video->public();
        }

        $video->image = $videoDto->getTranscodeResponse()->getPreviewUrl();
        $video->height = $videoDto->getTranscodeResponse()->getPreviewHeight();
        $video->width = $videoDto->getTranscodeResponse()->getPreviewWidth();
        $video->image_original = $videoDto->getTranscodeResponse()->getOriginalUrl();
        $video->height_original = $videoDto->getTranscodeResponse()->getOriginalHeight();
        $video->width_original = $videoDto->getTranscodeResponse()->getOriginalWidth();

        $video->save();

        if ($videoDto->getTags()) {
            $video->updateTags($videoDto->getTags());
        }
    }
}
