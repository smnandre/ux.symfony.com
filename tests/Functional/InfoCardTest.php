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
use Twig\Environment;

final class InfoCardTest extends KernelTestCase
{
    public function testItRendersContentMediaAndLegend()
    {
        self::bootKernel();

        $html = self::getContainer()->get(Environment::class)->createTemplate(<<<'TWIG'
            <twig:InfoCard id="pagination-url" title="The URL changed">
                <p>The page number is reflected in the URL.</p>

                <twig:block name="media">
                    <pre><code>/pagination/2</code></pre>
                </twig:block>

                <twig:block name="legend">
                    Refreshing restores the same page.
                </twig:block>
            </twig:InfoCard>
            TWIG)->render();

        self::assertStringContainsString('id="pagination-url"', $html);
        self::assertStringContainsString('popover="auto"', $html);
        self::assertStringContainsString('aria-labelledby="pagination-url-title"', $html);
        self::assertStringContainsString('<h2 id="pagination-url-title" class="InfoCard_title">The URL changed</h2>', $html);
        self::assertStringContainsString('popovertarget="pagination-url"', $html);
        self::assertStringContainsString('<p>The page number is reflected in the URL.</p>', $html);
        self::assertStringContainsString('<pre><code>/pagination/2</code></pre>', $html);
        self::assertStringContainsString('Refreshing restores the same page.', $html);
    }

    public function testItRendersANumberedPinConnectedToATarget()
    {
        self::bootKernel();

        $html = self::getContainer()->get(Environment::class)->createTemplate(<<<'TWIG'
            <twig:InfoCard
                id="pagination-filters"
                title="Filters reset the page"
                number="2"
                target=".filters"
            >
                <p>Changing a filter returns to page 1.</p>
            </twig:InfoCard>
            TWIG)->render();

        self::assertStringContainsString('class="InfoCard InfoCard--pinned"', $html);
        self::assertStringContainsString('data-controller="info-card"', $html);
        self::assertStringContainsString('data-info-card-target-selector-value=".filters"', $html);
        self::assertStringContainsString('aria-label="Open note 2: Filters reset the page"', $html);
        self::assertStringContainsString('<span>2</span>', $html);
        self::assertStringContainsString('class="InfoCard_connector"', $html);
    }
}
