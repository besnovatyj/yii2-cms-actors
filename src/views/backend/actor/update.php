<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use yii\web\View;

/* @var $this View */
/* @var $actor Actor */
/* @var $model ActorForm */

$this->title = 'Update actor: ' . $actor->name;
$this->params['breadcrumbs'][] = ['label' => 'Actors', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $actor->name, 'url' => ['view', 'id' => $actor->id]];
$this->params['breadcrumbs'][] = 'Update';

echo $this->render('_form', [
    'model' => $model,
    'actor' => $actor,
]);
