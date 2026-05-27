<?php

namespace Besnovatyj\Actors\helpers;

use Besnovatyj\Actors\entities\actors\Actor;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

class ActorHelper
{
    public static function statusList(): array
    {
        return [
            Actor::STATUS_DRAFT => 'Draft',
            Actor::STATUS_ACTIVE => 'Active',
        ];
    }

    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    public static function statusLabel($model): string
    {
        switch ($model->status) {
            case Actor::STATUS_DRAFT:
                $class = 'badge bg-secondary';
                $action = 'activate';
                break;
            case Actor::STATUS_ACTIVE:
                $class = 'badge bg-success';
                $action = 'draft';
                break;
            default:
                $class = 'badge bg-secondary';
                $action = 'activate';
        }

        $text = Html::tag('span', ArrayHelper::getValue(self::statusList(), $model->status), [
            'class' => $class,
        ]);
        $url = Url::to([$action, 'id' => $model->id]);
        return Html::a($text, $url, [
            'data' => [
//                'confirm' => "Сменить статус?",
                'method' => 'post',
            ],
        ]);

    }
}
