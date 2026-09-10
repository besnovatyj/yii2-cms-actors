<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\entities;

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\queries\TaxonomyQuery;
use Besnovatyj\Meta\Meta;
use Besnovatyj\Meta\MetaBehavior;
use Besnovatyj\TreeManager\Manager\entities\Node;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $tree - Номер дерева.
 * @property int $lft - Левый ключ.
 * @property int $rgt - Правый ключ.
 * @property int $depth - Глубина.
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $status
 * @property int $sort_order - Порядок сортировки корневых узлов
 *
 * @property Meta $meta
 * @mixin MetaBehavior
 */
class Taxonomy extends Node
{
    /** Раздел снят с публикации: не показывается на фронте и не участвует в поиске. */
    public const int STATUS_INACTIVE = 0;

    /** Раздел опубликован. */
    public const int STATUS_ACTIVE = 1;

    public Meta $meta;

    /**
     * Опубликован ли раздел сам по себе (без учёта предков — см. {@see TaxonomyQuery::visible()}).
     */
    public function isActive(): bool
    {
        return (int)$this->status === self::STATUS_ACTIVE;
    }

    public static function create($name, $slug, $description, Meta $meta): self
    {
        $taxonomy = new static();
        $taxonomy->name = $name;
        $taxonomy->slug = $slug;
        $taxonomy->description = $description;
        $taxonomy->meta = $meta;
        return $taxonomy;
    }

    public function edit($name, $slug, $description, Meta $meta): void
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->meta = $meta;
    }

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->name;
    }

    public static function tableName(): string
    {
        return '{{%actors_taxonomies}}';
    }

    public function countActorsByTaxonomy(): bool|int|string|null
    {
        return $this->getActors()->count();
    }

    public function getActors(): ActiveQuery
    {
        return $this->hasMany(Actor::class, ['taxonomy_id' => 'id']);
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
            ...parent::behaviors()
        ];
    }

    public static function find(): TaxonomyQuery
    {
        return new TaxonomyQuery(static::class);
    }
}
