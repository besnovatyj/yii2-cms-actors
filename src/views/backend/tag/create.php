<?php

use Besnovatyj\Actors\forms\backend\TagForm;

/* @var $this yii\web\View */
/* @var $model TagForm */

$this->title = 'Create tag';
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>
