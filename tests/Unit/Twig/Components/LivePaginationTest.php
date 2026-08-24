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

use App\Repository\JerseyRepository;
use App\Twig\Components\LivePagination;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\UX\Pagination\Adapter\ArrayPaginationAdapter;
use Symfony\UX\Pagination\Paginator;
use Symfony\UX\Pagination\PaginatorInterface;

final class LivePaginationTest extends TestCase
{
    public function testAColorCanBeSelected()
    {
        $component = new LivePagination(
            $this->createStub(PaginatorInterface::class),
            new JerseyRepository(),
        );
        $component->page = 8;

        $component->toggleFilter('color', 'pink');

        self::assertSame('pink', $component->color);
        self::assertSame(1, $component->page);
    }

    public function testAFilterCanBeClearedAndAlwaysResetsThePage()
    {
        $component = new LivePagination(
            $this->createStub(PaginatorInterface::class),
            new JerseyRepository(),
        );
        $component->pattern = 'halves';
        $component->page = 4;

        $component->toggleFilter('pattern', 'halves');

        self::assertSame('', $component->pattern);
        self::assertSame(1, $component->page);
    }

    public function testUnknownFiltersAreIgnored()
    {
        $component = new LivePagination(
            $this->createStub(PaginatorInterface::class),
            new JerseyRepository(),
        );
        $component->page = 3;

        $component->toggleFilter('missing', 'value');

        self::assertSame(3, $component->page);
    }

    public function testPaginationLinksContainTheActiveLiveFilters()
    {
        $routes = new RouteCollection();
        $routes->add('pagination', new Route('/pagination/{page}', defaults: ['page' => 1]));
        $paginator = new Paginator(
            [new ArrayPaginationAdapter()],
            requestStack: new RequestStack(),
            urlGenerator: new UrlGenerator($routes, new RequestContext()),
        );
        $component = new LivePagination($paginator, new JerseyRepository());
        $component->paginationRoute = 'pagination';
        $component->color = 'pink';
        $component->pattern = 'halves';

        self::assertSame('/pagination/2?color=pink&pattern=halves', $component->getPagination()->getUrl(2));
    }
}
