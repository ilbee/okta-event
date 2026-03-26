<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Cache-backed implementation for event ID deduplication.
 *
 * Stores event IDs in a PSR-6 cache pool with a configurable TTL.
 */
final readonly class CacheOktaEventIdStore implements OktaEventIdStoreInterface
{
    private const DEFAULT_TTL = 86400;

    public function __construct(
        private CacheItemPoolInterface $cache,
        private int $ttl = self::DEFAULT_TTL,
    ) {
    }

    public function has(string $eventId): bool
    {
        return $this->cache->hasItem($this->key($eventId));
    }

    public function add(string $eventId): void
    {
        $item = $this->cache->getItem($this->key($eventId));
        $item->set(true);
        $item->expiresAfter($this->ttl);
        $this->cache->save($item);
    }

    private function key(string $eventId): string
    {
        return \sprintf('okta_event_id_%s', md5($eventId));
    }
}
