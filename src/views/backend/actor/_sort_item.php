<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\helpers\ActorHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $actor Actor */
/* @var $thumbProfile string Имя профиля превью главной фотографии */

// Базовый размер превью; множитель применяет виджет через --sortable-list-scale
$previewStyle = '--sortable-list-preview-width: 70px; --sortable-list-preview-height: 100px;';
?>
<div class="d-flex align-items-center gap-3">
    <?php if ($actor->mainImage): ?>
        <?= Html::img($actor->mainImage->getThumbUrl('file', $thumbProfile), [
            'class' => 'sortable-list__preview',
            'style' => $previewStyle,
            'alt' => '',
            'loading' => 'lazy',
        ]) ?>
    <?php else: ?>
        <span class="sortable-list__preview d-flex align-items-center justify-content-center text-muted"
              style="<?= $previewStyle ?>">
            <i class="bi bi-person"></i>
        </span>
    <?php endif; ?>

    <div style="min-width: 0;"><!-- min-width для работы text-truncate во flex-строке -->
        <div class="text-truncate">
            <?= Html::a(Html::encode($actor->name), ['view', 'id' => $actor->id]) ?>
        </div>
        <small class="d-block text-muted">
            <?= Html::encode($actor->taxonomy?->name ?? '—') ?>
        </small>
        <small class="d-block"><?= ActorHelper::statusLabel($actor) ?></small>
    </div>
</div>
