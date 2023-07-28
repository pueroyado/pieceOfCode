<?php

namespace app\components\posting\dto\transcode;

class TranscodeAudioDto
{
    private string $status;
    private string $taskId;

    public function __construct(
        string $status,
        string $taskId
    )
    {
        $this->status = $status;
        $this->taskId = $taskId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTaskId(): string
    {
        return $this->taskId;
    }
}