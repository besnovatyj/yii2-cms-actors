<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [

    // Actors
    [
        'label' => 'Actors',
        'iconClass' => 'bi bi-people me-1',
        'url' => ['/Actors/backend/actor/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Actors/backend/actor');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Actor',
                    'groupIcon' => 'bi bi-person-square',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Taxonomies
    [
        'label' => 'Taxonomies',
        'iconClass' => 'bi bi-list-ol me-1',
        'url' => ['/Actors/backend/taxonomy/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Actors/backend/taxonomy');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Actor',
                    'groupIcon' => 'bi bi-person-square',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

    // Tags
    [
        'label' => 'Tags',
        'iconClass' => 'bi bi-tags me-1',
        'url' => ['/Actors/backend/tag/index'],
        'active' => static function () {
            return str_contains(\Yii::$app->request->url, 'Actors/backend/tag');
        },
        '_meta' => [
            'placements' => [
                [
                    'location' => 'left-sidebar',
                    'group' => 'Actor',
                    'groupIcon' => 'bi bi-person-square',
                    'priority' => 100,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],

];
