<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Model;

use App\Model\UxPackage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UxPackage::class)]
final class UxPackageTest extends TestCase
{
    public function testMetaTitlesUsePackageDefaults(): void
    {
        $package = new UxPackage(
            'foo-bar',
            'FooBar',
            'app_foo_bar',
            '#000000',
            'Lorem Ipsum',
            'Lorem ipsum dolor sit amet.',
        );

        self::assertSame('FooBar - Lorem Ipsum', $package->getSeoTitle());
        self::assertSame('Lorem Ipsum - Symfony UX FooBar', $package->getSocialTitle());
    }

    public function testMetaTitlesCanBeOverridden(): void
    {
        $package = new UxPackage(
            'foo-bar',
            'FooBar',
            'app_foo_bar',
            '#000000',
            'Lorem Ipsum',
            'Lorem ipsum dolor sit amet.',
            seoTitle: 'Lorem Ipsum - FooBar',
            socialTitle: 'FooBar - Lorem Ipsum',
        );

        self::assertSame('Lorem Ipsum - FooBar', $package->getSeoTitle());
        self::assertSame('FooBar - Lorem Ipsum', $package->getSocialTitle());
    }
}
