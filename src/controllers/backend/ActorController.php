<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Actors\controllers\backend;

use Besnovatyj\Actors\entities\actors\Image;
use Besnovatyj\Actors\forms\backend\actors\ActorForm;
use Besnovatyj\Actors\forms\backend\search\ActorSearch;
use Besnovatyj\Actors\image\ActorImageOwner;
use Besnovatyj\Actors\repositories\ActorRepository;
use Besnovatyj\Actors\services\manage\ActorManageService;
use Besnovatyj\Actors\services\manage\ActorSortService;
use Besnovatyj\Images\helpers\ImageActionsMap;
use Besnovatyj\Kernel\controller\ControllerTrait;
use Besnovatyj\Kernel\urlmanager\UrlManagerHelperTrait;
use DomainException;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ActorController extends Controller
{
    use ControllerTrait;
    use UrlManagerHelperTrait;

    /**
     * Границы множителя размера превью на экране порядка.
     *
     * Значение приходит из динамической настройки модуля (модуль Config), поэтому
     * зажимается: за пределами этого диапазона список либо нечитаем, либо не помещается на экран.
     */
    private const float PREVIEW_SCALE_MIN = 0.25;
    private const float PREVIEW_SCALE_MAX = 4.0;

    private ActorManageService $service;
    private ActorSortService $sortService;
    private ActorRepository $actorsRepo;

    public function __construct(
        $id,
        $module,
        ActorManageService $service,
        ActorSortService $sortService,
        ActorRepository $actorsRepo,
        $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->sortService = $sortService;
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
                    'set-order' => ['POST'],
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
     * Экран ручного порядка актёров: весь список одной страницей, без фильтров и пагинации.
     *
     * Порядок задаётся перетаскиванием, поэтому выборка не должна быть ни отфильтрованной,
     * ни постраничной — иначе перетаскивание меняло бы позицию относительно невидимых записей.
     */
    public function actionSort(): string
    {
        return $this->render('sort', [
            'actors' => $this->actorsRepo->allInOrder(),
            'previewScale' => $this->previewScale(),
        ]);
    }

    /**
     * Принимает новый порядок от виджета сортировки.
     *
     * @return array{status: string}
     *
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionSetOrder(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $order = Yii::$app->request->post('order');
        if (!is_array($order) || $order === []) {
            throw new BadRequestHttpException('Ожидается непустой массив "order".');
        }

        foreach ($order as $id) {
            if (!is_scalar($id) || !ctype_digit((string)$id)) {
                throw new BadRequestHttpException('Массив "order" должен содержать только идентификаторы.');
            }
        }

        try {
            $this->sortService->reorder($order);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            throw new ServerErrorHttpException('Не удалось сохранить порядок актёров.');
        }

        return ['status' => 'success'];
    }

    /**
     * Множитель размера превью для экрана порядка.
     *
     * Настраивается в админке (модуль Config), путь `modules.Actors.params.sort_preview_scale`.
     */
    private function previewScale(): float
    {
        $scale = (float)($this->module->params['sort_preview_scale'] ?? 1.0);

        return max(self::PREVIEW_SCALE_MIN, min(self::PREVIEW_SCALE_MAX, $scale));
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
