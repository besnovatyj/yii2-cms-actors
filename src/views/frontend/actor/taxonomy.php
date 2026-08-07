<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Module;
use yii\data\DataProviderInterface;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $taxonomy Taxonomy */
/* @var $dataProvider DataProviderInterface */

$this->title = $taxonomy->name;

$this->params['og:title'] = $this->title;

$this->params['breadcrumbs'] = new TreeQueryScope(Taxonomy::class)->breadcrumbs($taxonomy, urlCallback: function ($item) use ($taxonomy) {
    if ($item->id !== $taxonomy->id) {
        return Url::to(['taxonomy', 'slug' => $item->slug]);
    }
    return false;
});

$this->registerMetaTag(['name' => 'title', 'content' => $taxonomy->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $taxonomy->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $taxonomy->meta->description]);

if (Yii::$app->getModule('Config') instanceof Module) {
    $this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
}

?>

<section class="container my-4 my-lg-5">
    <?php if ($taxonomy->description): ?>
        <div class="mb-4 text-center">
            <h2 class="h4 text-secondary">
                <?= Yii::$app->formatter->asHtml($taxonomy->description) ?>
            </h2>
            <hr>
        </div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col">
                <?= $this->render('_item', [
                    'model' => $model
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-center">
        <?= $this->render("_pagination", ['dataProvider' => $dataProvider]) ?>
    </div>

</section>
