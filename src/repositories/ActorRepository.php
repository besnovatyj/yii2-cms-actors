<?php

namespace Besnovatyj\Actors\repositories;

use Besnovatyj\Actors\entities\actors\Actor;
use yii\db\Exception;
use yii\db\StaleObjectException;

class ActorRepository
{

    public function get(int $id): Actor
    {
        if (!$actors = Actor::findOne($id)) {
            throw new NotFoundException('Actor is not found.');
        }
        return $actors;
    }

    public function existsByMainTaxonomy(int $id): bool
    {
        return Actor::find()->andWhere(['taxonomy_id' => $id])->exists();
    }

    /**
     * @throws Exception
     */
    public function save(Actor $actor)
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                if ($actor->save()) {
                    return true;
                }
                throw new \Exception('Failed to save actor.');
            } catch (\yii\db\Exception $e) {
                if ($e->errorInfo[1] == 1213) { // Код ошибки дедлока
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        throw $e; // Превышено количество попыток
                    }
                    usleep(rand(100, 500) * 1000); // Задержка 100-500 мс
                    continue;
                }
                throw $e; // Другие ошибки
            }
        }
    }

    /**
     * @throws StaleObjectException|\Throwable
     */
    public function remove(Actor $actors): void
    {
        if (!$actors->delete()) {
            throw new \RuntimeException('Removing error.');
        }
    }
}
