<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Actor */

?>

<a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="card h-100 text-decoration-none shadow-sm overflow-hidden">
    <div class="ratio ratio-1x1">
        <img src="<?= $model->mainImage->getThumbUrl('file', 'frontend_list') ?>"
             class="card-img-top object-fit-cover" alt="<?= Html::encode($model->name) ?>"/>
    </div>
    <div class="card-body text-center">
        <h3 class="h6 card-title mb-0 text-body"><?= Html::encode($model->name) ?></h3>
    </div>
</a>
