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

use App\Entity\Food;
use App\Model\LiveDemo;
use App\Service\LiveDemoRepository;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

use function Zenstruck\Foundry\Persistence\persist;

class LiveComponentDemosTest extends KernelTestCase
{
    use Factories;
    use HasBrowser;
    use ResetDatabase;

    #[Before]
    public function setupEntities(): void
    {
        persist(Food::class, ['name' => 'Pizza', 'votes' => 10]);
    }

    #[DataProvider('getSmokeTests')]
    public function testDemoPagesAllLoad(LiveDemo $liveDemo)
    {
        $router = self::bootKernel()->getContainer()->get('router');
        $url = $router->generate($liveDemo->getRoute());
        $this->browser()
            ->visit($url)
            ->assertSuccessful()
            ->assertSeeIn('title', $liveDemo->getName())
            ->assertSeeIn('h1', $liveDemo->getName())
        ;
    }

    public static function getSmokeTests(): \Generator
    {
        $demoRepository = new LiveDemoRepository();
        foreach ($demoRepository->findAll() as $demo) {
            yield $demo->getIdentifier() => [$demo];
        }
    }

    public function testCursorDemoKeepsItsSliceWhenANewReleaseIsPublished()
    {
        $firstPage = $this->browser()
            ->visit('/demos/live-component/cursor-pagination')
            ->assertSuccessful()
        ;

        $nextUrl = $firstPage->crawler()->filter('a[rel="next"]')->attr('href');
        self::assertNotNull($nextUrl);

        $secondPage = $this->browser()
            ->visit($nextUrl)
            ->assertSuccessful()
            ->assertSee('id 8')
        ;

        $itemsBefore = $secondPage->crawler()->filter('.CursorPagination_release strong')->each(static fn ($node): string => $node->text());
        $publishUrl = $secondPage->crawler()->filter('.CursorPagination_change a')->attr('href');
        self::assertNotNull($publishUrl);

        $changedPage = $this->browser()
            ->visit($publishUrl)
            ->assertSuccessful()
            ->assertSee('Release 3.12 was inserted at the top.')
            ->assertSee('id 8')
        ;

        self::assertSame(
            $itemsBefore,
            $changedPage->crawler()->filter('.CursorPagination_release strong')->each(static fn ($node): string => $node->text()),
        );
    }

    public function testPaginationStudioHydratesACompleteSetupFromTheUrl(): void
    {
        $page = $this->browser()
            ->visit('/demos/live-component/pagination-studio?type=cursor&theme=application&per_page=12&items=120&includeCss=0')
            ->assertSuccessful()
            ->assertSee('Cursor with application theme')
            ->assertSee('items_per_page: 12')
            ->assertSee("theme: 'pagination/application.html.twig'")
        ;

        $generatedConfig = $page->crawler()->filter('.PaginationStudio_output code')->text();

        self::assertStringNotContainsString('max_offset:', $generatedConfig);
        self::assertStringNotContainsString('navigation:', $generatedConfig);
        self::assertSame(0, $page->crawler()->filter('input[name="output"][value="assets"]')->count());
        self::assertSame(0, $page->crawler()->filter('input[name="output"][value="css"]')->count());
        self::assertSame(1, $page->crawler()->filter('input[name="output"][value="theme"]')->count());
    }
}
