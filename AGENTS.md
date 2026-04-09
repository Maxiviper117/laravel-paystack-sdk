# AGENTS.md

This file provides repository-scoped instructions for Codex and other agents. Its scope is the entire repository tree rooted here.

## Repository purpose

- This repository contains a Laravel package: `maxiviper117/laravel-paystack-sdk`.
- The package provides a Paystack SDK built on Saloon with an Actions-first public API.
- Current implemented coverage includes transactions, customers, plans, subscriptions, an optional Billable persistence layer, and webhook intake/processing built on `spatie/laravel-webhook-client`.

## Source layout

- `src/Actions`: high-level action classes intended for consumer-facing package usage.
- `src/Concerns`: opt-in Laravel traits such as the optional Billable Eloquent integration.
- `src/Integrations`: Saloon connector and request classes for Paystack HTTP integration.
- `src/Data`: DTOs and response-shaping classes.
- `src/Models`: package Eloquent models for webhook calls and optional billing-layer persistence.
- `src/Support`: small helper utilities used across the package.
- `database/migrations`: optional package-owned billing-layer migrations that consumers may publish into their apps.
- `docs`: consumer-facing VitePress documentation content.
- `docs/examples`: cookbook-style VitePress examples for realistic Laravel integration flows.
- `.vitepress`: root VitePress site configuration and static docs assets.
- `.github/skills/vitepress`: repository-local skill for inspecting, validating, and operating the VitePress docs setup.
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
- Customer actions currently cover create, fetch, update, validate, set risk action, and list operations.
- Plan actions currently cover create, update, fetch, and list operations, and `UpdatePlanInputData` includes the documented `update_existing_subscriptions` flag.
- Subscription actions currently cover create, fetch, list, enable, disable, generate update link, and send update link operations.

## Working rules

- Keep the package Laravel-native and Actions-first.
- Treat action classes as injectable services; do not add static self-resolving helpers that call `app()` internally.
- Use DTO-first action contracts. Public action, manager, and facade APIs should accept typed input DTOs and return typed action-specific response DTOs.
- Keep the optional Billable layer as a convenience wrapper over the existing actions and DTOs; do not let it become a second, conflicting SDK surface.
- Prefer small, typed DTOs over passing raw arrays through public APIs.
- For richer resource domains, compose shared resource DTOs inside action-specific response DTOs instead of flattening everything into duplicated response shapes.
- Keep request classes thin and API-focused; keep business rules in actions or support classes.
- Treat security as a first-class concern in every change. Be alert to potentially introducing security bugs, especially around payment flows, webhook handling, secrets, signatures, request validation, authorization boundaries, and any code that processes sensitive financial or customer data.
- Preserve compatibility with Laravel `11.x` and `12.x` and PHP `8.3` and `8.4`.
- CI coverage is Linux-only. Do not add or restore Windows test jobs unless the package scope changes materially.
- Do not reintroduce Spatie skeleton placeholders or `Skeleton*` classes/files.
- Package convenience access belongs in `PaystackManager` and the facade. Action classes may expose `execute(...)` and `__invoke(...)`.
- If the optional Billable layer changes, keep its trait methods delegating to the existing manager/actions rather than bypassing package transport, validation, or response mapping.
- Input DTOs live under `src/Data/Input` and action response DTOs live under `src/Data/Output`.
- Webhook handling is local package logic, not an outbound Saloon request. Keep signature validation, stored webhook handling, and payload parsing outside the HTTP connector layer.

## Required verification

- Run `composer analyse` after code changes that affect PHP code.
- Run `composer test` after behavioral changes, request/response changes, config changes, or test changes.
- Tests must include applicable security coverage for the code under change. For this payment package SDK, add or update tests whenever a change could affect security-sensitive behavior such as signature verification, secret handling, input validation, authorization boundaries, request tampering resistance, or unsafe data exposure.
- `composer test-parallel` is available for full-suite parallel Pest runs. Keep `composer test` as the default serial verification command unless the task specifically calls for parallel execution behavior.
- If Rector-related work is requested, use `composer refactor-dry` first and only apply `composer refactor` when the task calls for code mutation.

## Tooling notes

- PHPStan is configured at level 10 in `phpstan.neon.dist`.
- Root documentation tooling uses `pnpm` with VitePress from the repository root and is separate from `workbench`.
- The GitHub Pages docs deployment workflow lives at `.github/workflows/deploy-docs.yml` and should publish the root VitePress build output.
- `composer analyse` runs through `tools/phpstan-analyse.php`, a thin wrapper around PHPStan that suppresses a known Windows-only `Cannot create a file when that file already exists.` noise line without changing analysis behavior.
- CI should invoke PHPStan through `composer analyse` so the repo's wrapper and memory settings are preserved instead of calling `vendor/bin/phpstan` directly.
- Rector is configured in `rector.php` with conservative prepared sets for this package and is pinned to the minimum supported PHP version (`8.3`) so it does not introduce syntax that would break the package's support matrix.
- The Rector config explicitly skips `ClassPropertyAssignToConstructorPromotionRector` because constructor promotion can rename parameters and break named-argument call sites in package code.
- Release Please is configured with a manifest-based setup for this repository.
- This repository is set up to merge pull requests to `main` via squash and merge only.
- While the package is pre-1.0, Release Please uses `bump-minor-pre-major: true`, so `feat` and breaking changes bump the minor version and fixes bump the patch version.
- Because this repository uses Release Please to generate releases from conventional-commit history on `main`, when users ask for commit-related work, require PR titles to follow Conventional Commits, for example `feat: add subscription cancel action` or `fix: handle missing webhook signature`.
- Do not manually bump package versions in PRs; maintainers should let Release Please manage pre-1.0 releases and explicitly choose when to promote the package to `1.0.0`.
- Maintainer-facing release process notes live in `RELEASE.md`; keep that file aligned with the actual workflow and config.
- Pest is the test runner.
- Pest parallel mode is supported in this repo via `composer test-parallel` and currently runs cleanly with the existing suite.

## Documentation maintenance

- Any repository change that affects package architecture, public APIs, supported tooling, commands, workflows, or constraints must keep this `AGENTS.md` file up to date in the same change.
- Any repository change that affects supported Paystack endpoints, SDK features, action/input/output DTO coverage, or live-test coverage must also update `SDK_SUPPORT.md` in the same change.
- Any repository change that affects the optional billing-layer trait, models, or package migrations must update the relevant docs, workbench flow, and this file in the same change.
- Keep the root VitePress docs in `docs/` aligned with the current public package API, configuration, and supported feature set.
- Keep VitePress config, navigation, GitHub Pages deployment details, and docs output path aligned with the actual root docs structure and build output.
- Keep consumer docs focused on the package itself; do not let VitePress docs drift into workbench-specific guidance unless the task explicitly targets workbench documentation.
- If package APIs, DTOs, supported Paystack resources, config variables, or recommended integration patterns change, update the relevant VitePress docs pages in the same change.
- Keep `.github/skills/vitepress/SKILL.md` aligned with the actual docs layout, config file path, scripts, Pages workflow, and verified build output path.
- If a change makes any instruction here inaccurate, update `AGENTS.md` before finishing.
- Keep instructions concrete and repository-specific; do not let this file drift into generic guidance.

## Preferred commands

- Install dependencies: `composer install`
- Install docs dependencies: `pnpm install --frozen-lockfile`
- Run tests: `composer test`
- Run tests in parallel: `composer test-parallel`
- Run static analysis: `composer analyse`
- Check Rector changes: `composer refactor-dry`
- Apply Rector changes: `composer refactor`
- Format code: `composer format`
- Run docs dev server: `pnpm run docs:dev`
- Build docs: `pnpm run docs:build`
- Workbench install: `cd workbench && composer install`
- Workbench dev server: `cd workbench && php artisan serve`
