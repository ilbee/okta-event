# User Authentication Events

Fired for authentication-related activities: sessions, MFA, password changes, API tokens, SSO, and account locks.

**Namespace:** `Ilbee\Okta\Event\Event\UserAuth`

## Event hierarchy

- **Individual events** extend `OktaUserAuthEvent` (group event)
- **Group event** `OktaUserAuthEvent` extends `GenericOktaEvent`

All events expose the raw `$oktaEvent` DTO with `eventType`, `target[]`, `actor`, `uuid`, and `published`.

## Events

### Sessions

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserSessionStartedEvent` | `user.session.start` | User started a new session |
| `OktaUserSessionEndedEvent` | `user.session.end` | User session ended |
| `OktaUserSessionClearedEvent` | `user.session.clear` | User sessions were cleared |

### Authentication

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserAuthSsoEvent` | `user.authentication.sso` | User authenticated via SSO |
| `OktaUserAuthViaMfaEvent` | `user.authentication.auth_via_mfa` | User authenticated via MFA |
| `OktaUserAuthViaIdpEvent` | `user.authentication.auth_via_IDP` | User authenticated via external IdP |
| `OktaUserAuthViaSocialEvent` | `user.authentication.auth_via_social` | User authenticated via social provider |
| `OktaUserUniversalLogoutEvent` | `user.authentication.universal_logout` | Universal logout was triggered |
| `OktaUserUniversalLogoutScheduledEvent` | `user.authentication.universal_logout.scheduled` | Universal logout was scheduled |
| `OktaAppSignOnDeniedEvent` | `application.policy.sign_on.deny_access` | Sign-on was denied by policy |

### MFA Factors

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserMfaFactorActivatedEvent` | `user.mfa.factor.activate` | MFA factor was activated |
| `OktaUserMfaFactorDeactivatedEvent` | `user.mfa.factor.deactivate` | MFA factor was deactivated |
| `OktaUserMfaFactorSuspendedEvent` | `user.mfa.factor.suspend` | MFA factor was suspended |
| `OktaUserMfaFactorUnsuspendedEvent` | `user.mfa.factor.unsuspend` | MFA factor was unsuspended |
| `OktaUserMfaFactorUpdatedEvent` | `user.mfa.factor.update` | MFA factor was updated |
| `OktaUserMfaFactorResetAllEvent` | `user.mfa.factor.reset_all` | All MFA factors were reset |
| `OktaMfaPreregisterInitiatedEvent` | `system.mfa.preregister.initiate` | MFA pre-registration was initiated |

### Passwords & Account

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserAccountPasswordResetEvent` | `user.account.reset_password` | Password was reset |
| `OktaUserAccountPasswordUpdatedEvent` | `user.account.update_password` | Password was updated |
| `OktaUserPasswordImportedEvent` | `user.import.password` | Password was imported |
| `OktaUserAccountLockedEvent` | `user.account.lock` | Account was locked |
| `OktaUserAccountUnlockedEvent` | `user.account.unlock` | Account was unlocked |
| `OktaUserAccountUnlockedByAdminEvent` | `user.account.unlock_by_admin` | Account was unlocked by an admin |

### Identity Verification

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserIdentityVerificationEvent` | `user.identity_verification` | Identity verification completed |
| `OktaUserIdentityVerificationStartedEvent` | `user.identity_verification.start` | Identity verification started |

### API Tokens

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaApiTokenCreatedEvent` | `system.api_token.create` | API token was created |
| `OktaApiTokenRevokedEvent` | `system.api_token.revoke` | API token was revoked |

### Phone Verification

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaPhoneVerificationSmsSentEvent` | `system.sms.send_phone_verification_message` | Phone verification SMS was sent |
| `OktaPhoneVerificationCallSentEvent` | `system.voice.send_phone_verification_call` | Phone verification call was sent |

## Usage examples

### Detect suspicious authentication patterns

```php
use Ilbee\Okta\Event\Event\UserAuth\OktaUserAccountLockedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class NotifyOnAccountLock
{
    public function __invoke(OktaUserAccountLockedEvent $event): void
    {
        $userTarget = $event->oktaEvent->target[0] ?? null;
        $email = $userTarget?->alternateId;

        $this->notifier->sendAlert("Account locked: {$email}");
    }
}
```

### Audit all authentication events

```php
use Ilbee\Okta\Event\Event\UserAuth\OktaUserAuthEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AuditAuthEvents
{
    public function __invoke(OktaUserAuthEvent $event): void
    {
        $this->auditLog->record(
            type: $event->oktaEvent->eventType,
            actor: $event->oktaEvent->actor?->alternateId,
            timestamp: $event->oktaEvent->published,
        );
    }
}
```
