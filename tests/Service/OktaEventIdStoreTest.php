<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Tests\Service;

use PHPUnit\Framework\TestCase;
use Ilbee\Okta\Event\Service\CacheOktaEventIdStore;
use Ilbee\Okta\Event\Service\NullOktaEventIdStore;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class OktaEventIdStoreTest extends TestCase
{
    public function testNullStoreNeverReportsDuplicate(): void
    {
        $store = new NullOktaEventIdStore();

        $store->add('event-1');
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
}
