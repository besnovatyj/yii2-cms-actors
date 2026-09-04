<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Все опции должны быть изначально определены при в конфигурации модуля при подключении в приложение.
return [
    'actors_sort_preview_scale' => [
        'path' => 'modules.Actors.params.sort_preview_scale',
        'label' => 'Множитель превью на экране порядка',
        'description' => 'Во сколько раз увеличить фотографии на экране «Порядок»: 1 — как есть, 2 — вдвое больше, 0.5 — половина. Допустимо от 0.25 до 4.',
        'group' => '',
        'category' => 'Actors',
        'rules' => [
            ['required'],
            ['number', 'min' => 0.25, 'max' => 4],
        ],
        'inputOptions' => [
            'type' => 'input',
        ],
    ],
];
