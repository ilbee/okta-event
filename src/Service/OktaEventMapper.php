<?php

declare(strict_types=1);

namespace Ilbee\Okta\Event\Service;

use Psr\Log\LoggerInterface;
use Ilbee\Okta\Event\DTO\OktaEvent;
use Ilbee\Okta\Event\DTO\OktaTarget;
use Ilbee\Okta\Event\Event\AbstractOktaEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberAddedEvent;
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberRemovedEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserAssignedEvent;
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserUnassignedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserActivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserCreatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeletedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserPasswordResetEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserProfileUpdatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserReactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserSuspendedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserUnsuspendedEvent;

final readonly class OktaEventMapper
{
    /**
     * Maps Okta event types to their typed event classes.
     * Events not listed here will be dispatched as GenericOktaEvent by the controller.
     *
     * @var array<string, class-string<AbstractOktaEvent>>
     */
    private const TYPED_USER_EVENTS = [
        'user.lifecycle.activate' => OktaUserActivatedEvent::class,
        'user.lifecycle.create' => OktaUserCreatedEvent::class,
        'user.lifecycle.deactivate' => OktaUserDeactivatedEvent::class,
        'user.lifecycle.delete.initiated' => OktaUserDeletedEvent::class,
        'user.lifecycle.reactivate' => OktaUserReactivatedEvent::class,
        'user.lifecycle.suspend' => OktaUserSuspendedEvent::class,
        'user.lifecycle.unsuspend' => OktaUserUnsuspendedEvent::class,
        'user.lifecycle.password_reset' => OktaUserPasswordResetEvent::class,
        'user.account.update_profile' => OktaUserProfileUpdatedEvent::class,
    ];

    private const TYPED_GROUP_EVENTS = [
        'group.user_membership.add' => OktaGroupMemberAddedEvent::class,
        'group.user_membership.remove' => OktaGroupMemberRemovedEvent::class,
    ];

    private const TYPED_APP_EVENTS = [
        'application.user_membership.add' => OktaAppUserAssignedEvent::class,
        'application.user_membership.remove' => OktaAppUserUnassignedEvent::class,
    ];

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Returns true if the event type has a dedicated typed event class.
     */
    public function supports(string $eventType): bool
    {
        return isset(self::TYPED_USER_EVENTS[$eventType])
            || isset(self::TYPED_GROUP_EVENTS[$eventType])
            || isset(self::TYPED_APP_EVENTS[$eventType]);
    }

    public function map(OktaEvent $oktaEvent): ?AbstractOktaEvent
    {
        if (!$this->supports($oktaEvent->eventType)) {
            return null;
        }

        $userTarget = $this->findTargetByType($oktaEvent, 'User');

        if (null === $userTarget) {
            $this->logger->warning('No User target found in event.', [
                'eventType' => $oktaEvent->eventType,
            ]);

            return null;
        }

        if (!filter_var($userTarget->alternateId, \FILTER_VALIDATE_EMAIL)) {
            $this->logger->warning('Skipping target with non-email alternateId.', [
                'alternateId' => mb_substr($userTarget->alternateId, 0, 255),
                'eventType' => mb_substr($oktaEvent->eventType, 0, 255),
            ]);

            return null;
        }

        $userEmail = $userTarget->alternateId;
        $actor = $oktaEvent->actor;

        if (isset(self::TYPED_USER_EVENTS[$oktaEvent->eventType])) {
            $eventClass = self::TYPED_USER_EVENTS[$oktaEvent->eventType];

            return new $eventClass($userEmail, $oktaEvent->eventType, $userTarget, $actor);
        }

        if (isset(self::TYPED_GROUP_EVENTS[$oktaEvent->eventType])) {
            return $this->buildGroupEvent(self::TYPED_GROUP_EVENTS[$oktaEvent->eventType], $oktaEvent, $userEmail, $userTarget);
        }

        if (isset(self::TYPED_APP_EVENTS[$oktaEvent->eventType])) {
            return $this->buildAppEvent(self::TYPED_APP_EVENTS[$oktaEvent->eventType], $oktaEvent, $userEmail, $userTarget);
        }

        return null;
    }

    /**
     * @param class-string<OktaGroupMemberAddedEvent|OktaGroupMemberRemovedEvent> $eventClass
     */
    private function buildGroupEvent(string $eventClass, OktaEvent $oktaEvent, string $userEmail, OktaTarget $userTarget): OktaGroupMemberAddedEvent|OktaGroupMemberRemovedEvent|null
    {
        $groupTarget = $this->findTargetByType($oktaEvent, 'UserGroup');

        if (null === $groupTarget) {
            $this->logger->warning('No UserGroup target found in group membership event.', [
                'eventType' => $oktaEvent->eventType,
            ]);

            return null;
        }

        return new $eventClass($userEmail, $oktaEvent->eventType, $userTarget, $oktaEvent->actor, $groupTarget->id, $groupTarget->displayName);
    }

    /**
     * @param class-string<OktaAppUserAssignedEvent|OktaAppUserUnassignedEvent> $eventClass
     */
    private function buildAppEvent(string $eventClass, OktaEvent $oktaEvent, string $userEmail, OktaTarget $userTarget): OktaAppUserAssignedEvent|OktaAppUserUnassignedEvent|null
    {
        $appTarget = $this->findTargetByType($oktaEvent, 'AppInstance');

        if (null === $appTarget) {
            $this->logger->warning('No AppInstance target found in app assignment event.', [
                'eventType' => $oktaEvent->eventType,
            ]);

            return null;
        }

        return new $eventClass($userEmail, $oktaEvent->eventType, $userTarget, $oktaEvent->actor, $appTarget->id, $appTarget->displayName);
    }

    private function findTargetByType(OktaEvent $oktaEvent, string $type): ?OktaTarget
    {
        foreach ($oktaEvent->target as $target) {
            if ($type === $target->type) {
                return $target;
            }
        }

        return null;
    }
}
