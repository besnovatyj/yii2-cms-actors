<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $actor Actor */

$this->title = $actor->name;

$this->params['og:title'] = $this->title;

$taxonomy = $actor->taxonomy;
$this->params['breadcrumbs'] = new TreeQueryScope(Taxonomy::class)->breadcrumbs($taxonomy, urlCallback: function ($item) use ($taxonomy) {
    return Url::to(['taxonomy', 'slug' => $item->slug]);
});
$this->params['breadcrumbs'][] = $actor->name;

$this->registerMetaTag(['name' => 'title', 'content' => $actor->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $actor->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $actor->meta->description]);

if (Yii::$app->getModule('Config') instanceof Module) {
    $this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
}
?>

<section class="container my-4 my-lg-5">
    <h1 class="h2 mb-4"><?= Html::encode($actor->name) ?></h1>

    <div class="row g-4 g-lg-5">
        <div class="col-12 col-md-5 col-lg-4">
            <?php if (is_array($actor->images) && $actor->images !== []): ?>
                <?php $posterKey = array_key_first($actor->images); ?>
                <?php $poster = $actor->images[$posterKey]; ?>
                <a href="<?= $poster->getUploadUrl('file') ?>"
                   class="d-block position-relative overflow-hidden rounded-3 shadow-sm"
                   target="_blank" rel="noopener">
                    <img src="<?= $poster->getThumbUrl('file', 'thumb') ?>"
                         class="img-fluid w-100"
                         alt="<?= Html::encode($actor->name) ?>"/>
                    <span class="position-absolute bottom-0 start-0 end-0 p-3
                                 bg-dark bg-opacity-50 text-white fw-semibold text-center">
                        <?= Html::encode($actor->name) ?>
                    </span>
                </a>

                <?php // Остальные фото — простая сетка миниатюр ?>
                <div class="row g-2 mt-1">
                    <?php foreach ($actor->images as $i => $image): ?>
                        <?php if ($i === $posterKey): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <div class="col-4">
                            <a href="<?= $image->getUploadUrl('file') ?>"
                               class="d-block rounded overflow-hidden shadow-sm"
                               target="_blank" rel="noopener">
                                <img src="<?= $image->getThumbUrl('file', 'thumb') ?>"
                                     class="img-fluid w-100"
                                     alt="<?= Html::encode($actor->name) ?>"/>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ratio ratio-1x1 bg-body-secondary rounded-3 d-flex align-items-center justify-content-center">
                    <span class="text-secondary">Нет фото</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md-7 col-lg-8">
            <div class="fs-5 lh-base">
                <?= Yii::$app->formatter->asHtml($actor->description, [
                    'Attr.AllowedRel' => ['nofollow'],
                    'HTML.SafeObject' => true,
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                ]) ?>
            </div>

            <?php if ($actor->tags !== []): ?>
                <hr class="my-4">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-secondary me-1">Теги:</span>
                    <?php foreach ($actor->tags as $tag): ?>
                        <a href="<?= Html::encode(Url::to(['tag', 'slug' => $tag->slug])) ?>"
                           class="badge rounded-pill text-bg-light border text-decoration-none link-dark">
                            <?= Html::encode($tag->name) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
