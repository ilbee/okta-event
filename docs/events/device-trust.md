# Device Trust Events

Fired for device trust authentication and credential enrollment.

**Namespace:** `Ilbee\Okta\Event\Event\DeviceTrust`

## Event hierarchy

- **Individual events** extend `OktaDeviceTrustEvent` (group event)
- **Group event** `OktaDeviceTrustEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaDeviceTrustAuthenticatedEvent` | `user.authentication.authenticate` | Device trust authentication succeeded |
| `OktaDeviceTrustCredentialEnrolledEvent` | `user.credential.enroll` | Device trust credential was enrolled |

## Usage example

```php
use Ilbee\Okta\Event\Event\DeviceTrust\OktaDeviceTrustEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TrackDeviceTrust
{
    public function __invoke(OktaDeviceTrustEvent $event): void
    {
        $this->logger->info('Device trust event', [
            'type' => $event->oktaEvent->eventType,
            'actor' => $event->oktaEvent->actor?->alternateId,
        ]);
    }
}
```
