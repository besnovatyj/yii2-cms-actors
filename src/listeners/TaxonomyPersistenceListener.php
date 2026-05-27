<?php

namespace Besnovatyj\Actors\listeners;

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\repositories\events\EntityPersisted;
use yii\caching\Cache;
use yii\caching\TagDependency;

class TaxonomyPersistenceListener
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function handle(EntityPersisted $event): void
    {
        if ($event->entity instanceof Taxonomy) {
            TagDependency::invalidate($this->cache, ['actors_cat']);
        }
    }
}
