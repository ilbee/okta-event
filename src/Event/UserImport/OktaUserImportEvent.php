<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\UserImport;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the USER IMPORT EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaUserImportEvent extends GenericOktaEvent
{
}
