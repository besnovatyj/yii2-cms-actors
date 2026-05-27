<?php

declare(strict_types=1);

namespace Besnovatyj\Actors\controllers\backend;

use Besnovatyj\Actors\entities\actors\Image;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use Besnovatyj\Actors\forms\backend\search\ActorSearch;
use Besnovatyj\Actors\image\ActorImageOwner;
use Besnovatyj\Actors\repositories\ActorRepository;
use Besnovatyj\Actors\services\manage\ActorManageService;
use Besnovatyj\Images\helpers\ImageActionsMap;
use common\components\controller\ControllerTrait;
use common\components\urlmanager\UrlManagerHelperTrait;
use DomainException;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class ActorController extends Controller
{
    use ControllerTrait;
    use UrlManagerHelperTrait;

    private ActorManageService $service;
    private ActorRepository $actorsRepo;

    public function __construct(
        $id,
        $module,
        ActorManageService $service,
        ActorRepository $actorsRepo,
        $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->actorsRepo = $actorsRepo;
    }

    /**
     * Регистрирует standalone image-actions через ImageActionsMap.
     *
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return ImageActionsMap::get(
            Image::class,
            fn(int $id) => new ActorImageOwner($this->actorsRepo->get($id), $this->actorsRepo),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'activate' => ['POST'],
                    'draft' => ['POST'],
                    'delete' => ['POST'],
                    'add-image' => ['POST'],
                    'delete-image' => ['POST'],
                    'set-main-image' => ['POST'],
                    'get-images' => ['POST'],
                    'set-new-sort' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new ActorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     * @throws InvalidConfigException
     */
    public function actionView(int $id): Response|string
    {
        try {
            $absoluteFrontendUrl = $this->getAbsoluteFrontendRoute('/Actors/actor/view/', ['id' => $id]);
            return $this->render('view', [
                'actor' => $this->actorsRepo->get($id),
                'absoluteFrontendUrl' => $absoluteFrontendUrl,
            ]);
        } catch (DomainException $e) {
            $this->handleDomainException($e);
        }
        return $this->redirect(['index']);
    }

    /**
     * @return Response|string
     */
    public function actionCreate(): Response|string
    {
        $form = new ActorForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $actor = $this->service->create($form);
                return $this->redirect(['view', 'id' => $actor->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e);
            }
        }
        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id): Response|string
    {
        $actor = $this->actorsRepo->get($id);
        $form = new ActorForm($actor);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->edit($actor->id, $form);
                return $this->redirect(['view', 'id' => $actor->id]);
            } catch (Throwable $e) {
                $this->handleDomainException($e);
            }
        }

        return $this->render('update', [
            'model' => $form,
            'actor' => $actor,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (Throwable $e) {
            $this->handleDomainException($e);
        }
        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionActivate(int $id): Response
    {
        try {
            $this->service->activate($id);
        } catch (Exception $e) {
            $this->handleDomainException($e);
        }
        return $this->goReferer();
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDraft(int $id): Response
    {
        try {
            $this->service->draft($id);
        } catch (Exception $e) {
            $this->handleDomainException($e);
        }
        return $this->goReferer();
    }
}
