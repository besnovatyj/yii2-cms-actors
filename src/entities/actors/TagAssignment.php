<?php

namespace Besnovatyj\Actors\entities\actors;

use yii\db\ActiveRecord;

/**
 * @property int $actor_id;
 * @property int $tag_id;
 */
class TagAssignment extends ActiveRecord
{
    public static function create(int $tagId): self
    {
        $assignment = new static();
        $assignment->tag_id = $tagId;
        return $assignment;
    }

    public function isForTag(int $id): bool
    {
        return $this->tag_id === $id;
    }

    public static function tableName(): string
    {
        return '{{%actors_tag_asgmt}}';
    }
}
