# Repository Guidelines

## Project Structure & Module Organization
Multshop follows the ThinkPHP 6 multi-app layout rooted in `app/`. Modules such as `admin`, `api`, `shop`, `shopapi`, and `kefuapi` expose `controller/`, `logic/`, `validate/`, and `view/` directories—keep controllers slim and push business rules into the paired logic classes. Shared services belong in `app/common`, while custom SDK wrappers should live under `extend/`. HTTP entry points and static assets stay inside `public/` (`public/index.php`, `public/static/`, `public/uploads/`). Global configuration lives in `config/*.php` with overrides coming from `.env`, and generated caches sit in `runtime/`.

## Build, Test, and Development Commands
- `composer install` — install dependencies before first run.
- `php think run -H 0.0.0.0 -p 8000` — launch the built-in dev server for smoke tests.
- `php think optimize:config && php think optimize:route` — cache configuration and routes before packaging or deployment.

## Coding Style & Naming Conventions
Follow PSR-12 (four-space indents, braces on new lines, strict types when practical). Controllers use PascalCase (`Admin.php`), method names stay camelCase, and templates or aliases remain snake_case. Validate data with each module’s `validate` classes before invoking logic and return payloads through `app\common\server\JsonServer` for consistent envelopes. Share helpers via `app/common` or `extend/`, and read configuration with `config()`/`env()` instead of hard-coded literals.

## Testing Guidelines
No automated suite ships yet; create `tests/` mirroring the module you touch, name files `*Test.php`, and rely on PHPUnit with ThinkPHP’s testing traits. Require `phpunit/phpunit` (or `topthink/think-testing`) as a dev dependency the first time you add a test. Run suites with `php think test` or `./vendor/bin/phpunit`. Cover success and failure paths for orders, payments, and uploads, and persist reusable payloads under `tests/fixtures` for deterministic assertions.

## Commit & Pull Request Guidelines
Commits should stay focused and follow Conventional Commit prefixes, mirroring the existing `feat: init` history. Summaries are imperative (“fix admin auth redirect”), and bodies explain rationale plus follow-up steps. Pull requests must outline the impacted module, list config or schema prerequisites, reference related issues, and include screenshots when touching assets under `public/pc` or `public/mobile`. Ensure local builds/tests pass and request at least one maintainer review before merging.

## Security & Configuration Tips
Never commit `.env`, `runtime/`, or anything under `public/uploads/`. Keep keys for WeChat, Aliyun, Tencent, and payment gateways inside `.env`, expose them through `config/*.php`, and read them via `config()` helpers. When registering new routes, validate signatures in `app/api` controllers before delegating to logic layers and log webhook payloads to non-public storage for traceability.
