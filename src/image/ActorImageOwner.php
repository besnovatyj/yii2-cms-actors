<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Actors\image;

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\actors\Image;
use Besnovatyj\Actors\repositories\ActorRepository;
use Besnovatyj\Images\contracts\ImageOwnerInterface;
use Besnovatyj\Images\contracts\NullImageOwnerTrait;
use yii\db\Exception;

/**
 * Адаптер Actor к ImageOwnerInterface.
 */
readonly class ActorImageOwner implements ImageOwnerInterface
{
    use NullImageOwnerTrait;

    public function __construct(
        private Actor           $actor,
        private ActorRepository $repository,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getOwnerId(): int
    {
        return $this->actor->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return Image[]
     */
    public function getOwnedImages(): array
    {
        return $this->actor->images;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageId(): ?int
    {
        return $this->actor->main_image_id ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function setMainImageId(?int $imageId): void
    {
        $this->actor->setMainImage($imageId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function saveOwner(): void
    {
        $this->repository->save($this->actor);
    }

    /**
     * Блокирует строку галереи (SELECT FOR UPDATE) до конца транзакции.
     *
     * Исключает race condition при параллельной загрузке нескольких файлов.
     * @throws Exception
     */
    public function lockOwner(): void
    {
        $this->actor->lock();
    }

    /**
     * Обновляет данные галереи из БД после применения блокировки.
     */
    public function refreshOwner(): void
    {
        $this->actor->refresh();
    }
}
