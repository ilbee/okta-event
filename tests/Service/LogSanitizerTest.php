<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use Ilbee\Okta\Event\Service\LogSanitizer;
use PHPUnit\Framework\TestCase;

class LogSanitizerTest extends TestCase
{
    public function testStripsNewlines(): void
    {
        self::assertSame('fakeline1fakeline2', LogSanitizer::sanitize("fakeline1\nfakeline2"));
    }

    public function testStripsCarriageReturns(): void
    {
        self::assertSame('fakeline1fakeline2', LogSanitizer::sanitize("fakeline1\r\nfakeline2"));
    }

    public function testStripsAnsiEscapeSequences(): void
    {
        self::assertSame('before[31mredafter', LogSanitizer::sanitize("before\e[31mredafter"));
    }

    public function testStripsNullBytes(): void
    {
        self::assertSame('ab', LogSanitizer::sanitize("a\x00b"));
    }

    public function testTruncatesLongValues(): void
    {
        $long = str_repeat('a', 300);
        $result = LogSanitizer::sanitize($long);

        self::assertSame(258, mb_strlen($result)); // 255 + '...'
        self::assertStringEndsWith('...', $result);
    }

    public function testCustomMaxLength(): void
    {
        $result = LogSanitizer::sanitize('abcdefghij', 5);

        self::assertSame('abcde...', $result);
    }

    public function testLeavesCleanStringUntouched(): void
    {
        self::assertSame('clean-value_123', LogSanitizer::sanitize('clean-value_123'));
    }
}
