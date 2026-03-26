# Policy Events

Fired when Okta policies and policy rules are created, updated, activated, deactivated, or deleted. Also covers trusted server changes.

**Namespace:** `Ilbee\Okta\Event\Event\Policy`

## Event hierarchy

- **Individual events** extend `OktaPolicyEvent` (group event)
- **Group event** `OktaPolicyEvent` extends `GenericOktaEvent`

## Events

### Policies

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaPolicyActivatedEvent` | `policy.lifecycle.activate` | Policy was activated |
| `OktaPolicyDeactivatedEvent` | `policy.lifecycle.deactivate` | Policy was deactivated |
| `OktaPolicyUpdatedEvent` | `policy.lifecycle.update` | Policy was updated |

### Policy Rules

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaPolicyRuleAddedEvent` | `policy.rule.add` | Policy rule was added |
| `OktaPolicyRuleUpdatedEvent` | `policy.rule.update` | Policy rule was updated |
| `OktaPolicyRuleActivatedEvent` | `policy.rule.activate` | Policy rule was activated |
| `OktaPolicyRuleDeactivatedEvent` | `policy.rule.deactivate` | Policy rule was deactivated |
| `OktaPolicyRuleDeletedEvent` | `policy.rule.delete` | Policy rule was deleted |

### Trusted Servers

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaTrustedServerAddedEvent` | `app.oauth2.trusted_server.add` | Trusted OAuth2 server was added |
| `OktaTrustedServerDeletedEvent` | `app.oauth2.trusted_server.delete` | Trusted OAuth2 server was deleted |

## Usage example

```php
use Ilbee\Okta\Event\Event\Policy\OktaPolicyEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditPolicyChanges
{
    public function __invoke(OktaPolicyEvent $event): void
    {
        $this->changeLog->record(
            category: 'okta_policy',
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
        );
    }
}
```
