<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m241022_195530_create_actors_tag_asgmt_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%actors_tag_asgmt}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'actor_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор актёра'),
            'tag_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор тега'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Связь актёра с тегом');

        $this->createIndexes(static::TABLE_NAME, 'actor_id');
        $this->createIndexes(static::TABLE_NAME, 'tag_id');
        $this->createIndexes(static::TABLE_NAME, ['actor_id', 'tag_id'], true);

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }

}
