<?php

namespace app\components\posting\dto\item;


class ItemTextDto extends PostItemDto
{
    protected int $type;
    protected int $position;
    protected string $text;

    /**
     * @param int $type
     * @param int $position
     * @param string $text
     */
    public function __construct(
        int $type,
        int $position,
        string $text
    )
    {
        $this->type = $type;
        $this->position = $position;
        $this->text = $text;
    }
}