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
 * Decodes cursor metadata for the demo panel. Validation remains the paginator's job.
 */
final class CursorPayloadPreview
{
    /**
     * @return array{direction: string, values: list<int|string|float>, order: string, context: string, signature: string}|null
     */
    public function decode(?string $cursor): ?array
    {
        if (null === $cursor) {
            return null;
        }

        $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);
        if (false === $decoded) {
            return null;
        }

        try {
            $data = json_decode($decoded, true, 8, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!\is_array($data) || !\is_array($data['payload'] ?? null)) {
            return null;
        }

        $payload = $data['payload'];
        $values = $payload['values'] ?? null;
        if (!\is_array($values)) {
            return null;
        }

        return [
            'direction' => \is_string($payload['direction'] ?? null) ? $payload['direction'] : '?',
            'values' => array_values(array_filter($values, static fn (mixed $value): bool => \is_int($value) || \is_string($value) || \is_float($value))),
            'order' => $this->shortHash($payload['order'] ?? null),
            'context' => $this->shortHash($payload['context'] ?? null),
            'signature' => $this->shortHash($data['signature'] ?? null),
        ];
    }

    private function shortHash(mixed $value): string
    {
        return \is_string($value) ? substr($value, 0, 8).'…' : '?';
    }
}
