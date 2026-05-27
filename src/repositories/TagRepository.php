<?php

namespace Besnovatyj\Actors\repositories;

use Besnovatyj\Actors\entities\actors\TagAssignment;
use Besnovatyj\Actors\entities\Tag;
use RuntimeException;
use Throwable;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Exception;
use yii\db\StaleObjectException;

class TagRepository
{
    public function get($id): Tag
    {
        if (!$tag = Tag::findOne($id)) {
            throw new NotFoundException('Tag is not found.');
        }
        return $tag;
    }

    public function findByName($name): ?Tag
    {
        return Tag::findOne(['name' => $name]);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return Tag::findOne(['slug' => $slug]);
    }

    /**
     * @throws Exception
     */
    public function save(Tag $tag): void
    {
        if (!$tag->save()) {
            throw new RuntimeException('Saving error.');
        }
    }

    /**
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function remove(Tag $tag): void
    {
        if (!$tag->delete()) {
            throw new RuntimeException('Removing error.');
        }
    }

    public function searchEmptyTags(): DataProviderInterface
    {
        $subQuery = TagAssignment::find()->select(['tag_id']);
        $query = Tag::find()->where(['not in', 'id', $subQuery])->orderBy('id');
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);
    }

    public function deleteEmptyTags(): int
    {
        $subQuery = TagAssignment::find()->select(['tag_id']);
        return Tag::deleteAll(['not in', 'id', $subQuery]);
    }

}
