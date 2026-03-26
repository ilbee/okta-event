<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\DeviceIdentity;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the DEVICE IDENTITY EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaDeviceIdentityEvent extends GenericOktaEvent
{
}
