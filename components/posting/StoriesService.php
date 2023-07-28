<?php

namespace app\components\posting;

use app\components\context\feed\prepared\PreparedFeedNodeWrapper;
use app\components\posting\exception\StoriesBaseException;
use app\components\posting\jobs\StoryCreateJob;
use app\components\posting\jobs\StoryRemoveFeedJob;
use app\components\posting\jobs\StoryUpdateJob;
use app\components\posting\jobs\StoryViewedJob;
use app\domain\stories\dataProviders\StoriesDataProvider;
use app\domain\stories\dto\StoriesDto;
use Yii;
use yii\queue\sync\Queue;

class StoriesService
{
    public StoriesDataProvider $dataProvider;
    private Queue $storiesQueue;

    private array $listContentType = [
        PreparedFeedNodeWrapper::TYPE_CLIP,
        PreparedFeedNodeWrapper::TYPE_VIDEO,
        PreparedFeedNodeWrapper::TYPE_POST,
        PreparedFeedNodeWrapper::TYPE_NEW_EVENT,
    ];

    /**
     * @param StoriesDataProvider $storiesDataProvider
     * @param Queue $storiesQueue
     */
    public function __construct(StoriesDataProvider $storiesDataProvider, Queue $storiesQueue)
    {
        $this->dataProvider = $storiesDataProvider;
        $this->storiesQueue = $storiesQueue;
    }

    /**
     * Добавление задачи на добавление сторис
     *
     * @param StoriesDto $storiesDto
     * @return void
     */
    public function add(StoriesDto $storiesDto): void
    {
        try {
            $this->checkContentFields($storiesDto);

            $this->storiesQueue->push(
                new StoryCreateJob(
                    [
                        'contentId' => $storiesDto->getContentId(),
                        'contentType' => $storiesDto->getContentType(),
                        'contentOwnerUserId' => $storiesDto->getContentOwnerUserId(),
                        'contentFeedId' => $storiesDto->getContentFeedId(),
                    ]
                ),
            );
        } catch (\Exception $e) {
            Yii::error(
                [
                    'message' => 'Error create StoryCreateJob',
                    'exception' => $e,
                ],
                'stories',
            );
        }
    }

    /**
     * Добавление задачи при редактирование контента
     * При редактировании контента, можно добавлять или удалять его из сторис
     *
     * @param StoriesDto $storiesDto
     * @param bool $isStory
     * @return void
     */
    public function update(StoriesDto $storiesDto, bool $isStory): void
    {
        try {
            $this->checkContentFields($storiesDto);

            $this->storiesQueue->push(
                new StoryUpdateJob(
                    [
                        'contentId' => $storiesDto->getContentId(),
                        'contentType' => $storiesDto->getContentType(),
                        'contentOwnerUserId' => $storiesDto->getContentOwnerUserId(),
                        'contentFeedId' => $storiesDto->getContentFeedId(),
                        'isStory' => $isStory,
                    ]
                ),
            );
        } catch (\Exception $e) {
            Yii::error('Error create StoryUpdateJob (update), detail: ' . $e->getMessage(), 'stories');
            unset($e);
        }
    }

    /**
     * Добавление задачи при удалении контента
     *
     * @param StoriesDto $storiesDto
     * @return void
     */
    public function remove(StoriesDto $storiesDto)
    {
        try {
            $this->storiesQueue->push(
                new StoryUpdateJob(
                    [
                        'contentId' => $storiesDto->getContentId(),
                        'contentType' => $storiesDto->getContentType(),
                        'contentOwnerUserId' => $storiesDto->getContentOwnerUserId(),
                        'isStory' => false,
                    ]
                ),
            );
        } catch (\Exception $e) {
            Yii::error('Error create StoryUpdateJob (remove), detail: ' . $e->getMessage(), 'stories');
            unset($e);
        }
    }

    /**
     * Удаление всех сторисов, которые есть в ленте
     *
     * @param int $feedId
     * @param int $contentOwnerUserId
     * @return void
     */
    public function removeForFeed(int $feedId, int $contentOwnerUserId): void
    {
        try {
            $this->storiesQueue->push(
                new StoryRemoveFeedJob(
                    [
                        'feedId' => $feedId,
                        'contentOwnerUserId' => $contentOwnerUserId,
                    ]
                ),
            );
        } catch (\Exception $e) {
            Yii::error('Error create StoryRemoveFeedJob, detail: ' . $e->getMessage(), 'stories');
            unset($e);
        }
    }

    /**
     * @param StoriesDto $storiesDto
     * @return void
     */
    private function checkContentFields(StoriesDto $storiesDto): void
    {
        $contentId = $storiesDto->getContentId();
        if ($contentId === 0) {
            throw new StoriesBaseException("ContentId должен быть больше 0");
        }

        $contentType = $storiesDto->getContentType();
        if (!in_array($contentType, $this->listContentType)) {
            throw new StoriesBaseException("Неизвестный тип контента - {$contentType}");
        }
    }

    /**
     * Создание задачи о просмотре,
     * отмечает сторисы пользователя просмотреными.
     *
     * @param int $ownerStoryUserId
     * @param int $viewerUserId
     * @return void
     */
    public function viewed(int $ownerStoryUserId, int $viewerUserId)
    {
        try {
            $this->storiesQueue->push(
                new StoryViewedJob(
                    [
                        'ownerStoryUserId' => $ownerStoryUserId,
                        'viewerUserId' => $viewerUserId,
                    ]
                ),
            );
        } catch (\Exception $e) {
            Yii::error('Error create StoryViewedJob, detail: ' . $e->getMessage(), 'stories');
            unset($e);
        }
    }

    /**
     * Проверка контента на stories
     * @param int $contentId
     * @param int $contentType
     * @return bool
     * @throws \Exception
     */
    public function contentCheckOnStories(int $contentId, int $contentType): bool
    {
        $checking = false;
        try {
            $checking = $this->dataProvider
                ->checkIsStory($contentId, $contentType);
        } catch (\Exception $e) {
            Yii::error('Error check content on stories, detail: ' . $e->getMessage(), 'stories');
            unset($e);
        }

        return $checking;
    }


    /**
     * Получение stories пользователей,
     * на которых подписан [$userId]
     *
     * @param int $authUserId
     * @param int $limit
     * @param int $offset
     *
     * @return array
     * @throws \Exception
     */
    public function getUserSubscriptionStories(int $authUserId, int $limit, int $offset): array
    {
        return $this->dataProvider
            ->getUserSubscriptionStories($authUserId, $limit, $offset);
    }

    /**
     * Получение контента stories конкретного пользователя
     *
     * @param int $userId
     * @param int $authUserId
     * @return array
     * @throws \Exception
     */
    public function getUserStoriesContent(int $userId, int $authUserId): array
    {
        return $this->dataProvider
            ->getUserStoriesContent($userId, $authUserId);
    }
}
