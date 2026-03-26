# Access Request Events

Fired when access requests are created, resolved, rejected, canceled, or expired. Also covers access request conditions, sequences, and settings.

**Namespace:** `Ilbee\Okta\Event\Event\AccessRequest`

## Event hierarchy

- **Individual events** extend `OktaAccessRequestEvent` (group event)
- **Group event** `OktaAccessRequestEvent` extends `GenericOktaEvent`

## Events

### Access Requests

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAccessRequestCreatedEvent` | `access.request.create` | Access request was created |
| `OktaAccessRequestResolvedEvent` | `access.request.resolve` | Access request was resolved |
| `OktaAccessRequestRejectedEvent` | `access.request.reject` | Access request was rejected |
| `OktaAccessRequestCanceledEvent` | `access.request.cancel` | Access request was canceled |
| `OktaAccessRequestExpiredEvent` | `access.request.expire` | Access request expired |

### Conditions

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAccessRequestConditionCreatedEvent` | `access.request.condition.create` | Condition was created |
| `OktaAccessRequestConditionUpdatedEvent` | `access.request.condition.update` | Condition was updated |
| `OktaAccessRequestConditionDeletedEvent` | `access.request.condition.delete` | Condition was deleted |
| `OktaAccessRequestConditionActivatedEvent` | `access.request.condition.activate` | Condition was activated |
| `OktaAccessRequestConditionDeactivatedEvent` | `access.request.condition.deactivate` | Condition was deactivated |
| `OktaAccessRequestConditionInvalidatedEvent` | `access.request.condition.invalidate` | Condition was invalidated |

### Sequences

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAccessRequestSequenceCreatedEvent` | `access.request.sequence.create` | Sequence was created |
| `OktaAccessRequestSequenceUpdatedEvent` | `access.request.sequence.update` | Sequence was updated |
| `OktaAccessRequestSequenceDeletedEvent` | `access.request.sequence.delete` | Sequence was deleted |

### Settings

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAccessRequestSettingsUpdatedEvent` | `access.request.settings.update` | Access request settings were updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\AccessRequest\OktaAccessRequestCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class NotifyOnAccessRequest
{
    public function __invoke(OktaAccessRequestCreatedEvent $event): void
    {
        $actor = $event->oktaEvent->actor;

        $this->notifier->send(
            channel: '#access-requests',
            message: sprintf('New access request from %s', $actor?->alternateId),
        );
    }
}
```
