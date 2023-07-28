<?php

namespace app\components\posting\jobs;

use app\domain\cache\dictionary\CacheTagPrefixDictionary;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\queue\RetryableJobInterface;
use app\repositories\StoriesRepository;
use app\components\cache\v3\CacheServiceYii;

class StoryRemoveFeedJob extends BaseObject implements RetryableJobInterface
{
    public int $feedId;
    public int $contentOwnerUserId;

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
     * @throws Exception
     */
    public function execute($queue): int
    {
        $this->storiesRepository->removeStoryForFeed($this->feedId, $this->contentOwnerUserId);

        $this->cacheServiceYii->invalidate(
            $this->cacheServiceYii->getTagsByArrays(
                [$this->contentOwnerUserId],
                [
                    CacheTagPrefixDictionary::STORIES_LIST_PREFIX,
                    CacheTagPrefixDictionary::STORIES_CONTENT_PREFIX,
                ]
            )
        );

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
