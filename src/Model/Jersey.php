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

final readonly class Jersey
{
    public function __construct(
        public int $id,
        public string $identifier,
        public string $color,
        public string $pattern,
        public string $secondary,
        public string $primaryColor,
        public string $logo,
    ) {
    }
}
