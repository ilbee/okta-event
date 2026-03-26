# Identity Provider Lifecycle Events

Fired when identity providers are created, updated, activated, deactivated, deleted, or when client secrets are read.

**Namespace:** `Ilbee\Okta\Event\Event\IdpLifecycle`

## Event hierarchy

- **Individual events** extend `OktaIdpLifecycleEvent` (group event)
- **Group event** `OktaIdpLifecycleEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIdpCreatedEvent` | `system.idp.lifecycle.create` | IdP was created |
| `OktaIdpUpdatedEvent` | `system.idp.lifecycle.update` | IdP was updated |
| `OktaIdpActivatedEvent` | `system.idp.lifecycle.activate` | IdP was activated |
| `OktaIdpDeactivatedEvent` | `system.idp.lifecycle.deactivate` | IdP was deactivated |
| `OktaIdpDeletedEvent` | `system.idp.lifecycle.delete` | IdP was deleted |
| `OktaIdpClientSecretReadEvent` | `system.idp.lifecycle.read_client_secret` | IdP client secret was read |

## Usage example

```php
use Ilbee\Okta\Event\Event\IdpLifecycle\OktaIdpClientSecretReadEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditSecretAccess
{
    public function __invoke(OktaIdpClientSecretReadEvent $event): void
    {
        $this->securityAudit->logSensitiveAccess(
            action: 'idp_client_secret_read',
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
