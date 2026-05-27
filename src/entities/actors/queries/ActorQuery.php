<?php

namespace Besnovatyj\Actors\entities\actors\queries;

use Besnovatyj\Actors\entities\actors\Actor;
use yii\db\ActiveQuery;

class ActorQuery extends ActiveQuery
{
    /**
     * @param null $alias
     * @return $this
     */
    public function active($alias = null): static
    {
        return $this->andWhere([
            ($alias ? $alias . '.' : '') . 'status' => Actor::STATUS_ACTIVE,
        ]);
    }
}
