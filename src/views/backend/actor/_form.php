<?php

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use Besnovatyj\File\widgets\customeditor\src\CkeditorCustomWidget;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/* @var $this View */
/* @var $actor Actor */
/* @var $model ActorForm */
?>
<?php $form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
]); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-6">
            <!--MAIN-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Main</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-main" role="button"
                       aria-expanded="true" aria-controls="collapseMain">
                        <i class="bi bi-plus-lg"></i>
                        <i class="bi bi-dash-lg"></i>
                    </a>
                </div>
                <div class="collapse show" id="collapse-main">
                    <div class="card-body">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
                        <?= $form->field($model, 'status')->dropDownList($model->statusList(), ['prompt' => 'Не выбрано', 'class' => 'custom-select']) ?>
                        <?= $form->field($model->taxonomies, 'main')->dropDownList(new TreeQueryScope(Taxonomy::class)->dropdownTree(), ['prompt' => 'Не выбрано', 'class' => 'custom-select'])->label('Taxonomy') ?>
                    </div>
                    <div class="card-footer">
                        <div class="d-grid gap-2">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <!--COMMON-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Common</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-common" role="button"
                       aria-expanded="false" aria-controls="collapseCommon">
                        <i class="bi bi-plus-lg"></i>
                        <i class="bi bi-dash-lg"></i>
                    </a>
                </div>
                <div class="collapse" id="collapse-common">
                    <div class="card-body">
                        <?= $form->field($model->tags, 'newTagsNames')->widget(\Besnovatyj\Select2\Select2Widget::class, [
                            'endpoint' => \yii\helpers\Url::to(['/Actors/backend/tag/search-endpoint'], true),
                            'options' => ['class' => ''],
                        ])->label('Tags') ?>

                        <?php
                        if (!isset($actor)) {
                            echo '<div class="alert alert-danger" role="alert">Перед заполнением контента сохраните.</div>';
                        } else {
                            // TODO создавать папку при создании. При удалении удалять.
                            $editorConfig = [];
                            $editorConfig['language'] = 'ru';
                            $editorConfig['fmDefaultPath'] = '/origin/Actors/' . $actor->id;
                            echo $form->field($model, 'description')->widget(\Besnovatyj\File\widgets\customeditor\src\CkeditorCustomWidget::class, $editorConfig);
                        }
                        ?>

                    </div>
                    <div class="card-footer">
                        <div class="d-grid gap-2">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--SEO-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">SEO</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-SEO" role="button"
                       aria-expanded="false" aria-controls="collapseSEO">
                        <i class="bi bi-plus-lg"></i>
                        <i class="bi bi-dash-lg"></i>
                    </a>
                </div>
                <div class="collapse" id="collapse-SEO">
                    <div class="card-body">
                        <?= $form->field($model->meta, 'title')->textInput(['class' => 'form-control']) ?>
                        <?= $form->field($model->meta, 'description')->textarea(['rows' => 2, 'class' => 'form-control']) ?>
                        <?= $form->field($model->meta, 'keywords')->textInput(['class' => 'form-control']) ?>
                    </div>
                    <div class="card-footer">
                        <div class="d-grid gap-2">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
