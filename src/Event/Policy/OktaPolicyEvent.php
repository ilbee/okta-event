<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\Policy;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the POLICY EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaPolicyEvent extends GenericOktaEvent
{
}
