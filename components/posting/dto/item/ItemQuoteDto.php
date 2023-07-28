<?php

namespace app\components\posting\dto\item;


class ItemQuoteDto extends PostItemDto
{
    protected int $type;
    protected int $position;
    protected string $text;
    protected string $extra;

    public function __construct(
        int $type,
        int $position,
        string $text,
        string $extra
    )
    {
        $this->type = $type;
        $this->position = $position;
        $this->text = $text;
        $this->extra = $extra;
    }
}