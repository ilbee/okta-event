<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\UserAuth;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the USER AUTH EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaUserAuthEvent extends GenericOktaEvent
{
}
