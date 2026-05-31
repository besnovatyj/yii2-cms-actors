<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\forms\backend\search\ActorSearch;
use Besnovatyj\Actors\helpers\ActorHelper;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel ActorSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Actors';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a('Create', ['create'], ['class' => 'btn  btn-success']) ?>
</p>

<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}",
            'columns' => [
                [
                    'value' => static function (Actor $model) {
                        return $model->mainImage ? Html::img($model->mainImage->getThumbUrl('file', 'admin')) : null;
                    },
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width: 100px'],
                ],
                'id',
                [
                    'attribute' => 'name',
                    'value' => static function (Actor $model) {
                        return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'taxonomy_id',
                    'filter' => new TreeQueryScope(Taxonomy::class)->dropdownTree(),
                    'value' => 'taxonomy.name',
                ],
                [
                    'attribute' => 'status',
                    'filter' => $searchModel->statusList(),
                    'value' => function (Actor $model) {
                        return ActorHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <nav aria-label="" class="nav-pagination">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </nav>
    </div>
</div>
<!-- /.card -->
