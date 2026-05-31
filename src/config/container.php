<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Meta\Meta;
use Besnovatyj\TreeManager\Manager\entities\Node;
use Besnovatyj\TreeManager\Manager\forms\TreeNodeFormInterface;
use Besnovatyj\TreeManager\Manager\TreeManager;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

/**
 * Конфигурация DI контейнера для модуля Actors
 */
return function (\yii\di\Container $container): void {

    $container->setSingleton('actors.tree.manager', function () use ($container) {
        return new TreeManager(
            modelClass: Taxonomy::class,
            entityFactory: function (TreeNodeFormInterface $form): Taxonomy {
                return Taxonomy::create(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
            },
            entityUpdater: function (Node $node, TreeNodeFormInterface $form): Node {
                /** @var Taxonomy $node */
                $node->edit(
                    $form->name,
                    $form->slug,
                    $form->description,
                    new Meta(
                        $form->meta->title,
                        $form->meta->description,
                        $form->meta->keywords,
                    ),
                );
                return $node;
            },
        );
    });

    $container->setSingleton('actors.tree.scope', function () use ($container) {
        return new TreeQueryScope(Taxonomy::class);
    });
};
