<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event;

use Ilbee\Okta\Event\DTO\OktaEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched for any Okta event type that does not have a dedicated typed event class.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by event type.
 */
class GenericOktaEvent extends Event
{
    public function __construct(
        public readonly OktaEvent $oktaEvent,
    ) {
    }
}
