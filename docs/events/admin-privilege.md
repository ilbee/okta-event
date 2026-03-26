# Admin Privilege Events

Fired when administrative privileges are granted or revoked, and when IAM roles, resource sets, and policies are managed.

**Namespace:** `Ilbee\Okta\Event\Event\AdminPrivilege`

## Event hierarchy

- **Individual events** extend `OktaAdminPrivilegeEvent` (group event)
- **Group event** `OktaAdminPrivilegeEvent` extends `GenericOktaEvent`

## Events

### Privilege Grant/Revoke

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserPrivilegeGrantedEvent` | `user.account.privilege.grant` | Admin privilege granted to a user |
| `OktaUserPrivilegeRevokedEvent` | `user.account.privilege.revoke` | Admin privilege revoked from a user |
| `OktaGroupPrivilegeGrantedEvent` | `group.privilege.grant` | Privilege granted to a group |
| `OktaGroupPrivilegeRevokedEvent` | `group.privilege.revoke` | Privilege revoked from a group |
| `OktaOAuth2ClientPrivilegeGrantedEvent` | `app.oauth2.client.privilege.grant` | Privilege granted to an OAuth2 client |
| `OktaOAuth2ClientPrivilegeRevokedEvent` | `app.oauth2.client.privilege.revoke` | Privilege revoked from an OAuth2 client |

### IAM Roles

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIamRoleCreatedEvent` | `iam.role.create` | Custom IAM role was created |
| `OktaIamRoleUpdatedEvent` | `iam.role.update` | IAM role was updated |
| `OktaIamRoleDeletedEvent` | `iam.role.delete` | IAM role was deleted |
| `OktaIamRolePermissionsAddedEvent` | `iam.role.permissions.add` | Permissions added to a role |
| `OktaIamRolePermissionsDeletedEvent` | `iam.role.permissions.delete` | Permissions removed from a role |
| `OktaIamPermissionConditionsAddedEvent` | `iam.role.permission.conditions.add` | Conditions added to a permission |
| `OktaIamPermissionConditionsDeletedEvent` | `iam.role.permission.conditions.delete` | Conditions removed from a permission |

### IAM Resource Sets

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIamResourceSetCreatedEvent` | `iam.resourceset.create` | Resource set was created |
| `OktaIamResourceSetUpdatedEvent` | `iam.resourceset.update` | Resource set was updated |
| `OktaIamResourceSetDeletedEvent` | `iam.resourceset.delete` | Resource set was deleted |
| `OktaIamResourceSetResourcesAddedEvent` | `iam.resourceset.resources.add` | Resources added to a set |
| `OktaIamResourceSetResourcesUpdatedEvent` | `iam.resourceset.resources.update` | Resources updated in a set |
| `OktaIamResourceSetResourcesDeletedEvent` | `iam.resourceset.resources.delete` | Resources removed from a set |
| `OktaIamResourceSetBindingsAddedEvent` | `iam.resourceset.bindings.add` | Bindings added to a resource set |
| `OktaIamResourceSetBindingsDeletedEvent` | `iam.resourceset.bindings.delete` | Bindings removed from a resource set |

### IAM Policy

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaIamPolicyConfigurationUpdatedEvent` | `iam.policy.configuration.update` | IAM policy configuration was updated |
| `OktaIamPolicyAssigneeConfigurationUpdatedEvent` | `iam.policy.assignee_configuration.update` | IAM policy assignee configuration was updated |

## Usage example

```php
use Ilbee\Okta\Event\Event\AdminPrivilege\OktaUserPrivilegeGrantedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditPrivilegeEscalation
{
    public function __invoke(OktaUserPrivilegeGrantedEvent $event): void
    {
        $actor = $event->oktaEvent->actor;
        $targets = $event->oktaEvent->target;

        $this->securityAudit->logPrivilegeGrant(
            grantedBy: $actor?->alternateId,
            grantedTo: $targets[0]?->alternateId ?? 'unknown',
        );
    }
}
```
