<?php

namespace Besnovatyj\Actors\controllers\backend;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\forms\backend\TaxonomyForm;
use Besnovatyj\TreeManager\Manager\controllers\TreeController;
use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Yii;

/**
 * Контроллер для управления деревом
 */
class TaxonomyController extends TreeController
{
    public function __construct($id, $module, $config = [])
    {
        $this->treeManager = Yii::$container->get('actors.tree.manager');
        $this->dataSource = new TreeDataSource(
            Taxonomy::class,
            function (Taxonomy $model) {
                return [
                    'id' => $model->id,
                    'title' => $model->name,
                    'slug' => $model->slug,
                ];
            },
            'sort_order'
        );
        $this->createFormClass = TaxonomyForm::class;
        $this->updateFormClass = TaxonomyForm::class;
        $this->formView = '_form';
        $this->indexTitle = 'Управление категориями';
        parent::__construct($id, $module, $config);
    }
}
