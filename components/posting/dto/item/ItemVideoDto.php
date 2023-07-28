<?php

namespace app\components\posting\dto\item;

use app\components\posting\dto\transcode\TranscodeVideoDto;

class ItemVideoDto extends PostItemDto
{
    protected int $type;
    protected int $position;

    private string $name;
    private array $tags;
    private ?string $description;
    private TranscodeVideoDto $transcodeResponse;

    /**
     * @throws \JsonException
     */
    public function __construct(
        int $type,
        int $position,
        array $video
    ) {
        $this->type = $type;
        $this->position = $position;

        $this->name = (string)$video['name'];
        $this->tags = (array)$video['tags'];
        $this->description = (isset($video['description'])) ? (string)$video['description'] : null;


        $transcode = json_decode($video['transcodeResponse'], true, 512, JSON_THROW_ON_ERROR);
        $this->transcodeResponse = new TranscodeVideoDto(
            $transcode['status'],
            $transcode['body']['task_id'],
            $transcode['body']['preview']['mobile']['width'],
            $transcode['body']['preview']['mobile']['height'],
            $transcode['body']['preview']['mobile']['url'],
            $transcode['body']['preview']['original']['width'],
            $transcode['body']['preview']['original']['height'],
            $transcode['body']['preview']['original']['url'],
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return TranscodeVideoDto
     */
    public function getTranscodeResponse(): TranscodeVideoDto
    {
        return $this->transcodeResponse;
    }
}
