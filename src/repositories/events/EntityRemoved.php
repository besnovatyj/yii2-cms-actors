<?php

namespace Besnovatyj\Actors\repositories\events;

class EntityRemoved
{
    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
