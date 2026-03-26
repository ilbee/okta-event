# User Import Events

Fired during Okta user import operations: start, completion, and roadblocks.

**Namespace:** `Ilbee\Okta\Event\Event\UserImport`

## Event hierarchy

- **Individual events** extend `OktaUserImportEvent` (group event)
- **Group event** `OktaUserImportEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaImportStartedEvent` | `system.import.start` | Import started |
| `OktaImportCompletedEvent` | `system.import.complete` | Import completed |
| `OktaImportRoadblockEvent` | `system.import.roadblock` | Import encountered a roadblock |

## Usage example

```php
use Ilbee\Okta\Event\Event\UserImport\OktaImportRoadblockEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnImportRoadblock
{
    public function __invoke(OktaImportRoadblockEvent $event): void
    {
        $this->alertService->warn('Okta user import roadblock', [
            'timestamp' => $event->oktaEvent->published,
        ]);
    }
}
```
