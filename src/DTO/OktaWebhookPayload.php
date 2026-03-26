<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\DTO;

/**
 * Represents the root object of an incoming Okta webhook payload.
 */
final readonly class OktaWebhookPayload
{
    public function __construct(
        public OktaEventData $data,
    ) {
    }
}
