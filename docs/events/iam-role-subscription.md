# IAM Role Subscription Events

Fired when IAM role notification subscriptions are updated.

**Namespace:** `Ilbee\Okta\Event\Event\IamRoleSubscription`

## Event hierarchy

- **Individual events** extend `OktaIamRoleSubscriptionEvent` (group event)
- **Group event** `OktaIamRoleSubscriptionEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIamRoleSubscriptionUpdatedEvent` | `iam.role.subscriptions.update` | Role subscription was updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\IamRoleSubscription\OktaIamRoleSubscriptionUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnRoleSubscriptionUpdated
{
    public function __invoke(OktaIamRoleSubscriptionUpdatedEvent $event): void
    {
        $this->auditLog->record(
            action: 'iam_role_subscription_updated',
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
