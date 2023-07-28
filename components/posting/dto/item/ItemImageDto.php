<?php

namespace app\components\posting\dto\item;


class ItemImageDto extends PostItemDto
{
    protected int $type;
    protected int $position;

    protected string $image;
    protected string $imageOriginal;
    protected int $imageWidth;
    protected int $imageHeight;
    protected int $widthImageOriginal;
    protected int $heightImageOriginal;

    public function __construct(
        int $type,
        int $position,
        string $image,
        string $imageOriginal,
        int $imageWidth,
        int $imageHeight,
        int $widthImageOriginal,
        int $heightImageOriginal
    )
    {
        $this->type = $type;
        $this->position = $position;
        $this->image = $image;
        $this->imageOriginal = $imageOriginal;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->widthImageOriginal = $widthImageOriginal;
        $this->heightImageOriginal = $heightImageOriginal;
    }
}