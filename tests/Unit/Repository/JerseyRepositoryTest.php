<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Repository;

use App\Model\Jersey;
use App\Repository\JerseyRepository;
use PHPUnit\Framework\TestCase;

final class JerseyRepositoryTest extends TestCase
{
    public function testItGeneratesEveryCombinationWithAStableId()
    {
        $jerseys = new JerseyRepository()->find();
        $combinations = array_map(
            static fn (Jersey $jersey): string => $jersey->color.'/'.$jersey->pattern.'/'.$jersey->secondary,
            $jerseys,
        );
        $ids = array_column($jerseys, 'id');
        $identifiers = array_column($jerseys, 'identifier');
        sort($ids);

        self::assertCount(120, $jerseys);
        self::assertSame(range(1, 120), $ids);
        self::assertCount(120, array_unique($combinations));
        self::assertCount(120, array_unique($identifiers));
        self::assertSame('000-P-W', $jerseys[0]->identifier);
        self::assertSame('060-P-W', $jerseys[20]->identifier);
    }

    public function testItDistributesEveryDimensionEvenly()
    {
        $jerseys = new JerseyRepository()->find();

        self::assertSame([20], array_values(array_unique(array_count_values(array_column($jerseys, 'color')))));
        self::assertSame([24], array_values(array_unique(array_count_values(array_column($jerseys, 'pattern')))));
        self::assertSame([30], array_values(array_unique(array_count_values(array_column($jerseys, 'secondary')))));
    }

    public function testItTemporarilyOrdersByColorSecondaryAndPattern()
    {
        $jerseys = new JerseyRepository()->find();

        self::assertSame(
            [
                'pink/white/plain',
                'pink/white/halves',
                'pink/white/stripe',
                'pink/white/hoops',
                'pink/white/sash',
                'pink/black/plain',
            ],
            array_map(
                static fn (Jersey $jersey): string => $jersey->color.'/'.$jersey->secondary.'/'.$jersey->pattern,
                \array_slice($jerseys, 0, 6),
            ),
        );
        self::assertSame('orange/white/plain', $jerseys[20]->color.'/'.$jerseys[20]->secondary.'/'.$jerseys[20]->pattern);
    }

    public function testTheSecondaryChoosesTheLogoTreatment()
    {
        $repository = new JerseyRepository();
        $jerseys = $repository->find();

        foreach (['white', 'black'] as $secondary) {
            self::assertSame(
                ['sfux'],
                $this->uniqueSortedLogos(array_values(array_filter($jerseys, static fn (Jersey $jersey): bool => $secondary === $jersey->secondary))),
            );
        }

        foreach (['shade', 'tint'] as $secondary) {
            self::assertSame(
                ['ux'],
                $this->uniqueSortedLogos(array_values(array_filter($jerseys, static fn (Jersey $jersey): bool => $secondary === $jersey->secondary))),
            );
        }
    }

    public function testItFiltersByDisplayedColorAndPattern()
    {
        $repository = new JerseyRepository();
        $pink = $repository->find(color: 'pink');
        $halves = $repository->find(pattern: 'halves');
        $combination = $repository->find(color: 'pink', pattern: 'halves');
        $secondaries = array_values(array_unique(array_column($combination, 'secondary')));
        sort($secondaries);

        self::assertCount(20, $pink);
        self::assertCount(24, $halves);
        self::assertCount(4, $combination);
        self::assertSame(['black', 'shade', 'tint', 'white'], $secondaries);
        self::assertSame([], $repository->find(color: 'missing'));
        self::assertSame([], $repository->find(pattern: 'missing'));
    }

    /**
     * @param list<Jersey> $jerseys
     *
     * @return list<string>
     */
    private function uniqueSortedLogos(array $jerseys): array
    {
        $logos = array_values(array_unique(array_column($jerseys, 'logo')));
        sort($logos);

        return $logos;
    }
}
