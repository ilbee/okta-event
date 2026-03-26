# Security Authenticator Events

Fired when authenticator configurations are created, updated, activated, or deactivated.

**Namespace:** `Ilbee\Okta\Event\Event\SecurityAuthenticator`

## Event hierarchy

- **Individual events** extend `OktaSecurityAuthenticatorEvent` (group event)
- **Group event** `OktaSecurityAuthenticatorEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaSecurityAuthenticatorCreatedEvent` | `security.authenticator.lifecycle.create` | Authenticator was created |
| `OktaSecurityAuthenticatorUpdatedEvent` | `security.authenticator.lifecycle.update` | Authenticator was updated |
| `OktaSecurityAuthenticatorActivatedEvent` | `security.authenticator.lifecycle.activate` | Authenticator was activated |
| `OktaSecurityAuthenticatorDeactivatedEvent` | `security.authenticator.lifecycle.deactivate` | Authenticator was deactivated |

## Usage example

```php
use Ilbee\Okta\Event\Event\SecurityAuthenticator\OktaSecurityAuthenticatorEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditAuthenticatorChanges
{
    public function __invoke(OktaSecurityAuthenticatorEvent $event): void
    {
        $this->auditLog->record(
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
