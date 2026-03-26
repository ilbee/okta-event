# Inline Hook Events

Fired when an Okta inline hook is executed.

**Namespace:** `Ilbee\Okta\Event\Event\InlineHook`

## Event hierarchy

- **Individual events** extend `OktaInlineHookEvent` (group event)
- **Group event** `OktaInlineHookEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaInlineHookExecutedEvent` | `inline_hook.executed` | An inline hook was executed |

## Usage example

```php
use Ilbee\Okta\Event\Event\InlineHook\OktaInlineHookExecutedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class MonitorInlineHooks
{
    public function __invoke(OktaInlineHookExecutedEvent $event): void
    {
        $this->metrics->increment('okta.inline_hook.executed');
    }
}
```
