# WebFiori Framework

<p align="center">
<img width="90px" hight="90px" src="https://webfiori.com/assets/images/favicon.png">
</p>

<p align="center">
  <a href="https://github.com/WebFiori/framework/actions"><img src="https://github.com/WebFiori/framework/actions/workflows/php85.yml/badge.svg?branch=main"></a>
  <a href="https://codecov.io/gh/WebFiori/framework">
    <img src="https://codecov.io/gh/WebFiori/framework/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_framework">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_framework&metric=alert_status" />
  </a>
  <a href="https://github.com/WebFiori/framework/releases">
      <img src="https://img.shields.io/github/release/WebFiori/framework.svg?label=latest" />
  </a>
  <a href="https://packagist.org/packages/webfiori/framework">
      <img src="https://img.shields.io/packagist/dt/webfiori/framework?color=light-green">
  </a>
</p>

> **Note:** This repo contains the core of the framework. The application template can be found at [`webfiori/app`](https://github.com/webfiori/app).

## Overview

WebFiori is a modular, object-oriented PHP framework designed for building secure web applications and APIs. It provides a complete toolkit — routing, middleware, authorization, database management, job queues, and more — while remaining lightweight (under 3 MB core) and free of heavy external dependencies.

## Requirements

- PHP 8.1 or later
- Extensions: `json`, `mbstring`, `fileinfo`, `openssl`
- Composer

## Supported PHP Versions

| PHP Version | Status |
|:-----------:|:------:|
| 8.1 | <a href="https://github.com/WebFiori/framework/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php81.yml/badge.svg?branch=main"></a> |
| 8.2 | <a href="https://github.com/WebFiori/framework/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php82.yml/badge.svg?branch=main"></a> |
| 8.3 | <a href="https://github.com/WebFiori/framework/actions/workflows/php83.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php83.yml/badge.svg?branch=main"></a> |
| 8.4 | <a href="https://github.com/WebFiori/framework/actions/workflows/php84.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php84.yml/badge.svg?branch=main"></a> |
| 8.5 | <a href="https://github.com/WebFiori/framework/actions/workflows/php85.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php85.yml/badge.svg?branch=main"></a> |

## Quick Start

```bash
composer create-project webfiori/app my-project
cd my-project
php -S localhost:8080 -t public
```

## Key Features

### Routing & HTTP
- Attribute-based and configuration-based route definitions
- Route groups, URI parameters, and middleware assignment
- OpenAPI 3.1 specification generation from annotated controllers

### Security
- CSRF protection middleware with automatic token injection
- CORS middleware with per-route configuration
- Rate limiting with configurable windows and trusted IPs
- Session encryption at rest (AES-256-GCM)
- Maintenance mode with IP allowlisting and Retry-After headers

### Authorization
- Role-Based Access Control (RBAC) with role inheritance
- Attribute-Based Access Control (ABAC) with policy evaluation
- Database-backed or in-memory storage for roles and permissions
- `#[PreAuthorize]` and `#[RequiresAuth]` attributes for declarative access control

### Database
- Query builder supporting MySQL, MSSQL, and SQLite
- Schema migrations with run, rollback, dry-run, fresh, and status commands
- Database seeders
- Connection management with environment variable resolution

### Middleware
- Priority-based execution with dependency resolution
- Before, after, and after-send lifecycle hooks
- Middleware groups for bulk assignment to routes
- Built-in: session, CSRF, CORS, rate limiting, caching, maintenance mode, authorization

### Job Queue
- Dispatching with priority and delayed execution
- Automatic retry with configurable attempts and backoff
- Payload encryption (AES-256-GCM) via environment key
- Failed job tracking and retry commands
- Pluggable storage backends via `QueueStorage` interface

### Dependency Injection
- Container with `bind()`, `singleton()`, and `instance()` registration
- Automatic constructor dependency resolution
- Integrated with framework core services

### Task Scheduling
- CRON-based background task execution
- Scheduler daemon for development environments
- Task arguments and conditional execution

### Observability
- Health check system with HTTP endpoint (200/503)
- Built-in checks for cache and storage availability
- Extensible via `HealthCheckInterface`
- Structured file-based logging with daily rotation and level filtering

### Additional Capabilities
- Internationalization (i18n) with LTR/RTL support
- Theming system for multiple UI variants
- Programmatic DOM manipulation in PHP
- Templated HTML email delivery with attachments
- CLI scaffolding commands for controllers, middleware, migrations, and more
- Event dispatcher for decoupled application components
- Pluggable cache layer with full-response and HTTP caching (ETag/304)
- Environment variable resolution in configuration (`env:` prefix)
- File upload handling with validation

## Architecture

WebFiori is composed of independent, interface-driven packages. Storage backends for sessions, cache, queues, and authorization are pluggable — implement the interface and swap the default.

Request lifecycle: **Request → Middleware (before) → Route Dispatch → Middleware (after) → Response → Middleware (afterSend)**

## Standard Libraries

| Library | Build | Latest |
|---------|-------|--------|
| [HTTP](https://github.com/WebFiori/http) | <a href="https://github.com/WebFiori/http/actions"><img src="https://github.com/WebFiori/http/actions/workflows/php84.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/http/releases"><img src="https://img.shields.io/github/release/WebFiori/http.svg" /></a> |
| [Database](https://github.com/WebFiori/database) | <a href="https://github.com/WebFiori/database/actions"><img src="https://github.com/WebFiori/database/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/database/releases"><img src="https://img.shields.io/github/release/WebFiori/database.svg" /></a> |
| [CLI](https://github.com/WebFiori/cli) | <a href="https://github.com/WebFiori/cli/actions"><img src="https://github.com/WebFiori/cli/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/cli/releases"><img src="https://img.shields.io/github/release/WebFiori/cli.svg" /></a> |
| [Cache](https://github.com/WebFiori/cache) | <a href="https://github.com/WebFiori/cache/actions"><img src="https://github.com/WebFiori/cache/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/cache/releases"><img src="https://img.shields.io/github/release/WebFiori/cache.svg" /></a> |
| [UI](https://github.com/WebFiori/ui) | <a href="https://github.com/WebFiori/ui/actions"><img src="https://github.com/WebFiori/ui/actions/workflows/php84.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/ui/releases"><img src="https://img.shields.io/github/release/WebFiori/ui.svg" /></a> |
| [Mailer](https://github.com/WebFiori/mail) | <a href="https://github.com/WebFiori/mail/actions"><img src="https://github.com/WebFiori/mail/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/mail/releases"><img src="https://img.shields.io/github/release/WebFiori/mail.svg" /></a> |
| [File](https://github.com/WebFiori/file) | <a href="https://github.com/WebFiori/file/actions"><img src="https://github.com/WebFiori/file/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/file/releases"><img src="https://img.shields.io/github/release/WebFiori/file.svg" /></a> |
| [Json](https://github.com/WebFiori/json) | <a href="https://github.com/WebFiori/json/actions"><img src="https://github.com/WebFiori/json/actions/workflows/php84.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/json/releases"><img src="https://img.shields.io/github/release/WebFiori/json.svg" /></a> |
| [Collections](https://github.com/WebFiori/collections) | <a href="https://github.com/WebFiori/collections/actions"><img src="https://github.com/WebFiori/collections/actions/workflows/php84.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/collections/releases"><img src="https://img.shields.io/github/release/WebFiori/collections.svg" /></a> |
| [Error Handler](https://github.com/WebFiori/err) | <a href="https://github.com/WebFiori/err/actions"><img src="https://github.com/WebFiori/err/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/err/releases"><img src="https://img.shields.io/github/release/WebFiori/err.svg" /></a> |
| [Container](https://github.com/WebFiori/container) | <a href="https://github.com/WebFiori/container/actions"><img src="https://github.com/WebFiori/container/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/container/releases"><img src="https://img.shields.io/github/release/WebFiori/container.svg" /></a> |
| [Queue](https://github.com/WebFiori/queue) | <a href="https://github.com/WebFiori/queue/actions"><img src="https://github.com/WebFiori/queue/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/queue/releases"><img src="https://img.shields.io/github/release/WebFiori/queue.svg" /></a> |
| [Event](https://github.com/WebFiori/event) | <a href="https://github.com/WebFiori/event/actions"><img src="https://github.com/WebFiori/event/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/event/releases"><img src="https://img.shields.io/github/release/WebFiori/event.svg" /></a> |
| [Log](https://github.com/WebFiori/log) | <a href="https://github.com/WebFiori/log/actions"><img src="https://github.com/WebFiori/log/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/log/releases"><img src="https://img.shields.io/github/release/WebFiori/log.svg" /></a> |

## Documentation

- [Getting Started Guide](https://webfiori.com/learn)
- [API Reference](https://webfiori.com/docs)

## Contributing

See [CONTRIBUTING.md](https://webfiori.com/contribute) for guidelines.

## Security

To report security vulnerabilities, please email [ibrahim@webfiori.com](mailto:ibrahim@webfiori.com). See [SECURITY.md](SECURITY.md) for supported versions.

## License

MIT — see [LICENSE](LICENSE) for details.
