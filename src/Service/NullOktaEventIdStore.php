<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * No-op implementation that does not perform any deduplication.
 *
 * Logs a warning on the first call to add() to alert operators that
 * replay protection is disabled. Use CacheOktaEventIdStore in production.
 */
final class NullOktaEventIdStore implements OktaEventIdStoreInterface
{
    private bool $warned = false;

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function has(string $eventId): bool
    {
        return false;
    }

    public function add(string $eventId): void
    {
        if (!$this->warned) {
            $this->warned = true;
            $this->logger->warning('OktaEventIdStore: replay protection is disabled. Configure a cache-backed store for production use.');
        }
    }
}
