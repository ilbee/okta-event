# Password Migration Events

Fired when an Active Directory password migration campaign completes for a user.

**Namespace:** `Ilbee\Okta\Event\Event\PasswordMigration`

## Event hierarchy

- **Individual events** extend `OktaPasswordMigrationEvent` (group event)
- **Group event** `OktaPasswordMigrationEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaPasswordMigrationCompletedEvent` | `app.ad.password_migration_campaign.user.migrate.end` | AD password migration completed for a user |

## Usage example

```php
use Ilbee\Okta\Event\Event\PasswordMigration\OktaPasswordMigrationCompletedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnPasswordMigrated
{
    public function __invoke(OktaPasswordMigrationCompletedEvent $event): void
    {
        $userTarget = $event->oktaEvent->target[0] ?? null;

        $this->logger->info('Password migrated for user', [
            'user' => $userTarget?->alternateId,
        ]);
    }
}
```
