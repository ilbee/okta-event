# User Account Subscription Events

Fired when a user's account notification subscriptions are updated.

**Namespace:** `Ilbee\Okta\Event\Event\UserAccountSubscription`

## Event hierarchy

- **Individual events** extend `OktaUserAccountSubscriptionEvent` (group event)
- **Group event** `OktaUserAccountSubscriptionEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserAccountSubscriptionUpdatedEvent` | `user.account.subscriptions.update` | Account subscription preferences were updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\UserAccountSubscription\OktaUserAccountSubscriptionUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnSubscriptionUpdated
{
    public function __invoke(OktaUserAccountSubscriptionUpdatedEvent $event): void
    {
        $this->logger->info('User subscription preferences updated', [
            'actor' => $event->oktaEvent->actor?->alternateId,
        ]);
    }
}
```
