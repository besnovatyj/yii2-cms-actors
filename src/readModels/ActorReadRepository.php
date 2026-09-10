<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\readModels;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Tag;
use Besnovatyj\Contracts\search\SearchDocument;
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
        return Actor::find()->visible()->count();
    }

    public function getAllByRange(int $offset, int $limit): array
    {
        return Actor::find()->alias('p')->visible('p')
            ->orderBy(['p.sort' => SORT_ASC, 'p.id' => SORT_ASC])
            ->limit($limit)->offset($offset)->all();
    }

    public function getAllIterator(): iterable
    {
        return Actor::find()->alias('p')->visible('p')->with('mainImage', 'brand')->each();
    }

    public function getAll(): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->visible('p')->with('mainImage');
        return $this->getProvider($query);
    }

    public function getAllByTaxonomy(Taxonomy $taxonomy): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->visible('p')->with('mainImage', 'taxonomy');
        $ids = $this->treeScope->descendantIds($taxonomy, andSelf: true);
        $query->andWhere(['p.taxonomy_id' => $ids]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function getAllByTag(Tag $tag): DataProviderInterface
    {
        $query = Actor::find()->alias('p')->visible('p')->with('mainImage');
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
        return Actor::find()->visible()->orderBy(new Expression('rand()'))->limit($limit)->all();
    }

    public function find(int $id): ?Actor
    {
        /** @var $actors Actor */
        $actors = Actor::find()->visible()->andWhere(['id' => $id])->one();
        return $actors;
    }

    /**
     * Актёры для сквозного поиска — только публично доступные ({@see ActorQuery::visible()}).
     *
     * Генератор с чтением пачками: полная переиндексация не должна держать в памяти всех актёров
     * сразу. Поля отдаются СЫРЫМИ (HTML не чистится, шорткоды не раскрываются) — нормализация
     * едина для всех модулей и выполняется модулем поиска.
     *
     * @return iterable<SearchDocument>
     */
    public function searchDocuments(): iterable
    {
        $query = Actor::find()->alias('p')->visible('p')
            ->with(['tags', 'taxonomy', 'mainImage'])
            ->orderBy(['p.id' => SORT_ASC]);

        /** @var Actor $actor */
        foreach ($query->each(100) as $actor) {
            $keywords = array_map(static fn (Tag $tag): string => (string)$tag->name, $actor->tags);

            if ($actor->taxonomy !== null) {
                $keywords[] = (string)$actor->taxonomy->name;
            }

            yield new SearchDocument(
                type: 'actors.actor',
                entityId: (int)$actor->id,
                route: '/Actors/actor/view',
                params: ['id' => (int)$actor->id],
                title: (string)$actor->name,
                text: (string)$actor->description,
                keywords: implode(' ', $keywords),
                // `created_at` — колонка DATETIME, а контракт ждёт Unix-timestamp: приведение
                // (int) молча дало бы год вместо даты (грабли, уже пойманные в блоге).
                date: $actor->created_at === null ? null : (strtotime((string)$actor->created_at) ?: null),
                image: $actor->mainImage?->getThumbUrl('file', 'frontend_list'),
            );
        }
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                // Порядок фронтэнда — ручной; id вторичным ключом, иначе записи
                // с одинаковым sort выстраивались бы произвольно
                'defaultOrder' => ['sort' => SORT_ASC],
                'attributes' => [
                    'sort' => [
                        'asc' => ['p.sort' => SORT_ASC, 'p.id' => SORT_ASC],
                        'desc' => ['p.sort' => SORT_DESC, 'p.id' => SORT_DESC],
                    ],
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
