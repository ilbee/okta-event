# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-03-27

### Security

- Require `X-Auth-Token` on GET verification endpoint (#4)
- Validate `Content-Type: application/json` before JSON deserialization, return 415 if missing or wrong (#5)
- Pre-check `Content-Length` header before reading the full body to reject oversized payloads early (#5)

## [1.0.0] - 2026-03-26

### Added

- Automatic webhook endpoint with GET verification and POST processing
- 200+ typed events covering the full Okta Event Hook catalog
- Three-layer event dispatch: typed events, individual events, and group events
- `GenericOktaEvent` fallback for unhandled event types
- Duplicate event detection with pluggable store (cache-based or null)
- Configurable payload size and event count limits
- Full documentation for all event categories

### Security

- Compiler pass warning when `webhook_secret` is not using `%env(...)%` notation (#3)
- Hardened replay protection defaults (#1)

[1.0.1]: https://github.com/ilbee/okta-event/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/ilbee/okta-event/releases/tag/v1.0.0
