<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\repositories;

use Besnovatyj\Actors\entities\actors\Image;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class ImageRepository
{
    public function get(int $id): Image
    {
        if (!$image = Image::findOne($id)) {
            throw new NotFoundException('Image is not found.');
        }
        return $image;
    }

    public function getByActorAndId(int $actorId, int $id): Image
    {
        $image = Image::find()
            ->andWhere(['id' => $id, 'actor_id' => $actorId])
            ->one();

        if (!$image) {
            throw new NotFoundException('Image is not found.');
        }
        return $image;
    }

    public function findByActor(int $actorId): array
    {
        return Image::find()
            ->andWhere(['actor_id' => $actorId])
            ->orderBy(['sort' => SORT_ASC])
            ->all();
    }

    public function getMaxSort(int $actorId): int
    {
        return (int)Image::find()
            ->andWhere(['actor_id' => $actorId])
            ->max('sort');
    }

    /**
     * @throws Exception
     */
    public function save(Image $image): void
    {
        if (!$image->save()) {
            throw new RuntimeException('Saving error: ' . json_encode($image->getErrors()));
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(Image $image): void
    {
        if (!$image->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }

    public function removeAllByActor(int $actorId): void
    {
        $images = $this->findByActor($actorId);
        foreach ($images as $image) {
            $this->remove($image);
        }
    }
}
