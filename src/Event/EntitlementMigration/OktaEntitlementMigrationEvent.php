<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\EntitlementMigration;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the ENTITLEMENT MIGRATION EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaEntitlementMigrationEvent extends GenericOktaEvent
{
}
