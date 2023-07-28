<?php

namespace app\components\posting\dto\item;

class PostItemDto
{
    protected int $type;
    protected int $position;

    protected string $text;

    protected string $image;
    protected string $imageOriginal;

    protected int $imageWidth;
    protected int $imageHeight;
    protected int $widthImageOriginal;
    protected int $heightImageOriginal;

    protected string $extra;
    protected string $link;

    protected int $param;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text ?? null;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image ?? null;
    }

    /**
     * @return string|null
     */
    public function getImageOriginal(): ?string
    {
        return $this->imageOriginal ?? null;
    }

    /**
     * @return int|null
     */
    public function getImageWidth(): ?int
    {
        return $this->imageWidth ?? null;
    }

    /**
     * @return int|null
     */
    public function getImageHeight(): ?int
    {
        return $this->imageHeight ?? null;
    }

    /**
     * @return int|null
     */
    public function getWidthImageOriginal(): ?int
    {
        return $this->widthImageOriginal ?? null;
    }

    /**
     * @return int|null
     */
    public function getHeightImageOriginal(): ?int
    {
        return $this->heightImageOriginal ?? null;
    }

    /**
     * @return string|null
     */
    public function getExtra(): ?string
    {
        return $this->extra ?? null;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link ?? null;
    }

    /**
     * @return int|null
     */
    public function getParam(): ?int
    {
        return $this->param ?? null;
    }
}