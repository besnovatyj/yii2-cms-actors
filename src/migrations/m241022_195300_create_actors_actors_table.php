<?php

namespace Besnovatyj\Actors\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m241022_195300_create_actors_actors_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%actors_actors}}';

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
            'id' => $this->primaryKey()
                ->comment('Идентификатор актёра'),
            'taxonomy_id' => $this->integer(10)->notNull()
                ->comment('Идентификатор категории актёра'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()')
                ->comment('Дата создания актёра'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()')->append('ON UPDATE NOW()')
                ->comment('Дата последнего редактирования актёра'),
            'name' => $this->string(255)->notNull()
                ->comment('Название актёра'),
            'description' => $this->text()->null()
                ->comment('Описание актёра'),
            'main_image_id' => $this->integer(10)->null()
                ->comment('Идентификатор основной фотографии актёра'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус отображения актёра'),
            'meta_json' => $this->text()->notNull()
                ->comment('JSON of meta-obj'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Актёры');

        $this->createIndexes(static::TABLE_NAME, 'taxonomy_id');
        $this->createIndexes(static::TABLE_NAME, 'main_image_id');

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
