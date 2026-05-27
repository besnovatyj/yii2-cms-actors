<?php

declare(strict_types=1);

namespace Besnovatyj\Actors\entities\actors;

use Besnovatyj\Images\base\BaseImage;

/**
 * Изображение актёра.
 *
 * @property int $id
 * @property int $actor_id
 * @property string $file
 * @property int $sort
 */
class Image extends BaseImage
{
    /**
     * {@inheritdoc}
     */
    protected static function getParentAttribute(): string
    {
        return 'actor_id';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getStorageName(): string
    {
        return 'Actors';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getThumbProfiles(): array
    {
        return [
            'admin'          => ['width' => 70,   'height' => 100], // /backend/actor/index
            'thumb'          => ['width' => 640,  'height' => 480], // /backend/actor/view
            'frontend_list'  => ['width' => 1200, 'height' => 600], // /frontend/actor/index
            'frontend_item'  => ['width' => 1200, 'height' => 600], // /frontend/actor/view
            'actor_poster'   => ['width' => 1200, 'height' => 600],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%actors_images}}';
    }
}
