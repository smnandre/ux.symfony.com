<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Twig\Components;

use App\Twig\Components\Code\CodeBlock;
use PHPUnit\Framework\TestCase;

final class CodeBlockTest extends TestCase
{
    public function testAnExtractedLineRangeRemovesItsCommonIndentation()
    {
        $directory = sys_get_temp_dir().'/ux-code-block-test-'.bin2hex(random_bytes(6));
        mkdir($directory);
        file_put_contents($directory.'/example.php', <<<'PHP'
            <?php

            final class Example
            {
                public function example(): void
                {
                    $value = true;
                }
            }
            PHP);

        try {
            $component = new CodeBlock($directory);
            $component->mount('example.php');
            $component->lineStart = 5;
            $component->lineEnd = 8;

            self::assertSame(<<<'PHP'
                public function example(): void
                {
                    $value = true;
                }
                PHP, $component->getRawSource());
        } finally {
            unlink($directory.'/example.php');
            rmdir($directory);
        }
    }
}
