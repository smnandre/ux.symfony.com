<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

/** Static ordered values used by the cursor pagination demo. */
final class UxReleaseFeed
{
    /**
     * @return list<array{id: int}>
     */
    public function all(bool $withDeletedItem = false): array
    {
        $items = array_map(
            static fn (int $id): array => ['id' => $id],
            range(20, 1),
        );

        if ($withDeletedItem) {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => 18 !== $item['id'],
            ));
        }

        return $items;
    }
}
