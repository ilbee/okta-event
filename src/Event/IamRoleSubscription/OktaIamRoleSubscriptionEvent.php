<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\IamRoleSubscription;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the IAM ROLE SUBSCRIPTIONS EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaIamRoleSubscriptionEvent extends GenericOktaEvent
{
}
