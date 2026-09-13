<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Actors\Module;
use Besnovatyj\Validators\SlugValidator;

/**
 * Yii2-конфиг модуля для движка yiisoft/config (группа `common` — общий для всех приложений).
 *
 * Объявляется через `extra.config-plugin`, собирается modman в merge-plan и мёржится в рантайме.
 * Содержит регистрацию модуля. Меню (adminMenu) и миграции остаются вкладами modman. Значения берутся
 * из статических методов {@see Module} — единый источник, без дублирования.
 *
 * URL-правила фронтенда — вклад в `frontendUrlManager` группы `common` (компонент есть и во фронте, и в
 * бэкенде). Плоская грамматика, общая для контентных модулей: `<prefix>` — список, `<prefix>/<id:\d+>` —
 * материал (всегда число), `<prefix>/<slug>` — раздел (лист дерева, без предков: слаг уникален по таблице).
 * Паттерны слагов — только из констант {@see SlugValidator}: STRICT (первый символ — буква) там, где слаг
 * делит сегмент с `<id:\d+>`, ANY — в собственном сегменте (`tag/…`). Гейтятся modman.
 */
return [
    'modules' => [
        Module::moduleId() => array_merge(
            ['class' => Module::class],
            Module::moduleConfig(),
            ['version' => Module::moduleVersion()],
        ),
    ],
    'components' => [
        'frontendUrlManager' => [
            'rules' => [
                'actors'                                                     => 'Actors/actor/index',
                'actors/tag/<slug:' . SlugValidator::SLUG_ANY . '>/<page:\d+>' => 'Actors/actor/tag', // <page> — пагинация
                'actors/tag/<slug:' . SlugValidator::SLUG_ANY . '>'            => 'Actors/actor/tag',
                // Карточка актёра — по числовому id; «красивый» адрес (`actors/anatoly-butor`) — алиас
                // модуля route-alias (см. Module::aliasTargets()), а не слаг сущности.
                'actors/<id:\d+>'                                            => 'Actors/actor/view',
                'actors/<slug:' . SlugValidator::SLUG_STRICT . '>/<page:\d+>' => 'Actors/actor/taxonomy', // <page> — пагинация
                'actors/<slug:' . SlugValidator::SLUG_STRICT . '>'            => 'Actors/actor/taxonomy',
            ],
        ],
    ],
];
