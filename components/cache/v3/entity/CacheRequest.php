<?php

namespace app\components\cache\v3\entity;

class CacheRequest
{
    private string $source;
    /* Далее опциональные */
    private array $tail; // мапа параметров запроса
    private ?int $elementId; // id целевого элемента
    private ?int $userId; // id авторизованного юзера

    public function __construct(
        string $source,
        array $tail = [],
        ?int $elementId = null,
        ?int $userId = null
    ) {
        ksort($tail); // Сортируем ключи хвоста для строго порядка
        $this->source = $source;
        $this->tail = $tail;
        $this->elementId = $elementId;
        $this->userId = $userId;
    }

    public static function create(...$args): self
    {
        return new self(...$args);
    }

    /**
     * @return array
     */
    public function getTail(): array
    {
        return $this->tail;
    }

    /**
     * @return int|null
     */
    public function getElementId(): ?int
    {
        return $this->elementId;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
