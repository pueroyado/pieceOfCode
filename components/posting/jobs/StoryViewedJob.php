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

class StoryViewedJob extends BaseObject implements RetryableJobInterface
{
    /**
     * ID пользователя, владельца сториса
     */
    public int $ownerStoryUserId;

    /**
     * ID пользователя, посмотревшего стори
     */
    public int $viewerUserId;

    /**
     * @var StoriesRepository|object|null
     */
    private StoriesRepository $storiesRepository;
    private CacheServiceYii $cacheServiceYii;

    /**
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->storiesRepository = \Yii::$app->get(StoriesRepository::class);
        $this->cacheServiceYii = Yii::$app->get(CacheServiceYii::class);
    }

    /**
     * @param $queue
     * @return int
     * @throws Exception
     */
    public function execute($queue): int
    {
        $this->storiesRepository
            ->viewed($this->ownerStoryUserId, $this->viewerUserId);

        $this->cacheServiceYii->invalidate(
            $this->cacheServiceYii->getTags(
                [$this->viewerUserId],
                CacheTagPrefixDictionary::STORIES_LIST_PREFIX,
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
