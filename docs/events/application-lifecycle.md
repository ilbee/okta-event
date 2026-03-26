# Application Lifecycle Events

Fired when applications are created, activated, deactivated, updated, or deleted in Okta.

**Namespace:** `Ilbee\Okta\Event\Event\ApplicationLifecycle`

## Event hierarchy

- **Individual events** extend `OktaApplicationLifecycleEvent` (group event)
- **Group event** `OktaApplicationLifecycleEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaApplicationCreatedEvent` | `application.lifecycle.create` | Application was created |
| `OktaApplicationActivatedEvent` | `application.lifecycle.activate` | Application was activated |
| `OktaApplicationDeactivatedEvent` | `application.lifecycle.deactivate` | Application was deactivated |
| `OktaApplicationUpdatedEvent` | `application.lifecycle.update` | Application was updated |
| `OktaApplicationDeletedEvent` | `application.lifecycle.delete` | Application was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\ApplicationLifecycle\OktaApplicationDeactivatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnAppDeactivated
{
    public function __invoke(OktaApplicationDeactivatedEvent $event): void
    {
        $appTarget = $event->oktaEvent->target[0] ?? null;

        $this->integrationService->disable($appTarget?->id);
    }
}
```
