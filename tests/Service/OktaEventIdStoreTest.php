<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use Ilbee\Okta\Event\Service\CacheOktaEventIdStore;
use Ilbee\Okta\Event\Service\NullOktaEventIdStore;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class OktaEventIdStoreTest extends TestCase
{
    public function testNullStoreNeverReportsDuplicate(): void
    {
        $store = new NullOktaEventIdStore();

        $store->add('event-1');
        self::assertFalse($store->has('event-1'));
    }

    public function testNullStoreLogsWarningOnFirstAdd(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with(self::stringContains('replay protection is disabled'));

        $store = new NullOktaEventIdStore($logger);

        $store->add('event-1');
        $store->add('event-2'); // second call should NOT trigger another warning
    }

    public function testNullStoreDoesNotWarnWithoutAdd(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $store = new NullOktaEventIdStore($logger);

        self::assertFalse($store->has('event-1'));
    }

    public function testCacheStoreDetectsDuplicate(): void
    {
        $cache = new ArrayAdapter();
        $store = new CacheOktaEventIdStore($cache);

        self::assertFalse($store->has('event-1'));
        $store->add('event-1');
        self::assertTrue($store->has('event-1'));
    }

    public function testCacheStoreDoesNotFalsePositive(): void
    {
        $cache = new ArrayAdapter();
        $store = new CacheOktaEventIdStore($cache);

        $store->add('event-1');
        self::assertFalse($store->has('event-2'));
    }

    public function testCacheStoreRejectsZeroTtl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TTL must be a positive integer');

        new CacheOktaEventIdStore(new ArrayAdapter(), 0);
    }

    public function testCacheStoreRejectsNegativeTtl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TTL must be a positive integer');

        new CacheOktaEventIdStore(new ArrayAdapter(), -1);
    }
}
