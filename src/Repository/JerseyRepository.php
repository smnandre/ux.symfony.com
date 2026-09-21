<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Model\Jersey;

final class JerseyRepository
{
    private const COLORS = [
        'pink' => 0,
        'orange' => 60,
        'lime' => 120,
        'turquoise' => 180,
        'blue' => 240,
        'purple' => 300,
    ];
    private const PATTERNS = [
        'plain' => 'Plain',
        'halves' => 'Halves',
        'stripe' => 'Stripe',
        'hoops' => 'Hoops',
        'sash' => 'Sash',
    ];
    private const PATTERN_CODES = [
        'plain' => 'P',
        'halves' => 'H',
        'stripe' => 'V',
        'hoops' => 'O',
        'sash' => 'D',
    ];
    private const SECONDARIES = ['white', 'black', 'shade', 'tint'];
    private const SECONDARY_CODES = [
        'white' => 'W',
        'black' => 'B',
        'shade' => 'S',
        'tint' => 'T',
    ];

    /**
     * @var list<Jersey>
     */
    private array $jerseys;

    public function __construct()
    {
        $this->jerseys = [];
        $colorNames = array_keys(self::COLORS);
        $patternNames = array_keys(self::PATTERNS);
        $total = \count(self::COLORS) * \count(self::PATTERNS) * \count(self::SECONDARIES);

        for ($id = 1; $id <= $total; ++$id) {
            $combination = $id - 1;
            $pattern = $patternNames[$combination % \count(self::PATTERNS)];
            $secondaryIndex = intdiv($combination, \count(self::PATTERNS)) % \count(self::SECONDARIES);
            $colorIndex = intdiv($combination, \count(self::PATTERNS) * \count(self::SECONDARIES));
            $color = $colorNames[$colorIndex];
            $secondary = self::SECONDARIES[$secondaryIndex];
            $logo = match ($secondary) {
                'shade', 'tint' => 'ux',
                default => 'sfux',
            };

            $this->jerseys[] = new Jersey(
                id: $id,
                identifier: \sprintf('%03d-%s-%s', self::COLORS[$color], self::PATTERN_CODES[$pattern], self::SECONDARY_CODES[$secondary]),
                color: $color,
                pattern: $pattern,
                secondary: $secondary,
                primaryColor: self::color(self::COLORS[$color]),
                logo: $logo,
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function getColors(): array
    {
        return array_map(self::color(...), self::COLORS);
    }

    /**
     * @return array<string, string>
     */
    public function getPatterns(): array
    {
        return self::PATTERNS;
    }

    /**
     * @return list<Jersey>
     */
    public function find(string $color = '', string $pattern = ''): array
    {
        return array_values(array_filter(
            $this->jerseys,
            static fn (Jersey $jersey): bool => ('' === $color || $jersey->color === $color)
                && ('' === $pattern || $jersey->pattern === $pattern),
        ));
    }

    private static function color(int $hue): string
    {
        return \sprintf('oklch(65%% 0.2 %d)', ($hue + 360) % 360);
    }
}
