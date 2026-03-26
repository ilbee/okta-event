# Entitlement Management Events

Fired for entitlement and resource management: entitlements, bundles, collections, policies, governance engine, risk rules, owners, and labels.

**Namespace:** `Ilbee\Okta\Event\Event\EntitlementManagement`

## Event hierarchy

- **Individual events** extend `OktaEntitlementManagementEvent` (group event)
- **Group event** `OktaEntitlementManagementEvent` extends `GenericOktaEvent`

## Events

### User Entitlements

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaResourceUserEntitlementsUpdatedEvent` | `resource.user_entitlements.update` | User entitlements were updated |

### Entitlements

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaResourceEntitlementCreatedEvent` | `resource.entitlement.create` | Entitlement was created |
| `OktaResourceEntitlementUpdatedEvent` | `resource.entitlement.update` | Entitlement was updated |
| `OktaResourceEntitlementDeletedEvent` | `resource.entitlement.delete` | Entitlement was deleted |

### Entitlement Bundles

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaResourceEntitlementBundleCreatedEvent` | `resource.entitlement_bundle.create` | Entitlement bundle was created |
| `OktaResourceEntitlementBundleUpdatedEvent` | `resource.entitlement_bundle.update` | Entitlement bundle was updated |
| `OktaResourceEntitlementBundleDeletedEvent` | `resource.entitlement_bundle.delete` | Entitlement bundle was deleted |

### Entitlement Policies

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaEntitlementPolicyActivatedEvent` | `resource.entitlement_policy.activate` | Entitlement policy was activated |
| `OktaEntitlementPolicyDraftCreatedEvent` | `resource.entitlement_policy.draft.create` | Policy draft was created |
| `OktaEntitlementPolicyDraftUpdatedEvent` | `resource.entitlement_policy.draft.update` | Policy draft was updated |
| `OktaEntitlementPolicyDraftDeletedEvent` | `resource.entitlement_policy.draft.delete` | Policy draft was deleted |

### Resource Collections

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaResourceCollectionCreatedEvent` | `resource.collection.create` | Collection was created |
| `OktaResourceCollectionUpdatedEvent` | `resource.collection.update` | Collection was updated |
| `OktaResourceCollectionDeletedEvent` | `resource.collection.delete` | Collection was deleted |
| `OktaResourceCollectionAssignedEvent` | `resource.collection.assign` | Collection was assigned |
| `OktaResourceCollectionUnassignedEvent` | `resource.collection.unassign` | Collection was unassigned |

### Governance Engine

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaGovernanceEngineEnabledEvent` | `resource.governance_engine.enable` | Governance engine was enabled |
| `OktaGovernanceEngineDisabledEvent` | `resource.governance_engine.disable` | Governance engine was disabled |

### Risk Rules

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaRiskRuleCreatedEvent` | `resource.risk_rule.create` | Risk rule was created |
| `OktaRiskRuleUpdatedEvent` | `resource.risk_rule.update` | Risk rule was updated |
| `OktaRiskRuleDeletedEvent` | `resource.risk_rule.delete` | Risk rule was deleted |

### Resource Metadata

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaResourceOwnerUpdatedEvent` | `resource.owner.update` | Resource owner was updated |
| `OktaResourceLabelUpdatedEvent` | `resource.label.update` | Resource label was updated |
| `OktaResourceLabelAssignmentUpdatedEvent` | `resource.label.assignment.update` | Label assignment was updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\EntitlementManagement\OktaEntitlementManagementEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditEntitlementChanges
{
    public function __invoke(OktaEntitlementManagementEvent $event): void
    {
        $this->governanceLog->record(
            action: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
            timestamp: $event->oktaEvent->published,
        );
    }
}
```
