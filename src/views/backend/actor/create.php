<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use yii\web\View;

/* @var $this View */
/* @var $model ActorForm */

$this->title = 'Create';
$this->params['breadcrumbs'][] = ['label' => 'Actors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', ['model' => $model]);
