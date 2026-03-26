# Entitlement Migration Events

Fired when entitlement migrations start, complete, or fail.

**Namespace:** `Ilbee\Okta\Event\Event\EntitlementMigration`

## Event hierarchy

- **Individual events** extend `OktaEntitlementMigrationEvent` (group event)
- **Group event** `OktaEntitlementMigrationEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaEntitlementMigrationStartedEvent` | `entitlement.migration.start` | Migration started |
| `OktaEntitlementMigrationCompletedEvent` | `entitlement.migration.complete` | Migration completed successfully |
| `OktaEntitlementMigrationFailedEvent` | `entitlement.migration.fail` | Migration failed |

## Usage example

```php
use Ilbee\Okta\Event\Event\EntitlementMigration\OktaEntitlementMigrationFailedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnMigrationFailed
{
    public function __invoke(OktaEntitlementMigrationFailedEvent $event): void
    {
        $this->alertService->critical('Entitlement migration failed', [
            'actor' => $event->oktaEvent->actor?->alternateId,
        ]);
    }
}
```
