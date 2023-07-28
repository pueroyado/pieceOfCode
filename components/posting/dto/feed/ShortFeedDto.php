<?php

declare(strict_types=1);

namespace app\components\posting\dto\feed;

class ShortFeedDto implements \JsonSerializable
{
    private int $id;
    private int $status;
    private int $type;
    private string $name;
    private int $userId;
    private bool $isPrivate;
    private ?string $description;
    private ?string $image;

    public function __construct(
        int $id,
        int $status,
        int $type,
        string $name,
        int $userId,
        bool $isPrivate,
        ?string $description = null,
        ?string $image = null
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->type = $type;
        $this->name = $name;
        $this->userId = $userId;
        $this->isPrivate = $isPrivate;
        $this->description = $description;
        $this->image = $image;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'userId' => $this->userId,
            'isPrivate' => $this->isPrivate,
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function getIsPrivate(): bool
    {
        return $this->isPrivate;
    }


    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }
}
