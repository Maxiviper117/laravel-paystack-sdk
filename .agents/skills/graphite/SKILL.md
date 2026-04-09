---
name: graphite
description: Work with Graphite (gt) for stacked PRs - creating, navigating, and managing PR stacks.
---

# Graphite Skill

Use Graphite (`gt`) for creating stacked branches locally, then submitting them as linked pull requests.

Graphite CLI v1 uses flat command names. Legacy aliases may still work, but prefer the current commands in this guide.

## Quick Reference

| I want to... | Command |
| --- | --- |
| Create a stacked branch | `gt create [name]` |
| Create with staged-all / message | `gt create --all --message "message"` |
| Amend or create a commit | `gt modify` |
| Add a new commit | `gt modify --commit` |
| Amend staged changes into a downstack branch | `gt modify --into` |
| Absorb staged hunks into existing commits | `gt absorb` |
| Split a branch | `gt split` |
| Squash a branch | `gt squash` |
| Restack the current stack | `gt restack` |
| Rebase onto another branch | `gt move --onto <branch>` |
| Reorder branches in a stack | `gt reorder` |
| Submit the current stack | `gt submit` |
| Submit descendants too | `gt submit --stack` |
| Sync local stack with remote | `gt sync` |
| Checkout a branch | `gt checkout [branch]` |
| Navigate up the stack | `gt up [steps]` |
| Navigate down the stack | `gt down [steps]` |
| Jump to the top | `gt top` |
| Jump to the bottom | `gt bottom` |
| Inspect stack structure | `gt log short` |
| Open the current PR | `gt pr` |
| Open the current stack page | `gt pr --stack` |
| Track a branch | `gt track [branch] -p <parent>` |
| Stop tracking a branch | `gt untrack [branch]` |
| Delete a branch but keep files | `gt pop` |
| Delete a branch locally | `gt delete [name]` |
| Fold a branch into its parent | `gt fold` |
| Undo the most recent mutation | `gt undo` |

Common aliases:

- `gt ls` = `gt log short`
- `gt ll` = `gt log long`
- `gt sp` = `gt split`
- `gt ss` = `gt submit --stack`

---

## Core Workflow

1. Make your code changes.
2. Stage the relevant files with `git add` or `gt add`.
3. Create the stacked branch with `gt create [name]`.
4. Use `gt modify`, `gt absorb`, `gt split`, or `gt squash` to shape the branch.
5. Restack with `gt restack` if parents or ancestry changed.
6. Use `gt sync` when you need to pull trunk updates and clean up merged branches.
7. Submit with `gt submit`, or `gt submit --stack` if you want descendants included.

---

## Branch Creation And Editing

### Create A Branch

```bash
git add <files>
gt create branch-name
```

If no branch name is provided, Graphite can generate one from the commit message.

### Modify The Current Branch

```bash
git add <files>
gt modify -m "updated commit message"
```

Use `-c, --commit` to create a new commit instead of amending the current one, `-a, --all` to stage everything, and `--into` to amend staged changes into a downstack branch.

### Absorb Staged Changes

Use `gt absorb` when staged hunks should be moved into earlier commits in the current stack.

### Split Or Squash

- `gt split` breaks one branch into multiple branches by commit, hunk, or file. Use `--by-commit`, `--by-hunk`, or `--by-file <pathspec>`.
- `gt squash` combines the current branch into a single commit and restacks descendants.

---

## Stack Navigation

```bash
gt up
gt down
gt top
gt bottom
gt checkout
gt parent
gt children
gt trunk
```

Use `gt log short` or `gt ls` to inspect the stack layout, and `gt log long` or `gt ll` for the commit graph.

---

## Tracking And Reparenting

If a branch is untracked, start tracking it with `gt track [branch] -p <parent>`.

```bash
gt track -p main
gt restack
```

Use `gt untrack [branch]` to stop tracking a branch, and `gt unlink [branch]` to remove the PR association without deleting the branch. If Graphite metadata is corrupted, `gt track [branch]` can re-establish parent relationships.

For branch moves:

```bash
gt move --onto main
```

Use `gt reorder` when you want to rearrange branches between trunk and the current branch.

---

## Submission

### Submit The Stack

```bash
gt submit
```

Use `gt submit --stack` when you want to submit descendants of the current branch too.

Useful submit flags include:

- `--ai`
- `--always`
- `--branch`
- `--draft`
- `--edit`
- `--publish`
- `--update-only`
- `--restack`
- `--cli`
- `--web`
- `--no-edit`
- `--no-edit-title`
- `--no-edit-description`

### Open PR Pages

```bash
gt pr
gt pr --stack
```

`gt pr` opens the current branch PR. `gt pr --stack` opens the stack page.

---

## Deleting And Folding

### Delete A Branch

`gt delete [name]` is local-only. It deletes the branch and restacks any children onto the parent branch. If the branch has an open PR, close it separately or use `--close`. Use `--upstack` or `--downstack` when you want to delete related branches too.

`gt pop` deletes the current branch but keeps the files in the working tree.

### Fold A Branch

`gt fold` folds a branch into its parent, updates descendants, and restacks locally. If the branch has an open PR, close it separately. Use `-k, --keep` if you want to keep the current branch name.

---

## Configuration

- `gt completion` sets up shell completion.
- `gt config` opens Graphite CLI configuration.
- `gt aliases` edits command aliases.
- `gt aliases --legacy` adds the legacy alias preset.
- `gt auth` adds the auth token needed to create and update GitHub PRs.
- `gt docs` opens the built-in docs.
- `gt upgrade` updates the CLI to the latest stable version.

---

## Troubleshooting

| Problem | Fix |
| --- | --- |
| Branch is untracked | Run `gt track [branch] -p <tracked-parent>` first. |
| Stack rooted on the wrong branch | Use `gt track -p main` and then `gt restack`. |
| Need to reorder branches | Use `gt reorder`. |
| Restack hits conflicts | Resolve them with git, then run `gt continue`. |
| Need to abandon a Graphite command blocked by a rebase conflict | Use `gt abort`. |
| Need the current branch's metadata | Use `gt info` or `gt log short`. |

---

## Practical Defaults

- Prefer small, atomic branches that can be submitted independently.
- Keep the stack rooted on the intended trunk before submitting.
- Prefer the current flat `gt` commands over legacy alias spellings when writing examples or docs.
- When you need the exact command list or flags, check `gt docs` or `gt --help --all`.
