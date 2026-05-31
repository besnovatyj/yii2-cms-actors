<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\controllers\frontend;

use Besnovatyj\Actors\readModels\TaxonomyReadRepository;
use Besnovatyj\Actors\readModels\ActorReadRepository;
use Besnovatyj\Actors\readModels\TagReadRepository;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ActorController extends Controller
{
    private ActorReadRepository $actors;
    private TaxonomyReadRepository $taxonomies;
    private TagReadRepository $tags;

    public function __construct(
        $id,
        $module,
        ActorReadRepository $actors,
        TaxonomyReadRepository $taxonomies,
        TagReadRepository $tags,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->actors = $actors;
        $this->taxonomies = $taxonomies;
        $this->tags = $tags;
    }

    public function actionIndex(): string
    {
        $dataProvider = $this->actors->getAll();
        $taxonomy = $this->taxonomies->getRoot();

        return $this->render('/frontend/actor/index', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTaxonomy(string $slug): string
    {
        if (!$taxonomy = $this->taxonomies->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->actors->getAllByTaxonomy($taxonomy);

        return $this->render('/frontend/actor/taxonomy', [
            'taxonomy' => $taxonomy,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTag(string $slug): string
    {
        if (!$tag = $this->tags->findBySlug($slug)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $dataProvider = $this->actors->getAllByTag($tag);

        return $this->render('/frontend/actor/tag', [
            'tag' => $tag,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        if (!$actor = $this->actors->find($id)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('/frontend/actor/view', [
            'actor' => $actor,
        ]);
    }
}
