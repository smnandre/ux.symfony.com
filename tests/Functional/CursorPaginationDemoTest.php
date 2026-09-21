<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;

final class CursorPaginationDemoTest extends KernelTestCase
{
    use HasBrowser;

    public function testDemoRendersCursorSlice(): void
    {
        $page = $this->browser()
            ->visit('/demos/pagination/cursor-pagination')
            ->assertSuccessful()
            ->assertSeeIn('h1', 'Cursor Pagination')
            ->assertSee('20 ordered values')
            ->assertSee('Visible slice')
        ;

        self::assertSame(20, $page->crawler()->filter('.CursorPagination_dot')->count());
        self::assertSame(5, $page->crawler()->filter('.CursorPagination_dot.is-active')->count());
        self::assertSame(1, $page->crawler()->filter('.CursorPagination_footer nav')->count());
        self::assertStringEndsWith('/demos/pagination/cursor-pagination', $page->crawler()->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testDemoIsLinkedFromPaginationListings(): void
    {
        foreach (['/pagination', '/demos/pagination'] as $url) {
            $page = $this->browser()->visit($url)->assertSuccessful();

            self::assertSame(1, $page->crawler()->filter('a[href="/demos/pagination/cursor-pagination"]')->count());
        }
    }

    public function testDemoIsInTheSitemap(): void
    {
        $page = $this->browser()->visit('/sitemap.xml')->assertSuccessful();

        self::assertStringContainsString('/demos/pagination/cursor-pagination</loc>', $page->content());
    }
}
