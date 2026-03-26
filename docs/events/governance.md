# Governance Events

Fired when Okta Governance settings are updated.

**Namespace:** `Ilbee\Okta\Event\Event\Governance`

## Event hierarchy

- **Individual events** extend `OktaGovernanceEvent` (group event)
- **Group event** `OktaGovernanceEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaGovernanceSettingsUpdatedEvent` | `governance.settings.update` | Governance settings were updated |
| `OktaGovernancePrincipalSettingsUpdatedEvent` | `governance.principal.settings.update` | Principal governance settings were updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\Governance\OktaGovernanceEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditGovernanceChanges
{
    public function __invoke(OktaGovernanceEvent $event): void
    {
        $this->auditLog->record(
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
