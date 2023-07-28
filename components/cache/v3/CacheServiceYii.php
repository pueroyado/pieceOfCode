<?php

namespace app\components\cache\v3;

use app\components\cache\v3\entity\CacheRequest;
use app\components\cache\v3\metrics\CacheHitMetric;
use app\components\cache\v3\metrics\CacheMissMetric;
use Yarus\Metrics\Components\MetricsComponent;
use yii\caching\CacheInterface;
use yii\caching\TagDependency;

class CacheServiceYii
{
    public const TAG_GENERAL = 'general';

    private CacheInterface $cache;
    private CacheManager $manager;
    private MetricsComponent $metrics;

    public function __construct(CacheInterface $cache, MetricsComponent $metrics)
    {
        $this->cache = $cache;
        $this->manager = new CacheManager();
        $this->metrics = $metrics;
    }

    public function get(CacheRequest $request)
    {
        $key = $this->manager->generateKey($request);

        $value = $this->cache->get($key);

        if ($value !== null) {
            $this->metrics->increment(new CacheHitMetric($request->getSource()));
        }

        return $value;
    }

    public function set(CacheRequest $request, $data, ?array $tags = [], ?int $ttl = 10 * 60): bool
    {
        $key = $this->manager->generateKey($request);

        $tags[] = self::TAG_GENERAL;

        $dependency = new TagDependency(['tags' => $tags]);

        $this->metrics->increment(new CacheMissMetric($request->getSource()));

        return $this->cache->set($key, $data, $ttl, $dependency);
    }

    public function invalidate(array $tags): void
    {
        TagDependency::invalidate($this->cache, $tags);
    }

    /**
     * @param string[] $tags
     */
    public function invalidateByTagsAndId(array $tags, int $entityId): void
    {
        $this->invalidate($this->getTagsByArrays([$entityId], $tags));
    }

    /**
     * @param array<string|int> $ids
     * @param string $tagPrefix
     * @return string[]
     */
    public function getTags(array $ids, string $tagPrefix): array
    {
        $result = [];
        foreach ($ids as $entityId) {
            $result[] = $this->getTagName($tagPrefix, $entityId);
        }

        return $result;
    }

    /**
     * @param array<string|int> $ids
     * @param string[] $tagPrefixes
     * @return string[]
     */
    public function getTagsByArrays(array $ids, array $tagPrefixes): array
    {
        $result = [];
        foreach ($tagPrefixes as $tagPrefix) {
            foreach ($ids as $entityId) {
                $result[] = $this->getTagName($tagPrefix, $entityId);
            }
        }
        return $result;
    }

    /**
     * @param string $tagPrefix
     * @param string|int $id
     * @return string
     */
    private function getTagName(string $tagPrefix, $id): string
    {
        return sprintf('%s_%s', $tagPrefix, $id);
    }
}
