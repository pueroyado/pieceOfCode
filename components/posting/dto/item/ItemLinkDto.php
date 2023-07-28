<?php

namespace app\components\posting\dto\item;


class ItemLinkDto extends PostItemDto
{
    protected int $type;
    protected int $position;
    protected string $text;
    protected string $link;

    public function __construct(
        int $type,
        int $position,
        string $text,
        string $link
    )
    {
        $this->type = $type;
        $this->position = $position;
        $this->text = $text;
        $this->link = $link;
    }
}