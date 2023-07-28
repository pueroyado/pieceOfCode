<?php

namespace app\components\posting\dto;

use app\components\posting\PostItemDtoFactory;

class PostDto
{
    private int $feedId;
    private int $userId;
    private int $age;
    private int $isPhoto;
    private ?string $publishDate = null;

    private ?string $createDate = null;
    private ?array $items = [];
    private int $status;

    public function __construct(
        int $userId,
        int $feedId,
        int $isPhoto,
        int $age,
        array $items
    ) {
        $this->userId = $userId;
        $this->feedId = $feedId;
        $this->isPhoto = $isPhoto;
        $this->age = $age;

        foreach ($items as $index => $item) {
            $this->items[] = PostItemDtoFactory::build($item['type'], ++$index, $item);
        }
    }

    /**
     * @return int
     */
    public function getFeedId(): int
    {
        return $this->feedId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @return int
     */
    public function getIsPhoto(): int
    {
        return $this->isPhoto;
    }

    /**
     * Элементы поста
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string|null
     */
    public function getCreateDate(): ?string
    {
        return $this->createDate;
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return void
     */
    public function setCreateDate(\DateTimeInterface $dateTime): void
    {
        $this->createDate = $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @return string|null
     */
    public function getPublishDate(): ?string
    {
        return $this->publishDate;
    }

    /**
     * @param ?\DateTimeInterface $publishDate
     * @return self
     */
    public function setPublishDate(?\DateTimeInterface $publishDate): self
    {
        $this->publishDate = $publishDate ? (
            $publishDate >= new \DateTime() ?
                $publishDate->format('Y-m-d H:i:s'):
                (new \DateTime())->format('Y-m-d H:i:s')
            ) : null;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }
}
