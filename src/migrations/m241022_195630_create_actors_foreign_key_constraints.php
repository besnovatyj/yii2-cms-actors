<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use Yii;
use yii\db\Exception;

class m241022_195630_create_actors_foreign_key_constraints extends BaseMigration
{

    /**
     * @throws Exception
     */
    public function safeUp(): void
    {
        parent::safeUp();

        Yii::$app->getDb()->createCommand("SET foreign_key_checks = 0")->execute();

        // Изображения актёров
        $this->createFKs(
            m241022_195240_create_actors_images_table::TABLE_NAME,
            'actor_id',
            m241022_195300_create_actors_actors_table::TABLE_NAME,
            'id', 'CASCADE', 'CASCADE');

        // Актёры
        $this->createFKs(
            m241022_195300_create_actors_actors_table::TABLE_NAME,
            'taxonomy_id',
            m241022_195230_create_actors_taxonomies_table::TABLE_NAME,
            'id');
        $this->createFKs(
            m241022_195300_create_actors_actors_table::TABLE_NAME,
            'main_image_id',
            m241022_195240_create_actors_images_table::TABLE_NAME,
            'id', 'SET NULL');

        // Связь актёра с тегами
        $this->createFKs(
            m241022_195530_create_actors_tag_asgmt_table::TABLE_NAME,
            'actor_id',
            m241022_195300_create_actors_actors_table::TABLE_NAME,
            'id', 'CASCADE');
        $this->createFKs(
            m241022_195530_create_actors_tag_asgmt_table::TABLE_NAME,
            'tag_id',
            m241022_195235_create_actors_tags_table::TABLE_NAME,
            'id', 'CASCADE');

        Yii::$app->db->createCommand('SET foreign_key_checks = 1')->execute();

    }

    public function safeDown(): void
    {
        // Отменяем действия по умолчанию,
        // так как \Besnovatyj\Kernel\migration\BaseMigration::safeDown() вызывает static::TABLE_NAME,
        // которого в данной миграции не существует.
        // Так же, \Besnovatyj\Kernel\migration\BaseMigration::safeDown() при удалении таблиц сам удалит у них все индексы и внешние ключи.

        // parent::safeDown();
    }

}
