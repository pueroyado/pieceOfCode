<?php

namespace app\components\cache\v3\metrics;

use Yarus\Metrics\Components\Prometheus\MetricType;
use Yarus\Metrics\Metric;
use Yarus\Metrics\MetricValue;

class CacheMissMetric implements Metric
{
    private string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function formatValue(MetricValue $value)
    {
        return $value->toInt();
    }

    public function getKey(): string
    {
        return 'cache_miss';
    }

    public function getLabels(): array
    {
        return ['source' => $this->source];
    }

    public function getType(): string
    {
        return MetricType::COUNTER()->getValue();
    }

    public function getHelp(): string
    {
        return 'Cache miss count';
    }
}
