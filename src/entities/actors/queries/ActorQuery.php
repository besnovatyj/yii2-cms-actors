<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\entities\actors\queries;

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Taxonomy;
use yii\db\ActiveQuery;

class ActorQuery extends ActiveQuery
{
    /**
     * @param null $alias
     * @return $this
     */
    public function active($alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Actor::STATUS_ACTIVE,
        ]);
    }

    /**
     * Актёр доступен анонимному посетителю: опубликован сам И лежит в видимом разделе.
     *
     * Одной публикации мало: скрытый раздел не должен «протекать» на фронт своими записями —
     * раздел проверяется целиком, вместе с предками (см. {@see TaxonomyQuery::visible()}).
     * Запись без раздела (`taxonomy_id` NULL) видна: скрывать её не за что.
     *
     * @param string|null $alias алиас таблицы актёров, если запрос строится с `alias()`
     */
    public function visible(?string $alias = null): static
    {
        $column = ($alias ? $alias . '.' : '') . 'taxonomy_id';

        return $this->active($alias)->andWhere([
            'or',
            [$column => null],
            [$column => Taxonomy::find()->visible()->select('id')],
        ]);
    }
}
