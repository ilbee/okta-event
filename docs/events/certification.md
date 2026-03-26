# Certification Events

Fired during Okta Identity Governance certification campaigns: launch, closure, item decisions, remediation, and context updates.

**Namespace:** `Ilbee\Okta\Event\Event\Certification`

## Event hierarchy

- **Individual events** extend `OktaCertificationEvent` (group event)
- **Group event** `OktaCertificationEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaCertificationCampaignLaunchedEvent` | `certification.campaign.launch` | Certification campaign was launched |
| `OktaCertificationCampaignClosedEvent` | `certification.campaign.close` | Certification campaign was closed |
| `OktaCertificationItemDecidedEvent` | `certification.campaign.item.decide` | Decision was made on a certification item |
| `OktaCertificationItemRemediatedEvent` | `certification.campaign.item.remediate` | Certification item was remediated |
| `OktaCertificationContextUpdatedEvent` | `certification.campaign.context.update` | Certification campaign context was updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\Certification\OktaCertificationCampaignLaunchedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnCertificationLaunched
{
    public function __invoke(OktaCertificationCampaignLaunchedEvent $event): void
    {
        $this->notifier->send(
            channel: '#compliance',
            message: 'New certification campaign launched',
        );
    }
}
```
