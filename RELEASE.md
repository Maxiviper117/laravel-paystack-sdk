# Release Guide

This repository uses [Release Please](https://github.com/googleapis/release-please) to manage changelog updates, version bumps, tags, and GitHub releases.

## Files

- [`.github/workflows/release-please.yml`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/.github/workflows/release-please.yml): runs Release Please on pushes to `main`
- [`release-please-config.json`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/release-please-config.json): release strategy and package settings
- [`.release-please-manifest.json`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/.release-please-manifest.json): current tracked version for the repository root package
- [`CHANGELOG.md`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/CHANGELOG.md): updated by Release Please

## Current strategy

- Release type: `php`
- Package path: repository root (`.`)
- Starting manifest version: `0.1.0`
- Tags include `v`, for example `v0.2.0`
- Changelog path: `CHANGELOG.md`

## Pre-1.0 behavior

This package is intentionally configured to stay on `0.x` until maintainers explicitly decide it is ready for `1.0.0`.

The key setting is:

- `bump-minor-pre-major: true`

That means:

- `fix:` commits bump patch versions, for example `0.1.0` -> `0.1.1`
- `feat:` commits bump minor versions, for example `0.1.1` -> `0.2.0`
- breaking changes before `1.0.0` also bump the minor version instead of creating `1.0.0`

This repository does not use prerelease tags like `0.2.0-beta.1`. "Beta" here means "remain pre-1.0", not "publish prerelease suffixes".

## Maintainer workflow

1. Merge conventional commits into `main`.
2. Release Please opens or updates a release PR.
3. Review the generated version and changelog.
4. Merge the release PR when ready.
5. Release Please creates the tag and GitHub release.

## Commit message guidance

Release Please relies on conventional commits. Use these prefixes consistently:

- `fix:` for bug fixes and compatible corrections
- `feat:` for new user-facing features
- `docs:` for documentation-only changes
- `chore:` for maintenance work with no release impact unless the change should be called out

For breaking changes, use either:

- `feat!:` / `fix!:` syntax
- or a `BREAKING CHANGE:` footer

Before `1.0.0`, those breaking changes still produce a minor release because of `bump-minor-pre-major: true`.

## How Release Please updates versions

Release Please updates the root package version metadata through:

- [`composer.json`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/composer.json)
- `composer.lock` when present and updated in the release PR
- [`CHANGELOG.md`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/CHANGELOG.md)
- [`.release-please-manifest.json`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/.release-please-manifest.json)

Do not manually bump the package version in normal PRs.

## Releasing 1.0.0 later

Release Please is not configured to automatically promote this package to `1.0.0`.

When the package is ready for stable release, make that a deliberate maintainer action. Typical options are:

- use a `Release-As: 1.0.0` footer on the relevant merge commit
- or update the manifest/config specifically for the `1.0.0` release

Do not remove the pre-1.0 safeguards casually. That change should happen only when the public API and maintenance expectations are stable enough for a `1.x` contract.

## Operational notes

- The workflow uses `secrets.GITHUB_TOKEN`.
- The workflow runs only on pushes to `main`.
- The old manual changelog workflow was removed to avoid conflicting release automation.
- If release behavior changes, update this file and [`AGENTS.md`](/d:/David/Development/Github/ALL/laravel-paystack-sdk/AGENTS.md) in the same change.
