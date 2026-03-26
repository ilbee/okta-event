# App User Management Events

Fired for application-level user management and group import operations.

**Namespace:** `Ilbee\Okta\Event\Event\AppUserManagement`

## Event hierarchy

- **Individual events** extend `OktaAppUserManagementEvent` (group event)
- **Group event** `OktaAppUserManagementEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAppUserManagementImportEvent` | `app.user_management` | App user management event |
| `OktaAppUserGroupImportSuccessEvent` | `app.user_management.user_group_import.upsert_success` | User group import upsert succeeded |

## Usage example

```php
use Ilbee\Okta\Event\Event\AppUserManagement\OktaAppUserGroupImportSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnGroupImportSuccess
{
    public function __invoke(OktaAppUserGroupImportSuccessEvent $event): void
    {
        $this->syncService->markImportComplete(
            targets: $event->oktaEvent->target,
        );
    }
}
```
