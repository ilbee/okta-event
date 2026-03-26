# Okta Event Bundle

[![CI](https://github.com/ilbee/okta-event/actions/workflows/ci.yml/badge.svg)](https://github.com/ilbee/okta-event/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-8892BF)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.x-black)](https://symfony.com/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%2010-brightgreen)](https://phpstan.org/)
[![License](https://img.shields.io/badge/License-MIT-blue)](LICENSE)

A Symfony bundle that receives [Okta Event Hooks](https://developer.okta.com/docs/concepts/event-hooks/) and dispatches typed Symfony events you can listen to in your application.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Okta Setup](#okta-setup)
- [Usage](#usage)
- [Security Recommendations](#security-recommendations)
- [Contributing](#contributing)
- [License](#license)
- [Full Documentation](docs/index.md)

## Features

- Automatic webhook endpoint registration (GET verification + POST processing)
- **200+ typed events** covering the full Okta Event Hook catalog:

| Category | Examples |
|---|---|
| User Lifecycle | activate, create, deactivate, suspend, delete, password reset, profile update |
| User Authentication | session start/end, MFA enroll/reset, SSO, password change, account lock |
| Group Management | member add/remove, group create/delete, profile update |
| Application | user assign/unassign, app create/activate/deactivate, OAuth2 consent |
| Admin Privileges | role grant/revoke, IAM resource set/role/permission changes |
| Security | risk detection, breached credentials, suspicious activity, session context change |
| Policy | policy/rule activate/deactivate/update, trusted server changes |
| Device | device enroll/activate/suspend/delete, device trust, user add/remove |
| Access Request | request create/resolve/reject/expire, conditions, sequences |
| And more... | IdP lifecycle, log streams, inline hooks, rate limits, certifications, entitlements |

- Duplicate event detection (pluggable store, cache-based or null)
- Configurable payload size and event count limits
- `GenericOktaEvent` fallback for unhandled event types

## Installation

```bash
composer require ilbee/okta-event
```

If you don't use Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
Ilbee\Okta\Event\OktaEventBundle::class => ['all' => true],
```

## Configuration

```yaml
# config/packages/okta_event.yaml
okta_event:
  # Required - shared secret configured in the Okta Event Hook
  webhook_secret: '%env(OKTA_WEBHOOK_SECRET)%'

  # Optional - defaults shown
  # route: '/okta/webhook'
  # verification_enabled: true
  # max_payload_size: 1048576      # 1 MB
  # max_events_per_request: 100
```

## Okta Setup

1. In Okta Admin, go to **Workflow > Event Hooks > Create Event Hook**.
2. Set the **URL** to your endpoint (e.g. `https://example.com/okta/webhook`).
3. Set **Authentication field** to `X-Auth-Token` and provide the same secret as `webhook_secret`.
4. Subscribe to the events you need.
5. Save — Okta sends a one-time GET verification that the bundle handles automatically. You can disable it afterwards with `verification_enabled: false`.

## Usage

Listen to any typed event using Symfony's `#[AsEventListener]`:

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserDeactivatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OnUserDeactivated
{
    public function __invoke(OktaUserDeactivatedEvent $event): void
    {
        $email = $event->userEmail;
        $actor = $event->actor;
        // ...
    }
}
```

You can also listen to group-level events to handle an entire category:

```php
use Ilbee\Okta\Event\Event\UserLifecycle\OktaUserLifecycleEvent;

#[AsEventListener]
class OnAnyUserLifecycleChange
{
    public function __invoke(OktaUserLifecycleEvent $event): void
    {
        // Fired for any user.lifecycle.* event
    }
}
```

For unknown/unhandled event types, a `GenericOktaEvent` is dispatched as fallback:

```php
use Ilbee\Okta\Event\Event\GenericOktaEvent;

#[AsEventListener]
class OnUnknownOktaEvent
{
    public function __invoke(GenericOktaEvent $event): void
    {
        $rawEvent = $event->oktaEvent; // OktaEvent DTO with full payload
    }
}
```

## Security Recommendations

For production, restrict traffic to [Okta's IP ranges](https://developer.okta.com/docs/reference/ip-addresses/) at the reverse proxy level:

```nginx
location /okta/webhook {
    allow 100.21.118.0/24;
    allow 52.2.12.0/24;
    deny all;
    proxy_pass http://your-app;
}
```

## Contributing

```bash
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/php-cs-fixer check ./src --diff --allow-risky=yes
```

## License

[MIT](LICENSE)
