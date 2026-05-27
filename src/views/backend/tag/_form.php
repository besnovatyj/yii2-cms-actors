<?php

use Besnovatyj\Actors\forms\backend\TagForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model TagForm */
/* @var $form ActiveForm */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class'=>'form-control']) ?>
        <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'class'=>'form-control']) ?>
    </div>
    <div class="card-footer clearfix">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

