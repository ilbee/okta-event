<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

final class LogSanitizer
{
    private const MAX_FIELD_LENGTH = 255;

    public static function sanitize(string $value, int $maxLength = self::MAX_FIELD_LENGTH): string
    {
        // Strip control characters (newlines, carriage returns, ANSI escapes, etc.)
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? $value;

        if (mb_strlen($value) > $maxLength) {
            return mb_substr($value, 0, $maxLength) . '...';
        }

        return $value;
    }
}
