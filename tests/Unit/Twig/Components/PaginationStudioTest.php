<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Twig\Components;

use App\Twig\Components\PaginationStudio;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\UX\Pagination\Adapter\ArrayPaginationAdapter;
use Symfony\UX\Pagination\Cursor\CursorCodec;
use Symfony\UX\Pagination\NumberedPaginationInterface;
use Symfony\UX\Pagination\Paginator;

final class PaginationStudioTest extends TestCase
{
    public function testNumberedSetupUsesTheSelectedDefaults(): void
    {
        $studio = $this->createStudio();
        $studio->itemsPerPage = 12;
        $studio->maxOffset = 50000;
        $studio->mode = 'fixed';
        $studio->size = 7;

        self::assertSame(12, $studio->getPreview()->getItemsPerPage());
        self::assertStringContainsString('items_per_page: 12', $studio->getConfigurationSource());
        self::assertStringContainsString('max_offset: 50000', $studio->getConfigurationSource());
        self::assertStringContainsString('mode: fixed', $studio->getConfigurationSource());
        self::assertStringContainsString('size: 7', $studio->getConfigurationSource());
    }

    public function testLookaheadSetupDoesNotGenerateNumberedNavigationConfig(): void
    {
        $studio = $this->createStudio();
        $studio->strategy = 'lookahead';

        $pagination = $studio->getPreview();

        self::assertInstanceOf(NumberedPaginationInterface::class, $pagination);
        self::assertNull($pagination->getTotalItems());
        self::assertStringNotContainsString('navigation:', $studio->getConfigurationSource());
        self::assertStringContainsString('->lookahead()', $studio->getPhpSource());
    }

    public function testCursorSetupOnlyGeneratesRelevantConfiguration(): void
    {
        $studio = $this->createStudio();
        $studio->strategy = 'cursor';

        $pagination = $studio->getPreview();

        self::assertTrue($pagination->hasNext());
        self::assertNotNull($pagination->getNextUrl());
        self::assertStringNotContainsString('max_offset:', $studio->getConfigurationSource());
        self::assertStringNotContainsString('navigation:', $studio->getConfigurationSource());
        self::assertStringContainsString("->orderBy(['createdAt', 'id'], 'DESC')", $studio->getPhpSource());
    }

    /**
     * @param list<string> $expected
     * @param list<string> $unexpected
     */
    #[DataProvider('provideThemeOutputs')]
    public function testThemeOnlyGeneratesTheFilesItNeeds(
        string $theme,
        bool $includeCss,
        array $expected,
        array $unexpected,
    ): void {
        $studio = $this->createStudio();
        $studio->theme = $theme;
        $studio->includeCss = $includeCss;

        $outputs = array_keys($studio->getOutputs());

        foreach ($expected as $output) {
            self::assertContains($output, $outputs);
        }
        foreach ($unexpected as $output) {
            self::assertNotContains($output, $outputs);
        }
    }

    /**
     * @return iterable<string, array{string, bool, list<string>, list<string>}>
     */
    public static function provideThemeOutputs(): iterable
    {
        yield 'default with CSS' => ['default', true, ['assets'], ['theme', 'css']];
        yield 'default without CSS' => ['default', false, [], ['assets', 'theme', 'css']];
        yield 'Bootstrap' => ['bootstrap', true, [], ['assets', 'theme', 'css']];
        yield 'Tailwind' => ['tailwind', false, ['assets'], ['theme', 'css']];
        yield 'application with CSS' => ['application', true, ['assets', 'theme', 'css'], []];
        yield 'application without CSS' => ['application', false, ['theme'], ['assets', 'css']];
    }

    public function testEncoreInstallationIncludesTheNpmPackageWhenNeeded(): void
    {
        $studio = $this->createStudio();
        $studio->pipeline = 'encore';

        self::assertStringContainsString(
            'npm install @symfony/ux-pagination',
            $studio->getInstallationSource(),
        );

        $studio->theme = 'bootstrap';

        self::assertStringNotContainsString(
            'npm install',
            $studio->getInstallationSource(),
        );
    }

    public function testApplicationThemeGeneratesAThemeAndEscapedCssValue(): void
    {
        $studio = $this->createStudio();
        $studio->theme = 'application';
        $studio->accentColor = 'red; background: url(evil)';

        self::assertStringContainsString(
            "{% extends '@UXPagination/theme/default.html.twig' %}",
            $studio->getApplicationThemeSource(),
        );
        self::assertStringNotContainsString('evil', $studio->getCustomCssSource());
        self::assertStringContainsString('#78d64b', $studio->getCustomCssSource());
    }

    public function testFullNavigationUsesThePreviewPageCountAsItsSafetyLimit(): void
    {
        $studio = $this->createStudio();
        $studio->mode = 'full';
        $studio->totalItems = 240;
        $studio->itemsPerPage = 10;

        $pagination = $studio->getPreview();

        self::assertInstanceOf(NumberedPaginationInterface::class, $pagination);
        self::assertSame(24, $studio->getNavigationSize());
        self::assertStringContainsString('size: 24', $studio->getConfigurationSource());
        self::assertCount(24, $pagination->getPages());
    }

    public function testChangingADataDecisionResetsBothNavigationStates(): void
    {
        $studio = $this->createStudio();
        $studio->page = 8;
        $studio->cursor = 'opaque';

        $studio->resetPreview(240);

        self::assertSame(1, $studio->page);
        self::assertNull($studio->cursor);
    }

    public function testRemovingAnOptionalFileReturnsToConfigurationOutput(): void
    {
        $studio = $this->createStudio();
        $studio->theme = 'application';
        $studio->output = 'css';
        $studio->includeCss = false;

        $studio->resetOutput(true);

        self::assertSame('config', $studio->output);
    }

    private function createStudio(): PaginationStudio
    {
        $routes = new RouteCollection();
        $routes->add(
            'app_demo_live_component_pagination_studio',
            new Route('/demos/live-component/pagination-studio'),
        );

        return new PaginationStudio(new Paginator(
            [new ArrayPaginationAdapter()],
            requestStack: new RequestStack(),
            urlGenerator: new UrlGenerator($routes, new RequestContext()),
            cursorCodec: new CursorCodec('test-secret'),
        ));
    }
}
