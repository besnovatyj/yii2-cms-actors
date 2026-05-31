<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// TODO @deprecated
// TODO - Образец схемы базы данных
return [
    'tables' => [
        // https://www.yiiframework.com/doc/api/2.0/yii-db-querybuilder#getColumnType()-detail
        // @see \yii\db\QueryBuilder::getColumnType()
        '{{%actors_taxonomies}}' => [
            'columns' => [
                'id' => 'pk',
                'tree' => 'integer DEFAULT NULL', // TODO Кажется, при переносе веток между деревьями обнуляется, проверить
                'lft' => 'integer NOT NULL',
                'rgt' => 'integer NOT NULL',
                'depth' => 'integer NOT NULL', // Атрибут не может быть беззнаковым!
                'name' => 'string NULL DEFAULT "Задайте название категории"',
                'slug' => 'string NOT NULL',
                'description' => 'text NULL DEFAULT NULL',
                'meta_json' => 'text NOT NULL',
                'status' => 'tinyint(1) NOT NULL DEFAULT 0',
                'sort_order' => 'integer NOT NULL DEFAULT 0',
            ],
            'comments' => [
                'id' => '(Form Paulzi behavior)',
                'tree' => 'Идентификатор дерева, если разрешено несколько деревьев (Form Paulzi behavior).',
                'lft' => 'Левый ключ NestedSets',
                'rgt' => 'Правый ключ NestedSets',
                'depth' => 'Глубина NestedSets',
                'name' => 'Название категории актёра',
                'slug' => 'Slug категории актёра',
                'description' => 'Описание категории актёра',
                'meta_json' => 'JSON of meta-obj',
                'status' => 'Статус отображения категории',
                'sort_order' => 'Сортировка корней',
            ],
            'comment' => 'Категория актёра',
            'indexes' => [
                // [['column_1', 'column_2', ...], isUnique(false), isPK(false)]
                ['depth', false],
                [['tree', 'rgt'], false],
                [['tree', 'lft', 'rgt'], false],
                ['slug', true], // true - уникальный индекс
            ],
        ],

        '{{%actors_tags}}' => [
            'columns' => [
                'id' => 'pk',
                'name' => 'string NOT NULL',
                'slug' => 'string NOT NULL',
            ],
            'comments' => [
                'id' => 'pk',
                'name' => 'Название тега',
                'slug' => 'Slug тега',
            ],
            'comment' => 'Теги актёра',
            'indexes' => [
                // [['column_1', 'column_2', ...], isUnique(false), isPK(false)]
                ['slug', true],
            ],
        ],

        '{{%actors_actors}}' => [
            'columns' => [
                'id' => 'pk',
                'taxonomy_id' => 'integer NOT NULL',
                'created_at' => 'timestamp NULL DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'name' => 'string NOT NULL',
                'description' => 'text NULL DEFAULT NULL',
                'main_image_id' => 'integer NULL DEFAULT NULL',
                'status' => 'tinyint(1) NOT NULL DEFAULT 0',
                'meta_json' => 'TEXT NOT NULL',
            ],
            'comments' => [
                'id' => 'Идентификатор актёра',
                'taxonomy_id' => 'Идентификатор категории актёра',
                'created_at' => 'Дата создания актёра',
                'updated_at' => 'Дата последнего редактирования актёра',
                'name' => 'Название актёра',
                'description' => 'Описание актёра',
                'main_image_id' => 'Идентификатор основной фотографии актёра',
                'status' => 'Статус отображения актёра',
                'meta_json' => 'JSON meta',
            ],
            'comment' => 'Актёры',
            'indexes' => [
                // [['column_1', 'column_2', ...], isUnique(false), isPK(false)]
                ['taxonomy_id',],
                ['main_image_id',],
            ],
            'foreignKeys' => [
                // [['column_1', 'column_2', ...], 'ref_table', 'ref_columns', 'ON DELETE', 'ON UPDATE'],
                [['taxonomy_id'], '{{%actors_taxonomies}}', 'id'],
                [['main_image_id'], '{{%actors_images}}', 'id', 'SET NULL'],
            ],
        ],

        '{{%actors_images}}' => [
            'columns' => [
                'id' => 'pk',
                'actor_id' => 'integer NOT NULL',
                'file' => 'string NOT NULL',
                'sort' => 'integer NOT NULL',
            ],
            'comments' => [
                'id' => '',
                'actor_id' => 'Id актёра, к которому принадлежит данное фото',
                'file' => 'Файл фотографии',
                'sort' => 'Сортировка фотографии у конкретного актёра',
            ],
            'comment' => 'Изображения для актёра',
            'indexes' => [
                // [['column_1', 'column_2', ...], isUnique(false), isPK(false)]
                ['actor_id'],
            ],
            'foreignKeys' => [
                // [['column_1', 'column_2', ...], 'ref_table', 'ref_columns', 'ON DELETE', 'ON UPDATE'],
                ['actor_id', '{{%actors_actors}}', 'id', 'CASCADE', 'CASCADE'],
            ],
        ],

        '{{%actors_tag_asgmt}}' => [
            'columns' => [
                'actor_id' => 'integer NOT NULL',
                'tag_id' => 'integer NOT NULL',
            ],
            'comments' => [
                'actor_id' => 'Идентификатор актёра',
                'tag_id' => 'Идентификатор тега',
            ],
            'comment' => 'Связь актёра с тегом',
            'indexes' => [
                // [['column_1', 'column_2', ...], isUnique(false), isPK(false)]
                [['actor_id', 'tag_id'], false, true],
                ['actor_id'],
                ['tag_id'],
            ],
            'foreignKeys' => [
                // [['column_1', 'column_2', ...], 'ref_table', 'ref_columns', 'ON DELETE', 'ON UPDATE'],
                [['actor_id'], '{{%actors_actors}}', 'id', 'CASCADE'],
                [['tag_id'], '{{%actors_tags}}', 'id', 'CASCADE'],
            ],
        ],

    ],
    'initialData' => [
        '{{%actors_taxonomies}}' => [
            [
                'id' => '1',
                'lft' => '1',
                'rgt' => '4',
                'depth' => '0',
                'tree' => '1',
                'name' => 'Актёры-1',
                'slug' => 'actors-1',
                'description' => '',
                'meta_json' => '{"title":"","description":"","keywords":""}',
                'status' => '1',
                'sort_order' => '1',
            ],
            [
                'id' => '2',
                'lft' => '2',
                'rgt' => '3',
                'depth' => '1',
                'tree' => '1',
                'name' => 'Актёры-2',
                'slug' => 'actors-2',
                'description' => '',
                'meta_json' => '{"title":"","description":"","keywords":""}',
                'status' => '1',
                'sort_order' => '0',
            ],
            [
                'id' => '3',
                'lft' => '1',
                'rgt' => '2',
                'depth' => '0',
                'tree' => '2',
                'name' => 'Актёры-3',
                'slug' => 'actors-3',
                'description' => '',
                'meta_json' => '{"title":"","description":"","keywords":""}',
                'status' => '1',
                'sort_order' => '2',
            ],
        ],
    ],
];
