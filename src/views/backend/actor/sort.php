<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Backend\Widgets\sortable\SortableList;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $actors Actor[] */
/* @var $previewScale float */

$this->title = 'Порядок актёров';
$this->params['breadcrumbs'][] = ['label' => 'Actors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

/**
 * При увеличении превью берётся крупный профиль: превью админки — 70×100,
 * растянутое до двойного размера, выглядело бы мыльным.
 */
$thumbProfile = $previewScale > 1 ? 'thumb' : 'admin';
?>

<p>
    <?= Html::a('К списку', ['index'], ['class' => 'btn btn-secondary']) ?>
</p>

<div class="card">
    <div class="card-header"><?= Html::encode($this->title) ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <p class="text-muted">
            Перетащите строку за ручку слева или переставьте кнопками «выше»/«ниже»
            (с клавиатуры — Alt+↑ и Alt+↓). Порядок сохраняется автоматически.
        </p>

        <?= SortableList::widget([
            'saveUrl' => ['set-order'],
            'previewScale' => $previewScale,
            'emptyText' => 'Актёров пока нет — сортировать нечего.',
            'items' => array_map(fn(Actor $actor): array => [
                'id' => $actor->id,
                'content' => $this->render('_sort_item', [
                    'actor' => $actor,
                    'thumbProfile' => $thumbProfile,
                ]),
            ], $actors),
        ]) ?>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
