# Documentation

## Architecture

This bundle dispatches **three layers of Symfony events** for each incoming Okta webhook:

### 1. Typed Events (via `OktaEventMapper`)

For a subset of high-value events (user lifecycle, group membership, app assignment), the bundle extracts structured data from the Okta payload and dispatches a **typed event** extending `AbstractOktaEvent`. These events expose:

| Property | Type | Description |
|---|---|---|
| `userEmail` | `string` | Email of the affected user (from `target[type=User].alternateId`) |
| `eventType` | `string` | Okta event type string (e.g. `user.lifecycle.deactivate`) |
| `target` | `OktaTarget` | The user target object |
| `actor` | `?OktaActor` | Who performed the action (null if system-initiated) |

Some typed events have additional properties — see [Group Membership](events/group.md) and [Application User](events/user-app.md).

### 2. Individual Events (via `OktaEventRegistry`)

For **every known Okta event type**, the bundle dispatches a specific event class (e.g. `OktaAccessRequestCreatedEvent`). These extend `GenericOktaEvent` and expose the raw `$oktaEvent` DTO.

### 3. Group Events (via `OktaEventRegistry`)

Alongside each individual event, a **group event** is also dispatched (e.g. `OktaAccessRequestEvent` for all `access.request.*` events). This lets you listen to an entire category with a single listener.

### 4. Fallback: `GenericOktaEvent`

If an event type is not registered in any of the above, a `GenericOktaEvent` is dispatched.

### Dispatch order

For a single Okta event (e.g. `user.lifecycle.deactivate`), listeners fire in this order:

1. `OktaUserDeactivatedEvent` (typed, via mapper — only for mapped events)
2. `OktaUserLifecycleEvent` (legacy backward-compat — only for deactivate/suspend)
3. Individual event class from registry
4. Group event class from registry

## Event categories

| Category | Documentation |
|---|---|
| User Lifecycle | [events/user-lifecycle.md](events/user-lifecycle.md) |
| User Authentication | [events/user-auth.md](events/user-auth.md) |
| User Application | [events/user-app.md](events/user-app.md) |
| Group Membership | [events/group.md](events/group.md) |
| Group Lifecycle | [events/group-lifecycle.md](events/group-lifecycle.md) |
| Group Application | [events/group-app.md](events/group-app.md) |
| Application Lifecycle | [events/application-lifecycle.md](events/application-lifecycle.md) |
| Admin Privileges | [events/admin-privilege.md](events/admin-privilege.md) |
| Security | [events/security.md](events/security.md) |
| Security Authenticator | [events/security-authenticator.md](events/security-authenticator.md) |
| Policy | [events/policy.md](events/policy.md) |
| Device Identity | [events/device-identity.md](events/device-identity.md) |
| Device Trust | [events/device-trust.md](events/device-trust.md) |
| Access Request | [events/access-request.md](events/access-request.md) |
| Access Review | [events/access-review.md](events/access-review.md) |
| Certification | [events/certification.md](events/certification.md) |
| Entitlement Management | [events/entitlement-management.md](events/entitlement-management.md) |
| Entitlement Migration | [events/entitlement-migration.md](events/entitlement-migration.md) |
| Governance | [events/governance.md](events/governance.md) |
| IdP Lifecycle | [events/idp-lifecycle.md](events/idp-lifecycle.md) |
| IdP Key | [events/idp-key.md](events/idp-key.md) |
| IAM Role Subscription | [events/iam-role-subscription.md](events/iam-role-subscription.md) |
| Inline Hook | [events/inline-hook.md](events/inline-hook.md) |
| Log Stream | [events/log-stream.md](events/log-stream.md) |
| Rate Limit | [events/rate-limit.md](events/rate-limit.md) |
| Email Delivery | [events/email-delivery.md](events/email-delivery.md) |
| Trusted Origin | [events/trusted-origin.md](events/trusted-origin.md) |
| User Account Subscription | [events/user-account-subscription.md](events/user-account-subscription.md) |
| User Import | [events/user-import.md](events/user-import.md) |
| App User Management | [events/app-user-management.md](events/app-user-management.md) |
| Password Migration | [events/password-migration.md](events/password-migration.md) |

## DTOs

All events carry data through these DTOs:

### `OktaEvent`

The raw Okta event from the webhook payload.

| Property | Type | Description |
|---|---|---|
| `eventType` | `string` | Okta event type identifier |
| `target` | `OktaTarget[]` | Array of target objects |
| `actor` | `?OktaActor` | Who triggered the event |
| `uuid` | `?string` | Unique event ID (used for deduplication) |
| `published` | `?string` | ISO 8601 timestamp of when the event was published |

### `OktaTarget`

| Property | Type | Description |
|---|---|---|
| `id` | `string` | Okta object ID |
| `type` | `string` | Target type (`User`, `UserGroup`, `AppInstance`, etc.) |
| `alternateId` | `string` | Alternate identifier (email for users, name for groups) |
| `displayName` | `?string` | Human-readable display name |

### `OktaActor`

| Property | Type | Description |
|---|---|---|
| `id` | `string` | Okta actor ID |
| `type` | `string` | Actor type (e.g. `User`, `SystemPrincipal`) |
| `alternateId` | `string` | Alternate identifier |
| `displayName` | `?string` | Human-readable display name |

## Listening patterns

### Listen to a specific event

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnUserDeactivated
{
    public function __invoke(OktaUserDeactivatedEvent $event): void
    {
        // Typed event — has structured properties
        $email = $event->userEmail;
        $actor = $event->actor;
    }
}
```

### Listen to all events in a category

```php
use Ilbee\Okta\Event\Event\Security\OktaSecurityEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnAnySecurityEvent
{
    public function __invoke(OktaSecurityEvent $event): void
    {
        // Group event — has raw OktaEvent DTO
        $type = $event->oktaEvent->eventType;
        $targets = $event->oktaEvent->target;
    }
}
```

### Listen to all unknown/unhandled events

```php
use Ilbee\Okta\Event\Event\GenericOktaEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnUnknownEvent
{
    public function __invoke(GenericOktaEvent $event): void
    {
        $raw = $event->oktaEvent;
    }
}
```
