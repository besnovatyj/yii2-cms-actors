<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Actors;

use Besnovatyj\Contracts\module\ProvidesDirectories;
use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesAdminMenu;
use Besnovatyj\Contracts\module\ProvidesDependencies;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;
use Besnovatyj\Contracts\menu\MenuTarget;
use Besnovatyj\Contracts\menu\MenuTargetProvider;
use Besnovatyj\Contracts\search\SearchSource;
use Besnovatyj\Contracts\search\SearchableProvider;
use Besnovatyj\Contracts\sitemap\ChangeFrequency;
use Besnovatyj\Contracts\sitemap\SitemapFreshness;
use Besnovatyj\Contracts\sitemap\SitemapProvider;
use Besnovatyj\Contracts\sitemap\SitemapSection;
use Besnovatyj\Contracts\sitemap\SitemapUrl;
use Besnovatyj\Actors\entities\Taxonomy;
use Besnovatyj\Actors\readModels\ActorReadRepository;
use Besnovatyj\Actors\readModels\TaxonomyReadRepository;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;

class Module extends CmsModule implements
    DeclaresModule, ProvidesMigrations,
    ProvidesAdminMenu, ProvidesOptions,
    ProvidesDependencies, ProvidesDirectories, MenuTargetProvider, SearchableProvider,
    SitemapProvider, SitemapFreshness
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
    public static function directories(): array { return ['@static/origin/Actors','@static/cache/Actors'];}

    /**
     * Цели для построения пунктов меню. Реализация {@see MenuTargetProvider};
     * вызывается только модулем меню, если он установлен.
     *
     * @return MenuTarget[]
     */
    public function menuTargets(): array
    {
        return [
            new MenuTarget('/Actors/actor/taxonomy', 'Таксономия актёров', 'slug'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string,string>
     */
    public function menuCandidates(string $route): array
    {
        return match (ltrim($route, '/')) {
            'Actors/actor/taxonomy' => $this->taxonomySlugMap(),
            default => [],
        };
    }

    /**
     * Карта `slug => подпись` (с отступом по глубине дерева) для таксономий актёров.
     *
     * @return array<string,string>
     */
    private function taxonomySlugMap(): array
    {
        return new TreeQueryScope(Taxonomy::class)->dropdownTree(keyAttribute: 'slug', indent: '— ');
    }

    /**
     * Контент модуля для сквозного поиска. Реализация {@see SearchableProvider}; вызывается
     * только модулем поиска, если он установлен.
     *
     * @return SearchSource[]
     */
    public function searchSources(): array
    {
        return [
            new SearchSource('actors.actor', 'Актёры', 1.0, 'bi bi-person-badge'),
            new SearchSource('actors.taxonomy', 'Разделы актёров', 0.7, 'bi bi-diagram-3'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchDocuments(string $type): iterable
    {
        return match ($type) {
            'actors.actor' => new ActorReadRepository()->searchDocuments(),
            'actors.taxonomy' => new TaxonomyReadRepository()->searchDocuments(),
            default => [],
        };
    }


    /**
     * Разделы карты сайта. Реализация {@see SitemapProvider}; вызывается только модулем карты,
     * если он установлен.
     *
     * Разделов два, и это не дублирование: «Актёры» — навигационная ветка (список и его разделы),
     * «Все актёры» — сами персоналии. Каждый режется в свой файл, включается и взвешивается
     * отдельно, а на человеческой карте даёт свой блок.
     *
     * @return SitemapSection[]
     */
    public function sitemapSections(): array
    {
        return [
            new SitemapSection(
                key: 'actors.taxonomy',
                label: 'Актёры',
                changeFrequency: ChangeFrequency::Weekly,
                priority: 0.6,
                order: 60,
                icon: 'bi bi-diagram-3',
            ),
            new SitemapSection(
                key: 'actors.actor',
                label: 'Все актёры',
                changeFrequency: ChangeFrequency::Monthly,
                priority: 0.6,
                order: 65,
                icon: 'bi bi-person-badge',
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sitemapUrls(string $section): iterable
    {
        return match ($section) {
            'actors.actor' => new ActorReadRepository()->sitemapUrls(),
            'actors.taxonomy' => $this->taxonomySitemapUrls(),
            default => [],
        };
    }

    /**
     * {@inheritdoc}
     *
     * Отпечаток есть только у персоналий: в дереве разделов колонок времени нет
     * (см. {@see TaxonomyReadRepository::sitemapUrls()}).
     */
    public function sitemapRevision(string $section): ?string
    {
        return match ($section) {
            'actors.actor' => new ActorReadRepository()->sitemapRevision(),
            default => null,
        };
    }

    /**
     * Разделы актёров, а перед ними — сам список.
     *
     * Список — корень ветки и для робота, и для читателя: на человеческой карте он открывает блок,
     * в XML это обычный адрес с высоким приоритетом. Отдельным разделом карты его заводить незачем —
     * раздел из одного адреса только засоряет и настройки, и индекс файлов.
     *
     * @return iterable<SitemapUrl>
     */
    private function taxonomySitemapUrls(): iterable
    {
        yield new SitemapUrl(
            route: '/Actors/actor/index',
            title: 'Актёры',
            changeFrequency: ChangeFrequency::Weekly,
            priority: 0.9,
        );

        yield from new TaxonomyReadRepository()->sitemapUrls();
    }
}
