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

/**
 * Static release feed used by the UX Pagination demos.
 */
final class UxReleaseFeed
{
    /**
     * @return list<array{id: int, name: string, summary: string}>
     */
    public function all(): array
    {
        return [
            ['id' => 11, 'name' => 'Pagination', 'summary' => 'Cursor and numbered pagination'],
            ['id' => 10, 'name' => 'Calendar Link', 'summary' => 'Add to calendar links'],
            ['id' => 9, 'name' => 'Native', 'summary' => 'Hotwire Native bridge'],
            ['id' => 8, 'name' => 'Toolkit', 'summary' => 'Copy-paste UI kits'],
            ['id' => 7, 'name' => 'Map', 'summary' => 'Interactive maps'],
            ['id' => 6, 'name' => 'Icons', 'summary' => 'SVG icons from any set'],
            ['id' => 5, 'name' => 'Translator', 'summary' => 'Translations in JavaScript'],
            ['id' => 4, 'name' => 'Autocomplete', 'summary' => 'Ajax-powered select fields'],
            ['id' => 3, 'name' => 'Live Component', 'summary' => 'Reactive server-rendered UI'],
            ['id' => 2, 'name' => 'Turbo', 'summary' => 'Single-page experience'],
            ['id' => 1, 'name' => 'Stimulus', 'summary' => 'Modest JavaScript framework'],
        ];
    }
}
