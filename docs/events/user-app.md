# User Application Events

Fired for user-to-application interactions: assignments, access requests, OAuth2 consent, provisioning, and inter-client operations.

**Namespace:** `Ilbee\Okta\Event\Event\UserApp`

## Event hierarchy

- **Individual events** extend `OktaUserAppEvent` (group event)
- **Group event** `OktaUserAppEvent` extends `GenericOktaEvent`
- **Exception:** `OktaAppUserAssignedEvent` and `OktaAppUserUnassignedEvent` extend `AbstractOktaEvent` directly (typed events with extra properties)

## Typed events with extra properties

These two events are dispatched as **typed events** via `OktaEventMapper` and have additional properties:

| Class | Okta Event Type |
|---|---|
| `OktaAppUserAssignedEvent` | `application.user_membership.add` |
| `OktaAppUserUnassignedEvent` | `application.user_membership.remove` |

**Properties:**

| Property | Type | Description |
|---|---|---|
| `userEmail` | `string` | Email of the affected user |
| `eventType` | `string` | Okta event type string |
| `target` | `OktaTarget` | User target object |
| `actor` | `?OktaActor` | Who performed the action |
| `appId` | `string` | Okta application instance ID |
| `appName` | `?string` | Application display name |

## Events

### Application Membership

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAppUserAssignedEvent` | `application.user_membership.add` | User was assigned to an app |
| `OktaAppUserUnassignedEvent` | `application.user_membership.remove` | User was unassigned from an app |
| `OktaAppUserUpdatedEvent` | `application.user_membership.update` | App user membership was updated |
| `OktaAppUserPasswordChangedEvent` | `application.user_membership.change_password` | App user's password was changed |

### Access Requests

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAppAccessRequestedEvent` | `app.access_request.request` | User requested access to an app |
| `OktaAppAccessApprovedEvent` | `app.access_request.approver.approve` | Access request was approved |
| `OktaAppAccessDeniedByApproverEvent` | `app.access_request.approver.deny` | Access request was denied by approver |
| `OktaAppAccessDeniedEvent` | `app.access_request.deny` | Access request was denied |
| `OktaAppAccessGrantedEvent` | `app.access_request.grant` | Access was granted |
| `OktaAppAccessExpiredEvent` | `app.access_request.expire` | Access request expired |
| `OktaAppAccessDeletedEvent` | `app.access_request.delete` | Access request was deleted |

### Provisioning

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaAppUserSyncedEvent` | `application.provision.user.sync` | App user was synced |
| `OktaAppUserProfileImportedEvent` | `application.provision.user.import_profile` | User profile was imported from app |
| `OktaAppUserPushedEvent` | `application.provision.user.push` | User was pushed to app |
| `OktaAppUserProfilePushedEvent` | `application.provision.user.push_profile` | User profile was pushed to app |
| `OktaAppUserReactivatedEvent` | `application.provision.user.reactivate` | App user was reactivated |

### OAuth2 Consent

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaOAuth2ConsentGrantedEvent` | `app.oauth2.as.consent.grant` | OAuth2 consent was granted |
| `OktaOAuth2ConsentRevokedEvent` | `app.oauth2.as.consent.revoke` | OAuth2 consent was revoked |
| `OktaOAuth2ConsentRevokedForServerEvent` | `app.oauth2.as.consent.revoke.implicit.as` | Consent revoked for authorization server |
| `OktaOAuth2ConsentRevokedForClientEvent` | `app.oauth2.as.consent.revoke.implicit.client` | Consent revoked for client |
| `OktaOAuth2ConsentRevokedForScopeEvent` | `app.oauth2.as.consent.revoke.implicit.scope` | Consent revoked for scope |
| `OktaOAuth2ConsentRevokedForUserScopesEvent` | `app.oauth2.as.consent.revoke.implicit.user` | Consent revoked for user scopes |
| `OktaOAuth2ConsentRevokedAllForUserEvent` | `app.oauth2.as.consent.revoke.user` | All consent revoked for user |
| `OktaOAuth2ConsentRevokedUserClientEvent` | `app.oauth2.as.consent.revoke.user.client` | Consent revoked for user+client pair |

### Inter-client Token

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaOAuth2InterclientTokenGrantedEvent` | `app.oauth2.as.token.grant.interclient_token` | Inter-client token granted (auth server) |
| `OktaOAuth2TokenInterclientGrantedEvent` | `app.oauth2.token.grant.interclient_token` | Inter-client token granted |
| `OktaInterclientMappingCreatedEvent` | `app.interclient_mapping.create` | Inter-client mapping created |
| `OktaInterclientMappingDeletedEvent` | `app.interclient_mapping.delete` | Inter-client mapping deleted |
| `OktaInterclientMappingAllDeletedEvent` | `app.interclient_mapping.delete_all` | All inter-client mappings deleted |

## Usage examples

### Track app assignments

```php
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserAssignedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnAppUserAssigned
{
    public function __invoke(OktaAppUserAssignedEvent $event): void
    {
        // Typed event with extra properties
        $email = $event->userEmail;
        $appId = $event->appId;
        $appName = $event->appName;

        $this->licenseService->allocate($email, $appId);
    }
}
```

### Revoke local access on app unassignment

```php
use Ilbee\Okta\Event\Event\UserApp\OktaAppUserUnassignedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnAppUserUnassigned
{
    public function __invoke(OktaAppUserUnassignedEvent $event): void
    {
        $this->accessManager->revoke($event->userEmail, $event->appId);
    }
}
```
