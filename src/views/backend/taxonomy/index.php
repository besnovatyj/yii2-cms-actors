<?php

use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Besnovatyj\TreeManager\Manager\TreeWidget;
use Besnovatyj\Actors\forms\backend\TaxonomyForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var string $title
 * @var TreeDataSource $treeDataSource
 */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="taxonomy-tree-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="btn-group">
            <?= Html::a(
                '<i class="bi bi-list-ul"></i> Список',
                ['/Actors/backend/taxonomy/index'],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= TreeWidget::widget([
                'dataSource' => $treeDataSource,
                'endpoints' => [
                    'loadChildren' => Url::to(['/Actors/backend/taxonomy/load-children']),
                    'createNode' => Url::to(['/Actors/backend/taxonomy/create']),
                    'updateNode' => Url::to(['/Actors/backend/taxonomy/update']),
                    'deleteNode' => Url::to(['/Actors/backend/taxonomy/delete']),
                    'moveNode' => Url::to(['/Actors/backend/taxonomy/move']),
                    'toggleStatus' => Url::to(['/Actors/backend/taxonomy/toggle-status']),
                    'checkIntegrity' => Url::to(['/Actors/backend/taxonomy/check-integrity']),
                ],
//                'forms' => [
//                    'createFormClass' => TaxonomyForm::class,
//                    'updateFormClass' => TaxonomyForm::class,
//                ],
                'serverForms' => [
                    'enabled' => true,
                    'display' => 'modal',
                    'errorStrategy' => 'both',
                    'operations' => [
                        'create' => true,
                        'edit' => true,
                    ],
                    'getFormUrl' => Url::to(['/Actors/backend/taxonomy/get-form']),
                ],
                'permissions' => [
                    'canCreate' => true, // Yii::$app->user->can('create'),
                    'canUpdate' => true, // Yii::$app->user->can('update'),
                    'canDelete' => true, // Yii::$app->user->can('delete'),
                    'canMove' => true, // Yii::$app->user->can('move'),
                ],
                'titleField' => 'title',
                'enablePersistence' => true,
                'storageKey' => 'actors-taxonomy-tree-state',
                'containerOptions' => [
                    'class' => 'actors-taxonomy-tree-widget',
                ],
            ]) ?>
        </div>
    </div>
</div>

<?php
// Дополнительные стили
$this->registerCss(<<<CSS
.actors-taxonomy-tree-widget {
    min-height: 400px;
}
CSS
);
?>
