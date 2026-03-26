# Access Review Events

Fired during access review campaigns: creation, start, closure, updates, actions, and remediation.

**Namespace:** `Ilbee\Okta\Event\Event\AccessReview`

## Event hierarchy

- **Individual events** extend `OktaAccessReviewEvent` (group event)
- **Group event** `OktaAccessReviewEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAccessReviewCreatedEvent` | `access.review.create` | Access review was created |
| `OktaAccessReviewStartedEvent` | `access.review.start` | Access review was started |
| `OktaAccessReviewClosedEvent` | `access.review.close` | Access review was closed |
| `OktaAccessReviewUpdatedEvent` | `access.review.update` | Access review was updated |
| `OktaAccessReviewActionEvent` | `access.review.action` | Action was taken on an access review |
| `OktaAccessReviewRemediatedEvent` | `access.review.remediate` | Access review was remediated |

## Usage example

```php
use Ilbee\Okta\Event\Event\AccessReview\OktaAccessReviewEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class TrackAccessReviews
{
    public function __invoke(OktaAccessReviewEvent $event): void
    {
        $this->complianceLog->record(
            category: 'access_review',
            action: $event->oktaEvent->eventType,
            timestamp: $event->oktaEvent->published,
        );
    }
}
```
