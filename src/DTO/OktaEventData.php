<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DTO;

/**
 * Represents the "data" object within the main Okta webhook payload.
 */
final readonly class OktaEventData
{
    /**
     * @param OktaEvent[] $events
     */
    public function __construct(
        /** @var OktaEvent[] */
        public array $events,
    ) {
    }
}
