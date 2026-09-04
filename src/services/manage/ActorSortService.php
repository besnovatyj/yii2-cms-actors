<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Actors\services\manage;

use Besnovatyj\Actors\repositories\ActorRepository;
use Throwable;
use Yii;

/**
 * Сервис ручного порядка актёров.
 *
 * Порядок хранится сквозным для всей таблицы: актёров десятки, отдельный порядок
 * внутри каждой таксономии дал бы два конкурирующих порядка при нулевой пользе —
 * относительный порядок внутри категории следует из общего.
 */
class ActorSortService
{
    private ActorRepository $actors;

    public function __construct(ActorRepository $actors)
    {
        $this->actors = $actors;
    }

    /**
     * Перенумеровывает весь список по присланному порядку.
     *
     * Список нумеруется целиком (1..N), а не «сдвигом соседей»: при полусотне записей
     * это одна короткая транзакция, зато исключены дыры и дубли позиций, из-за которых
     * порядок со временем вырождается в произвольный.
     *
     * @param array<int|string> $ids идентификаторы в новом порядке
     *
     * @throws Throwable
     */
    public function reorder(array $ids): void
    {
        $requested = array_values(array_unique(array_map(static fn($id): int => (int)$id, $ids)));

        // Единственный источник состава списка — база: присланные id, которых там нет,
        // отбрасываются, а недостающие (созданные, пока страница была открыта)
        // дописываются в конец в своём текущем порядке
        $current = $this->actors->sortMap();
        $known = array_keys($current);

        $order = array_values(array_intersect($requested, $known));
        $order = [...$order, ...array_values(array_diff($known, $order))];

        if ($order === []) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $position = 1;
            foreach ($order as $id) {
                if ($current[$id] !== $position) {
                    $this->actors->updateSort($id, $position);
                }
                $position++;
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
