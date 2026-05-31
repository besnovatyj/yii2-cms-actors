<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Actors\services\manage;

use Besnovatyj\Meta\Meta;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\actors\TagAssignment;
use Besnovatyj\Actors\entities\Tag;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use Besnovatyj\Actors\repositories\ActorRepository;
use Besnovatyj\Actors\repositories\TagRepository;
use Besnovatyj\Actors\repositories\TaxonomyRepository;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\helpers\Inflector;

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
    private TagRepository $tags;

    public function __construct(
        ActorRepository    $actors,
        TaxonomyRepository $taxonomies,
        TagRepository      $tags
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
            $this->actors->save($actor);
            $this->assignTags($actor, $form->tags->newTagsNames);

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
            $this->revokeTags($actor);
            $this->assignTags($actor, $form->tags->newTagsNames);

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
            $this->revokeTags($actor);
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
     * @throws Exception
     */
    private function assignTags(Actor $actor, array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            $slug = Inflector::slug($tagName);

            $tag = $this->tags->findBySlug($slug);
            if (!$tag) {
                $tag = Tag::create($tagName, $slug);
                $this->tags->save($tag);
            }

            $exists = TagAssignment::find()
                ->andWhere(['actor_id' => $actor->id, 'tag_id' => $tag->id])
                ->exists();

            if ($exists) {
                continue;
            }

            $assignment = new TagAssignment();
            $assignment->actor_id = $actor->id;
            $assignment->tag_id = $tag->id;

            if (!$assignment->save()) {
                throw new Exception('Failed to save tag assignment.');
            }
        }
    }

    private function revokeTags(Actor $actor): void
    {
        TagAssignment::deleteAll(['actor_id' => $actor->id]);
    }

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
