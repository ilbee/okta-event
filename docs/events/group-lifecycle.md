# Group Lifecycle Events

Fired when groups are created or deleted in Okta, including imported groups.

**Namespace:** `Ilbee\Okta\Event\Event\GroupLifecycle`

## Event hierarchy

- **Individual events** extend `OktaGroupLifecycleEvent` (group event)
- **Group event** `OktaGroupLifecycleEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaGroupCreatedEvent` | `group.lifecycle.create` | A group was created |
| `OktaGroupDeletedEvent` | `group.lifecycle.delete` | A group was deleted |
| `OktaImportGroupCreatedEvent` | `system.import.group.create` | A group was created via import |
| `OktaImportGroupDeletedEvent` | `system.import.group.delete` | An imported group was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\GroupLifecycle\OktaGroupCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnGroupCreated
{
    public function __invoke(OktaGroupCreatedEvent $event): void
    {
        $targets = $event->oktaEvent->target;
        $groupTarget = $targets[0] ?? null;

        $this->teamService->createFromOkta(
            oktaId: $groupTarget?->id,
            name: $groupTarget?->displayName,
        );
    }
}
```
