# Group Application Events

Fired when group push mapping operations fail.

**Namespace:** `Ilbee\Okta\Event\Event\GroupApp`

## Event hierarchy

- **Individual events** extend `OktaGroupAppEvent` (group event)
- **Group event** `OktaGroupAppEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaGroupPushMappingFailedEvent` | `application.provision.group_push.mapping.update.or.delete.failed.with.error` | Group push mapping update or deletion failed |

## Usage example

```php
use Ilbee\Okta\Event\Event\GroupApp\OktaGroupPushMappingFailedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnGroupPushFailure
{
    public function __invoke(OktaGroupPushMappingFailedEvent $event): void
    {
        $this->alertService->notify(
            'Group push mapping failed',
            ['event' => $event->oktaEvent->eventType],
        );
    }
}
```
