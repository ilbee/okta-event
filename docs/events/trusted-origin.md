# Trusted Origin Events

Fired when trusted origins are created, updated, activated, deactivated, or deleted.

**Namespace:** `Ilbee\Okta\Event\Event\TrustedOrigin`

## Event hierarchy

- **Individual events** extend `OktaTrustedOriginEvent` (group event)
- **Group event** `OktaTrustedOriginEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaTrustedOriginCreatedEvent` | `security.trusted_origin.create` | Trusted origin was created |
| `OktaTrustedOriginUpdatedEvent` | `security.trusted_origin.update` | Trusted origin was updated |
| `OktaTrustedOriginActivatedEvent` | `security.trusted_origin.activate` | Trusted origin was activated |
| `OktaTrustedOriginDeactivatedEvent` | `security.trusted_origin.deactivate` | Trusted origin was deactivated |
| `OktaTrustedOriginDeletedEvent` | `security.trusted_origin.delete` | Trusted origin was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\TrustedOrigin\OktaTrustedOriginCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnTrustedOriginCreated
{
    public function __invoke(OktaTrustedOriginCreatedEvent $event): void
    {
        $target = $event->oktaEvent->target[0] ?? null;

        $this->securityAudit->log(
            action: 'trusted_origin_created',
            origin: $target?->displayName,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
