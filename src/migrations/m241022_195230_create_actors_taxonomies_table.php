<?php

namespace Besnovatyj\Actors\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m241022_195230_create_actors_taxonomies_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%actors_taxonomies}}';

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
            'tree' => $this->integer()->null()
                ->comment('Идентификатор дерева'),
            'lft' => $this->integer(10)->notNull()
                ->comment('Левый ключ NestedSets'),
            'rgt' => $this->integer(10)->notNull()
                ->comment('Правый ключ NestedSets'),
            'depth' => $this->integer(10)->notNull()
                ->comment('Глубина NestedSets'), // Атрибут не может быть беззнаковым!
            'name' => $this->string(255)->null()->defaultValue("Задайте название категории")
                ->comment('Название категории актёра'),
            'slug' => $this->string(255)->notNull()
                ->comment('Slug категории актёра'),
            'description' => $this->text()->null()
                ->comment('Описание категории актёра'),
            'meta_json' => $this->text()->notNull()
                ->comment('JSON of meta-obj'),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(0)
                ->comment('Статус отображения категории'),
            'sort_order' => $this->integer(10)->notNull()->defaultValue(0)
                ->comment('Сортировка корней'),
        ], $this->tableOptions);
        $this->addCommentOnTable(static::TABLE_NAME, 'Категория актёра');

        $this->createIndexes(static::TABLE_NAME, 'depth');
        $this->createIndexes(static::TABLE_NAME, ['tree', 'rgt']);
        $this->createIndexes(static::TABLE_NAME, ['tree', 'lft', 'rgt']);
        $this->createIndexes(static::TABLE_NAME, 'slug', false, true);

        $this->insert(static::TABLE_NAME, [
            'id' => '1',
            'lft' => '1',
            'rgt' => '2',
            'depth' => '0',
            'name' => 'root',
            'slug' => 'root',
            'description' => '`NULL`',
            'meta_json' => '{}',
        ]);

        parent::safeUp();
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }

}
