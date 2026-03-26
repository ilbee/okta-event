# User Lifecycle Events

Fired when a user's lifecycle state changes in Okta (creation, activation, deactivation, suspension, deletion, etc.).

**Namespace:** `Ilbee\Okta\Event\Event\UserLifecycle`

## Event hierarchy

These events are dispatched at **two levels**:

### Typed events (via `OktaEventMapper`)

These extend `AbstractOktaEvent` and provide structured properties. They are dispatched for the event types listed below.

| Property | Type | Description |
|---|---|---|
| `userEmail` | `string` | Email of the affected user |
| `eventType` | `string` | Okta event type string |
| `target` | `OktaTarget` | User target object (`id`, `type`, `alternateId`, `displayName`) |
| `actor` | `?OktaActor` | Who performed the action |

### Group event (via `OktaEventRegistry`)

`OktaUserLifecycleGroupEvent` extends `GenericOktaEvent` and is dispatched for **every** user lifecycle event. Use this to handle all user lifecycle events with a single listener.

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserCreatedEvent` | `user.lifecycle.create` | A new user account was created |
| `OktaUserActivatedEvent` | `user.lifecycle.activate` | A user account was activated |
| `OktaUserReactivatedEvent` | `user.lifecycle.reactivate` | A previously deactivated user was reactivated |
| `OktaUserDeactivatedEvent` | `user.lifecycle.deactivate` | A user account was deactivated |
| `OktaUserSuspendedEvent` | `user.lifecycle.suspend` | A user account was suspended |
| `OktaUserUnsuspendedEvent` | `user.lifecycle.unsuspend` | A suspended user was unsuspended |
| `OktaUserDeletedEvent` | `user.lifecycle.delete.initiated` | User deletion was initiated |
| `OktaUserPasswordResetEvent` | `user.lifecycle.password_reset` | A user's password was reset via lifecycle |
| `OktaUserProfileUpdatedEvent` | `user.account.update_profile` | A user's profile was updated |

## Okta subscription

In your Okta Event Hook, subscribe to:

- `user.lifecycle.create`
- `user.lifecycle.activate`
- `user.lifecycle.reactivate`
- `user.lifecycle.deactivate`
- `user.lifecycle.suspend`
- `user.lifecycle.unsuspend`
- `user.lifecycle.delete.initiated`
- `user.lifecycle.password_reset`
- `user.account.update_profile`

## Usage examples

### React to a specific lifecycle event

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class DisableUserOnDeactivation
{
    public function __invoke(OktaUserDeactivatedEvent $event): void
    {
        $email = $event->userEmail;
        $userId = $event->target->id;

        // Disable the user in your application
        $this->userRepository->disableByEmail($email);
    }
}
```

### React to any user lifecycle event

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleGroupEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditUserLifecycleChanges
{
    public function __invoke(OktaUserLifecycleGroupEvent $event): void
    {
        $eventType = $event->oktaEvent->eventType;
        $targets = $event->oktaEvent->target;
        $actor = $event->oktaEvent->actor;

        // Log all user lifecycle changes for audit
        $this->auditLogger->log('okta.user_lifecycle', [
            'event_type' => $eventType,
            'actor' => $actor?->alternateId,
            'targets' => array_map(fn ($t) => $t->alternateId, $targets),
        ]);
    }
}
```

### React to user creation with full details

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ProvisionNewUser
{
    public function __invoke(OktaUserCreatedEvent $event): void
    {
        $email = $event->userEmail;
        $displayName = $event->target->displayName;
        $oktaId = $event->target->id;

        // Create user in your local database
        $this->userService->provision($email, $displayName, $oktaId);
    }
}
```

## Deprecated: `OktaUserLifecycleEvent`

The `OktaUserLifecycleEvent` class is **deprecated**. It is still dispatched for backward compatibility when `user.lifecycle.deactivate` or `user.lifecycle.suspend` events are received, but you should migrate to the specific event classes above.

```php
// DEPRECATED — do not use in new code
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleEvent;

// INSTEAD, use:
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserSuspendedEvent;
```
