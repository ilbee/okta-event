# Log Stream Events

Fired when Okta log streams are created, updated, activated, deactivated, or deleted.

**Namespace:** `Ilbee\Okta\Event\Event\LogStream`

## Event hierarchy

- **Individual events** extend `OktaLogStreamEvent` (group event)
- **Group event** `OktaLogStreamEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaLogStreamCreatedEvent` | `system.log_stream.lifecycle.create` | Log stream was created |
| `OktaLogStreamUpdatedEvent` | `system.log_stream.lifecycle.update` | Log stream was updated |
| `OktaLogStreamActivatedEvent` | `system.log_stream.lifecycle.activate` | Log stream was activated |
| `OktaLogStreamDeactivatedEvent` | `system.log_stream.lifecycle.deactivate` | Log stream was deactivated |
| `OktaLogStreamDeletedEvent` | `system.log_stream.lifecycle.delete` | Log stream was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\LogStream\OktaLogStreamEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditLogStreamChanges
{
    public function __invoke(OktaLogStreamEvent $event): void
    {
        $this->auditLog->record(
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
