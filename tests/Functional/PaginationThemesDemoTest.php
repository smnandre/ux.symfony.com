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

final class PaginationThemesDemoTest extends KernelTestCase
{
    use HasBrowser;

    public function testDemoRendersThreeThemes(): void
    {
        $page = $this->browser()
            ->visit('/demos/pagination/themes')
            ->assertSuccessful()
            ->assertSeeIn('h1', 'Pagination Themes')
            ->assertSee('Default')
            ->assertSee('Tailwind')
            ->assertSee('Application')
        ;

        self::assertSame(3, $page->crawler()->filter('.PaginationThemes_example')->count());
        self::assertSame(3, $page->crawler()->filter('.PaginationThemes_result nav')->count());
        self::assertStringEndsWith('/demos/pagination/themes', $page->crawler()->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testDemoIsLinkedFromPaginationListings(): void
    {
        foreach (['/pagination', '/demos/pagination'] as $url) {
            $page = $this->browser()->visit($url)->assertSuccessful();

            self::assertSame(1, $page->crawler()->filter('a[href="/demos/pagination/themes"]')->count());
        }
    }

    public function testDemoIsInTheSitemap(): void
    {
        $page = $this->browser()->visit('/sitemap.xml')->assertSuccessful();

        self::assertStringContainsString('/demos/pagination/themes</loc>', $page->content());
    }
}
