<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\AccessReview;

use Ilbee\Okta\Event\Event\GenericOktaEvent;

/**
 * Dispatched for events in the ACCESS REVIEW EVENTS group.
 *
 * Consumers can inspect $oktaEvent->eventType to filter by specific event type within this group.
 */
class OktaAccessReviewEvent extends GenericOktaEvent
{
}
