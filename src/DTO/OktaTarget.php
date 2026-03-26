<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DTO;

/**
 * Represents the "target" object within an Okta event.
 * Contains information about the user the event applies to.
 */
final readonly class OktaTarget
{
    public function __construct(
        public string $alternateId,
        public string $id,
        public string $type,
        public ?string $displayName,
    ) {
    }
}
