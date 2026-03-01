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

## Working rules

- Keep the package Laravel-native and Actions-first.
- Prefer small, typed DTOs over passing raw arrays through public APIs.
- Keep request classes thin and API-focused; keep business rules in actions or support classes.
- Preserve compatibility with Laravel `11.x` and `12.x` and PHP `8.3` and `8.4`.
- Do not reintroduce Spatie skeleton placeholders or `Skeleton*` classes/files.

## Required verification

- Run `composer analyse` after code changes that affect PHP code.
- Run `composer test` after behavioral changes, request/response changes, config changes, or test changes.
- If Rector-related work is requested, use `composer refactor-dry` first and only apply `composer refactor` when the task calls for code mutation.

## Tooling notes

- PHPStan is configured at level 10 in `phpstan.neon.dist`.
- Rector is configured in `rector.php` with conservative prepared sets for this package.
- Pest is the test runner.

## Documentation maintenance

- Any repository change that affects package architecture, public APIs, supported tooling, commands, workflows, or constraints must keep this `AGENTS.md` file up to date in the same change.
- If a change makes any instruction here inaccurate, update `AGENTS.md` before finishing.
- Keep instructions concrete and repository-specific; do not let this file drift into generic guidance.

## Preferred commands

- Install dependencies: `composer install`
- Run tests: `composer test`
- Run static analysis: `composer analyse`
- Check Rector changes: `composer refactor-dry`
- Apply Rector changes: `composer refactor`
- Format code: `composer format`
