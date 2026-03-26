<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\UserAccountSubscription;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the USER ACCOUNT SUBSCRIPTIONS EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaUserAccountSubscriptionEvent extends GenericOktaEvent
{
}
