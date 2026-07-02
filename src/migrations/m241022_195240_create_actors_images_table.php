<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m241022_195240_create_actors_images_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%actors_images}}';

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
            'id' => $this->primaryKey(),
            'actor_id' => $this->integer(10)->notNull()
                ->comment('Id актёра, к которому принадлежит данное фото'),
            'file' => $this->string(255)->notNull()
                ->comment('Файл фотографии'),
            'sort' => $this->integer(10)->notNull()
                ->comment('Сортировка фотографии у конкретного актёра'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Изображения для актёра');

        $this->createIndexes(static::TABLE_NAME, 'actor_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
