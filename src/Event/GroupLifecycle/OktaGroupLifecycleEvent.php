<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\GroupLifecycle;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the GROUP LIFECYCLE EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaGroupLifecycleEvent extends GenericOktaEvent
{
}
