<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\readModels;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Tags\entities\Tag;
use Besnovatyj\Contracts\tags\TaggedItem;
use Besnovatyj\Contracts\search\SearchDocument;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
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

    /**
     * Из переданных id — актёры, доступные анониму (для счётчиков страницы тега и облака).
     *
     * @param int[] $ids
     * @return int[]
     */
    public function visibleIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        return array_map('intval', Actor::find()->alias('p')->visible('p')->andWhere(['p.id' => $ids])->select('p.id')->column());
    }

    /**
     * Карточки актёров для страницы тега модуля Tags — только видимые, в порядке `$ids`.
     *
     * @param int[] $ids
     * @return iterable<TaggedItem>
     */
    public function taggedItems(array $ids): iterable
    {
        if ($ids === []) {
            return;
        }

        /** @var Actor[] $actors */
        $actors = Actor::find()->alias('p')->visible('p')->with('mainImage')->andWhere(['p.id' => $ids])->indexBy('id')->all();

        foreach ($ids as $id) {
            $actor = $actors[$id] ?? null;
            if ($actor === null) {
                continue;
            }
            yield new TaggedItem(
                type: Actor::tagType(),
                entityId: (int)$actor->id,
                route: '/Actors/actor/view',
                params: ['id' => (int)$actor->id],
                title: (string)$actor->name,
                excerpt: null,
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


    /**
     * Актёры для карты сайта.
     *
     * Тот же инвариант, что у поиска, — только публично доступное ({@see ActorQuery::visible()}).
     * Отличается набор полей: карте нужны имя (для человеческой карты) и дата ИЗМЕНЕНИЯ, по которой
     * краулер решает, перечитывать ли страницу; поиску — описание, теги и дата публикации. Поэтому
     * два тонких метода поверх одной выборки, а не один «универсальный».
     *
     * Порядок — как в списке на сайте: сначала заданный редактором, потом по идентификатору.
     *
     * @return iterable<SitemapUrl>
     */
    public function sitemapUrls(): iterable
    {
        $query = Actor::find()->alias('p')->visible('p')
            ->orderBy(['p.sort' => SORT_ASC, 'p.id' => SORT_ASC]);

        /** @var Actor $actor */
        foreach ($query->each(200) as $actor) {
            yield new SitemapUrl(
                route: '/Actors/actor/view',
                params: ['id' => (int)$actor->id],
                title: (string)$actor->name,
                // updated_at — колонка DATETIME, а контракт ждёт Unix-timestamp.
                lastModified: $actor->updated_at === null ? null : (strtotime((string)$actor->updated_at) ?: null),
            );
        }
    }

    /**
     * Отпечаток состояния актёров для карты сайта: сколько их и когда правили последний раз.
     *
     * Одного `MAX(updated_at)` мало — он не замечает удаления записи, а удалённая страница обязана
     * исчезнуть из карты. Пара «сколько + когда» это закрывает и стоит одного запроса.
     */
    public function sitemapRevision(): string
    {
        $row = Actor::find()->alias('p')->visible('p')
            ->select(['total' => 'COUNT(*)', 'latest' => 'MAX(p.updated_at)'])
            ->asArray()
            ->one();

        return ((string)($row['total'] ?? '0')) . ':' . ((string)($row['latest'] ?? ''));
    }
}
