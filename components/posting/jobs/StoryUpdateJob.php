<?php

namespace app\components\posting\jobs;

use app\domain\cache\dictionary\CacheTagPrefixDictionary;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\queue\RetryableJobInterface;
use app\components\cache\v3\CacheServiceYii;
use app\repositories\StoriesRepository;

/**
 * Обновление данных по сторису при редактировании контента
 */
class StoryUpdateJob extends BaseObject implements RetryableJobInterface
{
    public int $contentId;
    public int $contentType;
    public int $contentOwnerUserId;
    public bool $isStory;
    public ?int $contentFeedId;

    private StoriesRepository $storiesRepository;
    private CacheServiceYii $cacheServiceYii;

    /**
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->storiesRepository = Yii::$app->get(StoriesRepository::class);
        $this->cacheServiceYii = Yii::$app->get(CacheServiceYii::class);
    }

    /**
     * @param $queue
     * @return int
     * @throws Exception
     */
    public function execute($queue): int
    {
        $storyExists = $this->storiesRepository->exist(
            $this->contentId,
            $this->contentType,
            $this->contentOwnerUserId
        );

        $isInvalidateCache = false;

        // если сторис есть но контент нужно убрать
        if ($storyExists && $this->isStory === false) {
            $this->storiesRepository->remove(
                $this->contentId,
                $this->contentType,
                $this->contentOwnerUserId
            );

            $isInvalidateCache = true;
        } elseif ($this->isStory === true) {
            // update or insert
            $this->storiesRepository->save(
                $this->contentId,
                $this->contentType,
                $this->contentOwnerUserId,
                $this->contentFeedId
            );

            // удалим запись о просмотре сторис
            $this->storiesRepository
                ->clearViewed($this->contentOwnerUserId);

            $isInvalidateCache = true;
        }

        if ($isInvalidateCache) {
            $this->cacheServiceYii->invalidate(
                $this->cacheServiceYii->getTagsByArrays(
                    [$this->contentOwnerUserId],
                    [
                        CacheTagPrefixDictionary::STORIES_LIST_PREFIX,
                        CacheTagPrefixDictionary::STORIES_CONTENT_PREFIX,
                    ]
                )
            );
        }

        return 0;
    }

    /**
     * Максимальное время выполнения задания
     * @return int
     */
    public function getTtr(): int
    {
        return 2 * 60;
    }

    /**
     * Максимальное кол-во попыток
     * @param $attempt
     * @param $error
     * @return bool
     */
    public function canRetry($attempt, $error): bool
    {
        return ($attempt < 3) && ($error instanceof \Exception);
    }
}
