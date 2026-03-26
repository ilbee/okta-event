<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

/**
 * Stores processed event IDs to prevent replay attacks.
 *
 * Implement this interface with a persistent backend (Redis, database)
 * for production use. The bundle provides a no-op implementation by default.
 */
interface OktaEventIdStoreInterface
{
    /**
     * Returns true if this event ID has already been processed.
     */
    public function has(string $eventId): bool;

    /**
     * Marks this event ID as processed.
     */
    public function add(string $eventId): void;
}
