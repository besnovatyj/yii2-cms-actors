<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Contracts\adminMenu\AdminMenuLocation;
use Besnovatyj\Contracts\adminMenu\AdminMenuPlacement;

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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Actor',
                    groupIcon: 'bi bi-person-square',
                    groupPriority: 100,
                    priority: 100,
                ),
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
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Actor',
                    groupIcon: 'bi bi-person-square',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],

];
