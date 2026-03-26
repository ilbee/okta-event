# Group Membership Events

Fired when group membership, profile, or application assignments change.

**Namespace:** `Ilbee\Okta\Event\Event\Group`

## Event hierarchy

- **Individual events** extend `OktaGroupEvent` (group event)
- **Group event** `OktaGroupEvent` extends `GenericOktaEvent`
- **Exception:** `OktaGroupMemberAddedEvent` and `OktaGroupMemberRemovedEvent` extend `AbstractOktaEvent` directly (typed events with extra properties)

## Typed events with extra properties

These two events are dispatched as **typed events** via `OktaEventMapper`:

| Class | Okta Event Type |
|---|---|
| `OktaGroupMemberAddedEvent` | `group.user_membership.add` |
| `OktaGroupMemberRemovedEvent` | `group.user_membership.remove` |

**Properties:**

| Property | Type | Description |
|---|---|---|
| `userEmail` | `string` | Email of the affected user |
| `eventType` | `string` | Okta event type string |
| `target` | `OktaTarget` | User target object |
| `actor` | `?OktaActor` | Who performed the action |
| `groupId` | `string` | Okta group ID |
| `groupName` | `?string` | Group display name |

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaGroupMemberAddedEvent` | `group.user_membership.add` | User was added to a group |
| `OktaGroupMemberRemovedEvent` | `group.user_membership.remove` | User was removed from a group |
| `OktaGroupProfileUpdatedEvent` | `group.profile.update` | Group profile was updated |
| `OktaGroupAppAssignmentAddedEvent` | `group.application_assignment.add` | Application was assigned to a group |
| `OktaGroupAppAssignmentRemovedEvent` | `group.application_assignment.remove` | Application was removed from a group |
| `OktaGroupAppAssignmentUpdatedEvent` | `group.application_assignment.update` | Group application assignment was updated |

## Usage examples

### Sync group membership to local roles

```php
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberAddedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class SyncGroupMembership
{
    public function __invoke(OktaGroupMemberAddedEvent $event): void
    {
        $email = $event->userEmail;
        $groupId = $event->groupId;
        $groupName = $event->groupName;

        // Map Okta group to local role
        $role = $this->roleMapper->fromOktaGroup($groupId);
        if ($role) {
            $this->userService->grantRole($email, $role);
        }
    }
}
```

### Revoke access on group removal

```php
use Ilbee\Okta\Event\Event\Group\OktaGroupMemberRemovedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class RevokeOnGroupRemoval
{
    public function __invoke(OktaGroupMemberRemovedEvent $event): void
    {
        $role = $this->roleMapper->fromOktaGroup($event->groupId);
        if ($role) {
            $this->userService->revokeRole($event->userEmail, $role);
        }
    }
}
```
