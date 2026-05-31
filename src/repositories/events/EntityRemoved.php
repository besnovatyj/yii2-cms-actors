<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors\repositories\events;

class EntityRemoved
{
    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
