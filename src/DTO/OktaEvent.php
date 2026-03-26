<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DTO;

/**
 * Represents a single event from the "events" array in the Okta payload.
 */
final readonly class OktaEvent
{
    /**
     * @param OktaTarget[] $target
     */
    public function __construct(
        public string $eventType,
        /** @var OktaTarget[] */
        public array $target = [],
        public ?OktaActor $actor = null,
        public ?string $uuid = null,
        public ?string $published = null,
    ) {
    }
}
