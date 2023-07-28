<?php

namespace app\components\posting;

use app\models\post\PostItem;
use app\components\posting\dto\item\{
    ItemAdsDto,
    ItemMusicTrackDto,
    PostItemDto,
    ItemHeaderDto,
    ItemTextDto,
    ItemImageDto,
    ItemQuoteDto,
    ItemLinkDto,
    ItemAudioDto,
    ItemVideoDto
};

class PostItemDtoFactory
{
    public static function build(int $type, int $position, array $args): PostItemDto
    {
        switch ($type) {
            case PostItem::TYPE_HEADER:
                $item = new ItemHeaderDto(
                    $args['type'],
                    $position,
                    $args['text']
                );
                break;
            case PostItem::TYPE_TEXT:
                $item = new ItemTextDto(
                    $args['type'],
                    $position,
                    $args['text']
                );
                break;
            case PostItem::TYPE_IMAGE:
                $item = new ItemImageDto(
                    $args['type'],
                    $position,
                    $args['image'],
                    $args['imageOriginal'],
                    $args['imageWidth'],
                    $args['imageHeight'],
                    $args['widthImageOriginal'],
                    $args['heightImageOriginal'],
                );
                break;
            case PostItem::TYPE_QUOTE:
                $item = new ItemQuoteDto(
                    $args['type'],
                    $position,
                    $args['text'],
                    $args['extra'],
                );
                break;
            case PostItem::TYPE_LINK:
                $item = new ItemLinkDto(
                    $args['type'],
                    $position,
                    $args['text'],
                    $args['link'],
                );
                break;
            case PostItem::TYPE_AUDIO:
                $item = new ItemAudioDto(
                    $args['type'],
                    $position,
                    $args['audio']
                );
                break;
            case PostItem::TYPE_VIDEO:
                $item = new ItemVideoDto(
                    $args['type'],
                    $position,
                    $args['video']
                );
                break;
            case PostItem::TYPE_IN_APP_MUSIC:
                $item = new ItemMusicTrackDto(
                    $args['type'],
                    $position,
                    $args['trackId']
                );
                break;
            case PostItem::TYPE_ADS:
                $item = new ItemAdsDto(
                    $args['type'],
                    $position,
                    $args['image'],
                    $args['imageOriginal'],
                    $args['imageWidth'],
                    $args['imageHeight'],
                    $args['widthImageOriginal'],
                    $args['heightImageOriginal'],
                    $args['link'] ?? null,
                    $args['param'] ? (int)$args['param'] : PostItem::ADS_PARAM_INLINE,
                );
                break;
            default:
                throw new \RuntimeException('Not found type item');
        }

        return $item;
    }
}
