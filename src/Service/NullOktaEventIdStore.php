<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

/**
 * No-op implementation that does not perform any deduplication.
 *
 * Used as the default when no event ID store is configured.
 * Replace with a cache-backed implementation for replay protection.
 */
final readonly class NullOktaEventIdStore implements OktaEventIdStoreInterface
{
    public function has(string $eventId): bool
    {
        return false;
    }

    public function add(string $eventId): void
    {
    }
}
