<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesAdminMenu;
use Besnovatyj\Contracts\module\ProvidesDependencies;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;

class Module extends CmsModule implements
    DeclaresModule, ProvidesMigrations,
    ProvidesAdminMenu, ProvidesOptions, ProvidesDependencies
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'Actors';

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }
    public static function adminMenu(): array { return require __DIR__.'/config/adminMenu.php'; }
    public static function options(): array { return require __DIR__.'/config/options.php'; }
    public static function dependencies(): array { return require __DIR__.'/config/dependencies.php'; }
    public static function migrationPath(): string { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }

}
