# Security Events

Fired for security-related detections: risk changes, breached credentials, suspicious activity, and token usage outside allowed ranges.

**Namespace:** `Ilbee\Okta\Event\Event\Security`

## Event hierarchy

- **Individual events** extend `OktaSecurityEvent` (group event)
- **Group event** `OktaSecurityEvent` extends `GenericOktaEvent`

## Events

| Class | Okta Event Type | Description |
|---|---|---|
| `OktaUserRiskChangedEvent` | `user.risk.change` | User's risk level changed |
| `OktaUserRiskDetectedEvent` | `user.risk.detect` | Risk was detected for a user |
| `OktaBreachedCredentialDetectedEvent` | `security.breached_credential.detected` | Breached credential was detected |
| `OktaUserSuspiciousActivityReportedEvent` | `user.account.report_suspicious_activity_by_enduser` | User reported suspicious activity |
| `OktaUserBehaviorProfileResetEvent` | `user.behavior.profile.reset` | User behavior profile was reset |
| `OktaUserSessionContextChangedEvent` | `user.session.context.change` | User session context changed |
| `OktaPolicyAuthReevaluationFailedEvent` | `policy.auth_reevaluate.fail` | Policy auth re-evaluation failed |
| `OktaApiTokenOutsideAllowedRangeEvent` | `system.api_token.request_outside_allowed_range` | API token used from outside allowed IP range |
| `OktaOAuth2TokenOutsideAllowedRangeEvent` | `system.oauth2.token.request_outside_allowed_range` | OAuth2 token used from outside allowed range |

## Usage example

### React to breached credentials

```php
use Ilbee\Okta\Event\Event\Security\OktaBreachedCredentialDetectedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnBreachedCredential
{
    public function __invoke(OktaBreachedCredentialDetectedEvent $event): void
    {
        $userTarget = $event->oktaEvent->target[0] ?? null;

        $this->securityService->forcePasswordReset($userTarget?->alternateId);
        $this->notifier->alertSecurityTeam('Breached credential detected', [
            'user' => $userTarget?->alternateId,
        ]);
    }
}
```

### Monitor all security events

```php
use Ilbee\Okta\Event\Event\Security\OktaSecurityEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class SecurityEventMonitor
{
    public function __invoke(OktaSecurityEvent $event): void
    {
        $this->siem->ingest([
            'source' => 'okta',
            'event_type' => $event->oktaEvent->eventType,
            'actor' => $event->oktaEvent->actor?->alternateId,
            'targets' => array_map(fn ($t) => $t->alternateId, $event->oktaEvent->target),
            'timestamp' => $event->oktaEvent->published,
        ]);
    }
}
```
