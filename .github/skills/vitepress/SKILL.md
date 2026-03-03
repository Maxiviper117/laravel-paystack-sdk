---
name: vitepress
description: Inspect, validate, and operate this repository's VitePress documentation site, including root config, docs pages, and GitHub Pages deployment settings.
---

# VitePress skill

Overview
This skill helps an agent inspect, validate, and operate the VitePress documentation site in this repository.

When to use
- When asked to review docs configuration, verify search or navigation settings, confirm GitHub Pages deployment behavior, list docs pages, or build the site.
- When a package change may have made the consumer docs, Pages workflow, or docs skill inaccurate.

Repository-specific layout
- Root docs content lives in `docs/`.
- Cookbook-style integration examples live under `docs/examples/`.
- Root VitePress config lives in `.vitepress/config.mts`.
- Static docs assets live in `.vitepress/public/`.
- The GitHub Pages workflow lives in `.github/workflows/deploy-docs.yml`.
- The verified VitePress build output path for this repository should be `.vitepress/dist`.

How to operate
1. Read the following files and directories relative to repo root:
   - `package.json`
   - `.vitepress/config.mts`
   - `.vitepress/public/`
   - `docs/`
   - `.github/workflows/deploy-docs.yml`
   - `.gitignore`
   - `AGENTS.md`
2. Extract and report these values:
   - `package.json`: `scripts.docs:dev`, `scripts.docs:build`, `scripts.docs:preview`, `packageManager`, `devDependencies.vitepress`
   - `.vitepress/config.mts`: `srcDir`, `base`, `title`, `description`, `themeConfig.search.provider`, nav items, sidebar items
   - `docs/`: top-level pages, missing expected pages, and whether the content still matches the current package surface
   - workflow: confirm the build step runs `pnpm run docs:build` and the artifact upload path is `.vitepress/dist`
   - `.gitignore`: presence of `/.vitepress/cache` and `/.vitepress/dist`
3. Validate common constraints:
   - `base` must start and end with `/` for GitHub project pages deployments
   - `srcDir` must be `docs` unless the repo is intentionally restructured
   - `docs/index.md` must exist
   - search provider config must match the intended search mode
   - workflow upload path must match the verified build output path for this repo
   - docs should stay package-focused and should not drift into workbench-specific instructions unless explicitly requested
4. Reporting format:
   - Return a JSON object with keys:
     - `srcDir`
     - `base`
     - `title`
     - `description`
     - `search_provider`
     - `docs_pages`
     - `scripts`
     - `package_manager`
     - `vitepress_version`
     - `ci_deploy_path`
     - `issues`
5. If the user asks to verify the docs build:
   - Run `pnpm install --frozen-lockfile` if dependencies are missing or stale
   - Run `pnpm run docs:build`
   - Report build status, warnings, and output path `.vitepress/dist`
6. If the user asks to update docs:
   - Keep `docs/`, `.vitepress/config.mts`, `.github/workflows/deploy-docs.yml`, and `.github/skills/vitepress/SKILL.md` aligned when the docs structure or behavior changes
   - Update `AGENTS.md` in the same change if any docs instruction becomes inaccurate

Examples
- Input: "Review the VitePress setup and tell me if Pages deployment is correct"
  Output: JSON plus a short summary of issues or confirmations.
- Input: "Build the docs and confirm the artifact path"
  Output: build status, warnings, and confirmation that `.vitepress/dist` matches the workflow.

Edge cases
- This repository should behave like `result-flow`: root `.vitepress/config.mts`, `srcDir: 'docs'`, CLI commands run from the repository root, and build output under `.vitepress/dist`.
- If `base` is `/` while the workflow targets GitHub project pages, report it as a deployment bug.
- If the package adds or removes supported Paystack resources, check that docs pages and the support matrix still match the actual SDK surface.

Files referenced
- `package.json`
- `.vitepress/config.mts`
- `.vitepress/public/`
- `docs/`
- `.github/workflows/deploy-docs.yml`
- `.gitignore`
- `AGENTS.md`
- `SDK_SUPPORT.md`

Security and permissions
- Do not expose secrets from environment files or config values.
- Ask before running long-lived dev server commands such as `pnpm run docs:dev`.
