<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

final class Jersey
{
    public function __construct(
        public readonly int $id,
        public readonly string $identifier,
        public readonly string $color,
        public readonly string $pattern,
        public readonly string $secondary,
        public readonly string $primaryColor,
        public readonly string $logo,
    ) {
    }
}
