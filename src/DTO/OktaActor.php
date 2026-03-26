<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DTO;

/**
 * Represents the "actor" object within an Okta event.
 * Contains information about who performed the action.
 */
final readonly class OktaActor
{
    public function __construct(
        public string $id,
        public string $type,
        public string $alternateId,
        public ?string $displayName,
    ) {
    }
}
