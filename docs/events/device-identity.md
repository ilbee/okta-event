# Device Identity Events

Fired when devices are enrolled, activated, deactivated, suspended, deleted, or when users are added/removed from devices.

**Namespace:** `Ilbee\Okta\Event\Event\DeviceIdentity`

## Event hierarchy

- **Individual events** extend `OktaDeviceIdentityEvent` (group event)
- **Group event** `OktaDeviceIdentityEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaDeviceEnrolledEvent` | `device.enrollment.create` | Device was enrolled |
| `OktaDeviceActivatedEvent` | `device.lifecycle.activate` | Device was activated |
| `OktaDeviceDeactivatedEvent` | `device.lifecycle.deactivate` | Device was deactivated |
| `OktaDeviceSuspendedEvent` | `device.lifecycle.suspend` | Device was suspended |
| `OktaDeviceUnsuspendedEvent` | `device.lifecycle.unsuspend` | Device was unsuspended |
| `OktaDeviceDeletedEvent` | `device.lifecycle.delete` | Device was deleted |
| `OktaDeviceUserAddedEvent` | `device.user.add` | User was added to a device |
| `OktaDeviceUserRemovedEvent` | `device.user.remove` | User was removed from a device |

## Usage example

```php
use Ilbee\Okta\Event\Event\DeviceIdentity\OktaDeviceEnrolledEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnDeviceEnrolled
{
    public function __invoke(OktaDeviceEnrolledEvent $event): void
    {
        $deviceTarget = $event->oktaEvent->target[0] ?? null;

        $this->deviceInventory->register(
            oktaId: $deviceTarget?->id,
            name: $deviceTarget?->displayName,
        );
    }
}
```
