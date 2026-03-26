# Rate Limit Events

Fired when Okta rate limit warnings or violations occur.

**Namespace:** `Ilbee\Okta\Event\Event\RateLimit`

## Event hierarchy

- **Individual events** extend `OktaRateLimitEvent` (group event)
- **Group event** `OktaRateLimitEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaRateLimitWarningEvent` | `system.org.rate_limit.warning` | Rate limit warning threshold reached |
| `OktaRateLimitViolationEvent` | `system.org.rate_limit.violation` | Rate limit was violated |

## Usage example

```php
use Ilbee\Okta\Event\Event\RateLimit\OktaRateLimitViolationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnRateLimitViolation
{
    public function __invoke(OktaRateLimitViolationEvent $event): void
    {
        $this->alertService->critical('Okta rate limit violated', [
            'actor' => $event->oktaEvent->actor?->alternateId,
            'timestamp' => $event->oktaEvent->published,
        ]);
    }
}
```
