<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\readModels;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Tag;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\Expression;

class ActorReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Taxonomy::class);
    }

    public function count(): int
    {
        return Actor::find()->active()->count();
    }

    public function getAllByRange(int $offset, int $limit): array
    {
        return Actor::find()->alias('p')->active('p')->orderBy(['sort' => SORT_ASC])->limit($limit)->offset($offset)->all();
    }

    public function getAllIterator(): iterable
    {
        return Actor::find()->alias('p')->active('p')->with('mainImage', 'brand')->each();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->active('p')->with('mainImage');
        return $this->getProvider($query);
    }

    public function getAllByTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->active('p')->with('mainImage', 'taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->andWhere(['p.taxonomy_id' => $ids]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->active('p')->with('mainImage');
        $query->joinWith(['tagAssignments ta'], false);
        $query->andWhere(['ta.tag_id' => $tag->id]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

//    public function getFeatured($limit): array
//    {
//        return Actor::find()->with('mainImage')->orderBy(['id' => SORT_DESC])->limit($limit)->all();
//    }

    public function getRand($limit): array
    {
        return Actor::find()->active()->orderBy(new Expression('rand()'))->limit($limit)->all();
    }

    public function find(int $id): ?Actor
    {
        /** @var $actors Actor */
        $actors = Actor::find()->active()->andWhere(['id' => $id])->one();
        return $actors;
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => ['p.id' => SORT_ASC],
                        'desc' => ['p.id' => SORT_DESC],
                    ],
                    'name' => [
                        'asc' => ['p.name' => SORT_ASC],
                        'desc' => ['p.name' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSizeLimit' => [15, 100],
                'pageSize' => 12,
                'pageSizeParam' => false,
            ]
        ]);
    }
}
