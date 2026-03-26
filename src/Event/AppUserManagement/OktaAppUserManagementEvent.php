<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\AppUserManagement;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the APP USER MANAGEMENT EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaAppUserManagementEvent extends GenericOktaEvent
{
}
