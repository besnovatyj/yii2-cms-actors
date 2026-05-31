<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\helpers\ActorHelper;
use Besnovatyj\Images\widgets\upload\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $actor Actor */
/* @var $absoluteFrontendUrl string */

$this->title = $actor->name;
$this->params['breadcrumbs'][] = ['label' => 'Actors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= Html::a('Create', ['create'], ['class' => 'btn  btn-success']) ?>
    <?php if ($actor->isActive()): ?>
        <?= Html::a('To draft', ['draft', 'id' => $actor->id], ['class' => 'btn  btn-warning', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('To active', ['activate', 'id' => $actor->id], ['class' => 'btn  btn-success', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?= Html::a('Update', ['update', 'id' => $actor->id], ['class' => 'btn  btn-primary']) ?>
    <?= Html::a('Delete', ['delete', 'id' => $actor->id], [
        'class' => 'btn  btn-danger',
        'data' => [
            'confirm' => 'Are you sure?',
            'method' => 'post',
        ],
    ]) ?>

    <a class="btn  btn-secondary" target="_blank"
       href="<?= $absoluteFrontendUrl; ?>">
        <i class="bi bi-eye"></i>
    </a>
</p>

<div class="container">
    <div class="row">
        <div class="col-sm">
            <!--COMMON-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Common</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-common" role="button"
                       aria-expanded="true" aria-controls="collapseCommon"></a>
                </div>
                <div class="collapse show" id="collapse-common">
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $actor,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'status',
                                    'value' => ActorHelper::statusLabel($actor),
                                    'format' => 'raw',
                                ],
                                'name',
                                [
                                    'attribute' => 'taxonomy_id',
                                    'value' => ArrayHelper::getValue($actor, 'taxonomy.name'),
                                ],
                                [
                                    'label' => 'Теги',
                                    'value' => implode(', ', ArrayHelper::getColumn($actor->tags, 'name')),
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <!--DESCRIPTION-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Description</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-description" role="button"
                       aria-expanded="true" aria-controls="collapseDescription"></a>
                </div>
                <div class="collapse show" id="collapse-description">
                    <div class="card-body">
                        <?= Yii::$app->formatter->asHtml($actor->description, [
                            'Attr.AllowedRel' => array('nofollow'),
                            'HTML.SafeObject' => true,
                            'Output.FlashCompat' => true,
                            'HTML.SafeIframe' => true,
                            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <!--SEO-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">SEO</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-SEO" role="button"
                       aria-expanded="true" aria-controls="collapseSEO"></a>
                </div>
                <div class="collapse show" id="collapse-SEO">
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $actor,
                            'attributes' => [
                                [
                                    'attribute' => 'meta.title',
                                    'value' => $actor->meta->title,
                                ],
                                [
                                    'attribute' => 'meta.description',
                                    'value' => $actor->meta->description,
                                ],
                                [
                                    'attribute' => 'meta.keywords',
                                    'value' => $actor->meta->keywords,
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <!--IMAGES-->
            <div class="card">
                <div class="card-header d-md-flex justify-content-md-between">
                    <div class="pt-1">Images</div>
                    <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#collapse-images" role="button"
                       aria-expanded="true" aria-controls="collapseImages"></a>
                </div>
                <div class="collapse show" id="collapse-images">
                    <div class="card-body">
                        <?= Widget::widget([
                            'ownerId'   => $actor->id,
                            'endpoints' => [
                                'getImages'    => Url::to(['/Actors/backend/actor/get-images'], true),
                                'setNewSort'   => Url::to(['/Actors/backend/actor/set-new-sort'], true),
                                'upload'       => Url::to(['/Actors/backend/actor/add-image'], true),
                                'deleteImage'  => Url::to(['/Actors/backend/actor/delete-image'], true),
                                'setMainImage' => '/Actors/backend/actor/set-main-image',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
