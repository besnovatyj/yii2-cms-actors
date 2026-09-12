<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Actors\services\manage;

use Besnovatyj\Meta\Meta;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use Besnovatyj\Actors\repositories\ActorRepository;
use Besnovatyj\Actors\repositories\TaxonomyRepository;
use Besnovatyj\Tags\services\TagAssigner;
use Throwable;
use Yii;
use yii\db\Exception;

/**
 * Сервис управления актёрами.
 *
 * Отвечает за CRUD актёров и управление тегами.
 * Логика загрузки/удаления изображений вынесена в standalone actions
 * через пакет besnovatyj/yii2-cms-images + ActorImageOwner.
 */
class ActorManageService
{
    private ActorRepository $actors;
    private TaxonomyRepository $taxonomies;
    /** Теги — общий словарь модуля Tags: связи пишет только он, slug из имени выводит его форма. */
    private TagAssigner $tags;

    public function __construct(
        ActorRepository    $actors,
        TaxonomyRepository $taxonomies,
        TagAssigner        $tags
    ) {
        $this->actors = $actors;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function create(ActorForm $form): Actor
    {
        $taxonomy = $this->taxonomies->get($form->taxonomies->main);

        $actor = Actor::create(
            $form->name,
            $form->description,
            $taxonomy->id,
            $form->status,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Новый актёр встаёт в конец ручного порядка: позиция по умолчанию (0)
            // у всех новых записей одинакова, и порядок между ними определяла бы база
            $actor->changeSort($this->actors->nextSort());

            $this->actors->save($actor);
            $this->tags->sync(Actor::tagType(), (int)$actor->id, $form->tags->items);

            $transaction->commit();
            return $actor;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function edit(int $id, ActorForm $form): void
    {
        $actor = $this->actors->get($id);
        $taxonomy = $this->taxonomies->get($form->taxonomies->main);

        $actor->edit(
            $form->name,
            $form->description,
            $form->status,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            )
        );

        $actor->changeMainTaxonomy($taxonomy->id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->actors->save($actor);
            $this->tags->sync(Actor::tagType(), (int)$actor->id, $form->tags->items);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function activate(int $id): void
    {
        $actor = $this->actors->get($id);
        $actor->activate();
        $this->actors->save($actor);
    }

    /**
     * @throws Exception
     */
    public function draft(int $id): void
    {
        $actor = $this->actors->get($id);
        $actor->draft();
        $this->actors->save($actor);
    }

    /**
     * @throws Throwable
     */
    public function remove(int $id): void
    {
        $actor = $this->actors->get($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Внешнего ключа на актёра у общих связей тегов нет — снимаем явно, иначе останутся сироты.
            $this->tags->detachAll(Actor::tagType(), (int)$actor->id);
            $this->removeImages($actor);

            $this->actors->remove($actor);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // ==================== Private methods ====================

    /**
     * @throws \yii\db\StaleObjectException
     * @throws Throwable
     */
    private function removeImages(Actor $actor): void
    {
        foreach ($actor->images as $image) {
            $image->delete();
        }
    }
}
