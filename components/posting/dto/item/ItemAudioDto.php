<?php

namespace app\components\posting\dto\item;


use app\components\posting\dto\transcode\TranscodeAudioDto;

class ItemAudioDto extends PostItemDto
{
    protected int $type;
    protected int $position;

    private string $artist;
    private string $name;
    private TranscodeAudioDto $transcodeResponse;

    public function __construct(
        int $type,
        int $position,
        array $audio
    )
    {
        $this->type = $type;
        $this->position = $position;

        $this->artist = $audio['artist'];
        $this->name = $audio['name'];

        $transcode = json_decode($audio['transcodeResponse'], true);
        $this->transcodeResponse = new TranscodeAudioDto(
            $transcode['status'],
            $transcode['body']['task_id']
        );
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TranscodeAudioDto
     */
    public function getTranscodeResponse(): TranscodeAudioDto
    {
        return $this->transcodeResponse;
    }
}