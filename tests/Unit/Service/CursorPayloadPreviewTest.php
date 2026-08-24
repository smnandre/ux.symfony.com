<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Service;

use App\Service\CursorPayloadPreview;
use PHPUnit\Framework\TestCase;

final class CursorPayloadPreviewTest extends TestCase
{
    public function testItReturnsAReadablePreviewOfACursor()
    {
        $token = $this->encode([
            'payload' => [
                'direction' => 'next',
                'values' => [42],
                'order' => '1234567890',
                'context' => 'abcdefghij',
            ],
            'signature' => 'signature-value',
        ]);

        self::assertSame([
            'direction' => 'next',
            'values' => [42],
            'order' => '12345678…',
            'context' => 'abcdefgh…',
            'signature' => 'signatur…',
        ], new CursorPayloadPreview()->decode($token));
    }

    public function testItIgnoresInvalidValues()
    {
        self::assertNull(new CursorPayloadPreview()->decode(null));
        self::assertNull(new CursorPayloadPreview()->decode('not-json'));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function encode(array $data): string
    {
        return rtrim(strtr(base64_encode(json_encode($data, \JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    }
}
