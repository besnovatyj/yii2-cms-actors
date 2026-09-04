<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\repositories;

use Besnovatyj\Actors\entities\actors\Actor;
use yii\db\Exception;
use yii\db\Expression;
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
     * Все актёры в порядке ручной сортировки — для экрана управления порядком.
     *
     * Пагинации нет намеренно: порядок задаётся по всему списку целиком.
     *
     * @return Actor[]
     */
    public function allInOrder(): array
    {
        return Actor::find()
            ->with('mainImage', 'taxonomy')
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * Карта `id => sort` всех актёров в текущем порядке.
     *
     * @return array<int, int>
     */
    public function sortMap(): array
    {
        $rows = Actor::find()
            ->select(['id', 'sort'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->asArray()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = (int)$row['sort'];
        }
        return $map;
    }

    /**
     * Позиция для нового актёра — в конец списка.
     *
     * Без этого все новые записи получают `sort = 0` и порядок между ними
     * определяется базой, то есть произвольно.
     */
    public function nextSort(): int
    {
        return (int)Actor::find()->max('sort') + 1;
    }

    /**
     * Присваивает позицию одной записи.
     *
     * `updated_at` переписывается своим же значением намеренно: у колонки объявлен
     * `ON UPDATE NOW()`, и без этого пересортировка выдавала бы всех актёров
     * за только что отредактированных.
     */
    public function updateSort(int $id, int $sort): void
    {
        Actor::updateAll(
            ['sort' => $sort, 'updated_at' => new Expression('[[updated_at]]')],
            ['id' => $id]
        );
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
