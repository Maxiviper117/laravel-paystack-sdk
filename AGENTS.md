# AGENTS.md

This file provides repository-scoped instructions for Codex and other agents. Its scope is the entire repository tree rooted here.

## Repository purpose

- This repository contains a Laravel package: `maxiviper117/laravel-paystack-sdk`.
- The package provides a Paystack SDK built on Saloon with an Actions-first public API.
- Current MVP coverage is focused on Transactions and Customers.

## Source layout

- `src/Actions`: high-level action classes intended for consumer-facing package usage.
- `src/Integrations`: Saloon connector and request classes for Paystack HTTP integration.
- `src/Data`: DTOs and response-shaping classes.
- `src/Support`: small helper utilities used across the package.
- `tests/Feature`: package behavior and request/response flow tests.
- `tests/Unit`: small focused utility and connector tests.
- `config/paystack.php`: published package configuration.
- `workbench`: minimal Laravel app used for live package testing against Paystack test mode.
- The `workbench` app uses `pnpm` for frontend package management in Composer scripts.
- `workbench` is committed for development and live testing, but it is excluded from package distribution archives via `.gitattributes` `export-ignore`.
- `workbench` is a standalone Laravel app with its own Composer configuration; do not autoload it from the root package `composer.json`.
- `workbench/routes/web.php` and `workbench/README.md` should reflect the current recommended package integration style when live-test examples change.
- Keep the workbench app up to date with the current package state. If package APIs, DTOs, response shapes, config, or recommended integration patterns change, update the relevant workbench routes/docs in the same change.
- `SDK_SUPPORT.md` is the maintainer-facing support matrix for Paystack endpoints and SDK capabilities; keep it aligned with the actual implemented package surface.

## Working rules

- Keep the package Laravel-native and Actions-first.
- Treat action classes as injectable services; do not add static self-resolving helpers that call `app()` internally.
- Use DTO-first action contracts. Public action, manager, and facade APIs should accept typed input DTOs and return typed action-specific response DTOs.
- Prefer small, typed DTOs over passing raw arrays through public APIs.
- For richer resource domains, compose shared resource DTOs inside action-specific response DTOs instead of flattening everything into duplicated response shapes.
- Keep request classes thin and API-focused; keep business rules in actions or support classes.
- Preserve compatibility with Laravel `11.x` and `12.x` and PHP `8.3` and `8.4`.
- Do not reintroduce Spatie skeleton placeholders or `Skeleton*` classes/files.
- Package convenience access belongs in `PaystackManager` and the facade. Action classes may expose `execute(...)` and `__invoke(...)`.
- Input DTOs live under `src/Data/Input` and action response DTOs live under `src/Data/Output`.
- Webhook verification is local package logic, not an outbound Saloon request. Keep signature verification and payload parsing outside the HTTP connector layer.

## Required verification

- Run `composer analyse` after code changes that affect PHP code.
- Run `composer test` after behavioral changes, request/response changes, config changes, or test changes.
- If Rector-related work is requested, use `composer refactor-dry` first and only apply `composer refactor` when the task calls for code mutation.

## Tooling notes

- PHPStan is configured at level 10 in `phpstan.neon.dist`.
- Rector is configured in `rector.php` with conservative prepared sets for this package and is pinned to the minimum supported PHP version (`8.3`) so it does not introduce syntax that would break the package's support matrix.
- The Rector config explicitly skips `ClassPropertyAssignToConstructorPromotionRector` because constructor promotion can rename parameters and break named-argument call sites in package code.
- Release Please is configured with a manifest-based setup for this repository.
- While the package is pre-1.0, Release Please uses `bump-minor-pre-major: true`, so `feat` and breaking changes bump the minor version and fixes bump the patch version.
- Do not manually bump package versions in PRs; maintainers should let Release Please manage pre-1.0 releases and explicitly choose when to promote the package to `1.0.0`.
- Maintainer-facing release process notes live in `RELEASE.md`; keep that file aligned with the actual workflow and config.
- Pest is the test runner.

## Documentation maintenance

- Any repository change that affects package architecture, public APIs, supported tooling, commands, workflows, or constraints must keep this `AGENTS.md` file up to date in the same change.
- Any repository change that affects supported Paystack endpoints, SDK features, action/input/output DTO coverage, or live-test coverage must also update `SDK_SUPPORT.md` in the same change.
- If a change makes any instruction here inaccurate, update `AGENTS.md` before finishing.
- Keep instructions concrete and repository-specific; do not let this file drift into generic guidance.

## Preferred commands

- Install dependencies: `composer install`
- Run tests: `composer test`
- Run static analysis: `composer analyse`
- Check Rector changes: `composer refactor-dry`
- Apply Rector changes: `composer refactor`
- Format code: `composer format`
- Workbench install: `cd workbench && composer install`
- Workbench dev server: `cd workbench && php artisan serve`
