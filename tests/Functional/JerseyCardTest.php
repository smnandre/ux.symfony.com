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

use App\Model\Jersey;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class JerseyCardTest extends KernelTestCase
{
    public function testItRendersAThreeLayerJersey()
    {
        self::bootKernel();

        $jersey = new Jersey(1, '000-H-S', 'pink', 'halves', 'shade', 'oklch(65% 0.2 0)', 'ux');
        $html = self::getContainer()->get(Environment::class)->createTemplate(
            '<twig:JerseyCard :jersey="jersey" />',
        )->render(['jersey' => $jersey]);

        self::assertStringContainsString('class="JerseyCard"', $html);
        self::assertStringContainsString('000-H-S', $html);
        self::assertSame(3, substr_count($html, '<use'));
        self::assertStringContainsString('#jersey-base', $html);
        self::assertStringContainsString('#jersey-motif-halves', $html);
        self::assertStringContainsString('class="logo logo-shade logo-halves"', $html);
        self::assertStringContainsString('#jersey-logo-ux', $html);
        self::assertStringNotContainsString('images/jerseys.svg', $html);
        self::assertStringNotContainsString('data-live-id', $html);
        self::assertStringNotContainsString('class="number"', $html);
    }
}
