<?php

namespace app\components\posting\dto\transcode;

class TranscodeVideoDto
{
    private string $status;
    private string $taskId;

    private int $previewWidth;
    private int $previewHeight;
    private string $previewUrl;

    private int $originalWidth;
    private int $originalHeight;
    private string $originalUrl;

    /**
     * @param string $status
     * @param string $taskId
     * @param int $previewWidth
     * @param int $previewHeight
     * @param string $previewUrl
     * @param int $originalWidth
     * @param int $originalHeight
     * @param string $originalUrl
     */
    public function __construct(
        string $status,
        string $taskId,
        int $previewWidth,
        int $previewHeight,
        string $previewUrl,
        int $originalWidth,
        int $originalHeight,
        string $originalUrl
    ) {
        $this->status = $status;
        $this->taskId = $taskId;

        $this->previewWidth = $previewWidth;
        $this->previewHeight = $previewHeight;
        $this->previewUrl = $previewUrl;

        $this->originalWidth = $originalWidth;
        $this->originalHeight = $originalHeight;
        $this->originalUrl = $originalUrl;
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

    /**
     * @return int
     */
    public function getPreviewWidth(): int
    {
        return $this->previewWidth;
    }
    /**
     * @return int
     */
    public function getPreviewHeight(): int
    {
        return $this->previewHeight;
    }
    /**
     * @return string
     */
    public function getPreviewUrl(): string
    {
        return $this->previewUrl;
    }

    /**
     * @return int
     */
    public function getOriginalWidth(): int
    {
        return $this->originalWidth;
    }
    /**
     * @return int
     */
    public function getOriginalHeight(): int
    {
        return $this->originalHeight;
    }

    /**
     * @return string
     */
    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }
}
