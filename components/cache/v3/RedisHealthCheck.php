<?php

declare(strict_types=1);

namespace app\components\cache\v3;

use Yarus\Metrics\Healthchecks\Healthcheckable;
use yii\redis\Connection;

class RedisHealthCheck implements Healthcheckable
{
    private Connection $connection;
    private string $marker;

    public function __construct(Connection $connection, string $marker)
    {
        $this->connection = $connection;
        $this->marker = $marker;
    }

    public function makeDummyOperation(): bool
    {
        $key = 'mainApi_healthCheck';
        $value = 'test';

        $this->connection->set($key, $value);
        $actualValue = $this->connection->get($key);
        return $actualValue === $value;
    }

    public function getHealthcheckMarker(): string
    {
        return $this->marker;
    }
}
