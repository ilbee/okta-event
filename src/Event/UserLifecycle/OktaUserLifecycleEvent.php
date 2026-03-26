<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Event\UserLifecycle;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when a user lifecycle event (e.g., deactivate or suspend) is received from Okta.
 *
 * @deprecated since 2.0, use OktaUserDeactivatedEvent or OktaUserSuspendedEvent instead.
 */
final class OktaUserLifecycleEvent extends Event
{
    public const DEACTIVATE = 'user.lifecycle.deactivate';
    public const SUSPEND = 'user.lifecycle.suspend';

    /**
     * @param string $userEmail The email of the user concerned by the event (from target.alternateId).
     * @param string $eventType The type of the Okta event (e.g., 'user.lifecycle.deactivate').
     */
    public function __construct(
        public readonly string $userEmail,
        public readonly string $eventType,
    ) {
        trigger_deprecation('ilbee/okta-event', '2.0', 'The "%s" class is deprecated, use "%s" or "%s" instead.', self::class, OktaUserDeactivatedEvent::class, OktaUserSuspendedEvent::class);
    }
}
