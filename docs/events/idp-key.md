# Identity Provider Key Events

Fired when IdP signing/encryption keys are created, updated, or deleted.

**Namespace:** `Ilbee\Okta\Event\Event\IdpKey`

## Event hierarchy

- **Individual events** extend `OktaIdpKeyEvent` (group event)
- **Group event** `OktaIdpKeyEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIdpKeyCreatedEvent` | `system.idp.key.create` | IdP key was created |
| `OktaIdpKeyUpdatedEvent` | `system.idp.key.update` | IdP key was updated |
| `OktaIdpKeyDeletedEvent` | `system.idp.key.delete` | IdP key was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\IdpKey\OktaIdpKeyEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TrackKeyRotation
{
    public function __invoke(OktaIdpKeyEvent $event): void
    {
        $this->auditLog->record(
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
