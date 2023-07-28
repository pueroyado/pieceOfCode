<?php

declare(strict_types=1);

namespace app\components\posting\dto\item;

class ItemMusicTrackDto extends PostItemDto
{
    private int $trackId;

    public function __construct(int $type, int $position, int $trackId)
    {
        $this->type = $type;
        $this->position = $position;
        $this->trackId = $trackId;
    }

    public function getTrackId(): int
    {
        return $this->trackId;
    }
}
