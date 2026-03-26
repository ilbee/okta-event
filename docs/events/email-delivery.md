# Email Delivery Events

Fired when an Okta email delivery status is updated.

**Namespace:** `Ilbee\Okta\Event\Event\EmailDelivery`

## Event hierarchy

- **Individual events** extend `OktaEmailDeliveryEvent` (group event)
- **Group event** `OktaEmailDeliveryEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaEmailDeliveryStatusUpdatedEvent` | `system.email.delivery` | Email delivery status was updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\EmailDelivery\OktaEmailDeliveryStatusUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TrackEmailDelivery
{
    public function __invoke(OktaEmailDeliveryStatusUpdatedEvent $event): void
    {
        $this->metrics->increment('okta.email.delivery');
    }
}
```
