# Webapp License Header Enforcement

## Summary
Extend the existing `apache/skywalking-eyes` license-header check to cover the Angular frontend (`.ts`, `.html`, `.scss` under `webapp/src/`), and backfill headers into every existing in-scope file.

## Context
**Current behavior**: The `Check License Headers` step in `.github/workflows/php.yml` enforces a GPL-3.0-or-later header on PHP files under `api/src/`, `api/tests/`, and `api/migrations/` via `.github/license-eye-config.yaml`. Frontend files have no header and no enforcement.

**New behavior**: The same workflow step also enforces a GPL-3.0-or-later header on webapp source files. All existing in-scope files start with the header. Future PRs that add new `.ts`/`.html`/`.scss` files without a header fail CI.

## Acceptance Criteria
- [ ] Every in-scope webapp file starts with the GPL-3.0-or-later header block, using the correct comment syntax for its file type (`/* */` for `.ts`/`.scss`, `<!-- -->` for `.html`)
- [ ] The license-header CI step fails on a PR that adds a new in-scope webapp file with no header
- [ ] The license-header CI step passes on `main` after the backfill
- [ ] Excluded files (listed in Scope below) remain unchanged and are not checked
- [ ] `ng build` and `ng run PoppySeedPetsApp:storybook` still succeed after the backfill (headers do not break the Angular compiler or template parser)

## Scope
Configuration + generated backfill only — no behavior changes.

**In scope (headers added + CI-checked):**
- `webapp/src/**/*.ts` (including `*.spec.ts`)
- `webapp/src/**/*.html`
- `webapp/src/**/*.scss` (including the root-level `webapp/src/*.scss` files like `reset.scss`, `layout.scss`, etc.)

**Excluded (no header, not checked):**
- `webapp/src/environments/environment*.ts` — deployment-specific, not a source asset
- `webapp/src/main.ts`, `webapp/src/polyfills.ts` — Angular-generated bootstrap files
- `webapp/src/*.html` site-verification files (`google2172386c34c00d9e.html`, `nortonsw_*.html`, `index.html`)
- Anything under `webapp/node_modules/` or `webapp/dist/`

## Implementation

### 1. Extend the skywalking-eyes config with a webapp header block
**Why**: skywalking-eyes supports a YAML list of header configs, each with its own `paths` / `paths-ignore` and `content`. Adding a second entry lets us use a webapp-specific `software-name` ("Poppy Seed Pets Webapp") without disturbing the PHP config.

**File**: `.github/license-eye-config.yaml`

Convert the top-level `header:` mapping into a list of two entries. The first entry is the current PHP config, unchanged. The second entry mirrors its structure but:
- `software-name: Poppy Seed Pets Webapp`
- `content:` — same GPL-3.0-or-later wording as the PHP block, with "Poppy Seed Pets API" → "Poppy Seed Pets Webapp" in all three sentences
- `paths:` — `webapp/src/**/*.ts`, `webapp/src/**/*.html`, `webapp/src/**/*.scss`
- `paths-ignore:` — `webapp/src/environments/environment*.ts`, `webapp/src/main.ts`, `webapp/src/polyfills.ts`, `webapp/src/*.html` (this excludes the three static verification/index HTML files at the webapp src root; nested `.html` files under `webapp/src/app/**` are still covered)
- `comment: on-failure` and `license-locations: [COPYING]` to match the PHP block (adjust path if skywalking-eyes resolves it from repo root vs config dir; the PHP block uses `api/COPYING`, so the webapp block should point to the top-level `COPYING` at repo root)

Keep both blocks in the same file so the single `Check License Headers` workflow step covers both.

### 2. Verify the workflow step covers both blocks
**Why**: `apache/skywalking-eyes/header@main` reads the full config file, so a list with two entries is processed in one invocation — no workflow change needed. Confirm by reading `.github/workflows/php.yml` lines 55-58 and checking that no `path`-scoped filter on the action or the step would cause the webapp block to be skipped. If the step has any conditional that limits it to PHP-only runs, remove that condition.

### 3. Backfill headers into all in-scope webapp files
**Why**: Without this, enabling the check turns CI red on `main` immediately. skywalking-eyes ships a `fix` mode that inserts the correct header at the top of each file based on the same config.

Run the fixer locally against the webapp block. Two viable approaches — pick whichever works on Windows:
- **Docker**: `docker run --rm -v "$PWD:/github/workspace" apache/skywalking-eyes header fix`
- **Go install**: `go install github.com/apache/skywalking-eyes/cmd/license-eye@latest`, then `license-eye header fix`

After running, verify:
- Every `.ts`/`.scss` in scope begins with a `/* ... */` block containing the webapp header
- Every `.html` in scope begins with `<!-- ... -->`
- `.ts` files: the header comes *before* the first `import` / `declare` / code — skywalking-eyes handles this correctly by default
- `.html` component templates still render (the HTML comment at the top is valid Angular template syntax)
- None of the excluded files were modified

Commit the backfill in the same PR as the config change so CI stays green through the merge.

### 4. Spot-check the output
**Why**: skywalking-eyes is strict about exact header text, so a single character off in the config will make every backfilled file fail its own check. After the backfill, re-run `license-eye header check` (or push and watch CI) and confirm it reports zero violations. If any file fails, it's almost always because the configured `content:` doesn't match what `fix` inserted — usually a trailing-newline or wording diff.

## Test Plan
- [ ] After the backfill, run `license-eye header check` locally — expect zero violations
- [ ] Open a branch that adds a new empty `.ts` file under `webapp/src/app/` with no header — push, confirm the `Check License Headers` step fails with that file listed
- [ ] Add the header to that file — confirm the step passes
- [ ] Add a new file under `webapp/src/environments/environment.staging.ts` with no header — confirm the step still passes (excluded)
- [ ] Add a new file under `webapp/node_modules/fake.ts` — confirm the step still passes (excluded)
- [ ] Run `ng build` (from `webapp/`) — confirm the build succeeds with headers present
- [ ] Run `ng run PoppySeedPetsApp:storybook` — confirm Storybook still loads a component whose template now has a leading HTML comment
- [ ] Open the running app in a browser and navigate a couple of pages — confirm templates render (leading `<!-- -->` in component templates does not break Angular parsing)
- [ ] `git diff --stat` on the backfill commit — sanity-check the file count roughly matches expectations (hundreds of files, but no unexpected paths like `dist/` or `node_modules/`)

## Learnings

### Architectural decisions
- **Custom Node backfill script instead of `license-eye header fix`**: Docker and Go were both unavailable on the dev machine, so the ticket's suggested tooling was a non-starter. Wrote `webapp/scripts/backfill-license-headers.mjs` that mimics the skywalking-eyes default comment styles — `/* ... */` with ` * ` line prefix for `.ts`/`.scss`, and `<!-- ... -->` with `  ~ ` line prefix for `.html`. The script is idempotent (checks for the signature line "This file is part of the Poppy Seed Pets Webapp." before writing). Validation was deferred to CI.
- **Excluded `webapp/src/test.ts`** in addition to the explicitly-listed `main.ts` / `polyfills.ts` / `environment*.ts`. It's the same category of Angular-generated bootstrap file (loads the Karma test env), not authored product code. Confirmed with user before implementation.
- **Bundled `.gitattributes` `eol=lf` change into this PR**: The backfill surfaced a CRLF-vs-LF warning, which led to a quick discussion: the old `* text=auto` convention (LF in repo, OS-native in working tree) is legacy Windows tooling compensation that modern editors don't need. Switched to `* text=auto eol=lf` so the working tree matches the repo on every OS. Orthogonal cleanup but coherent with the noise the backfill would have created in `git status`.

### Problems / gotchas
- **No local validation possible without the skywalking-eyes binary.** `license-eye header check` is the only authoritative check — if the configured `content:` wording drifts by even a trailing newline from what the fixer inserted, every backfilled file fails. Mitigation: the header text is driven by a single source (the `contentLines` array in the backfill script + the `content:` field in the config YAML), and CI will catch any mismatch on first push. If CI complains, the fix is almost always to adjust the script's output format to match what skywalking-eyes expects, not the config.
- **`webapp/src/*.html` glob = depth-1 only.** Standard glob semantics; the script replicates this by checking `parts.length === 2` before excluding. Nested `.html` under `webapp/src/app/**` remain in scope as intended.

### Tidbits
- **File count**: 1193 files backfilled, 202 skipped (excluded by extension or rule). Useful baseline if future audits need to sanity-check coverage.
- **skywalking-eyes config supports a list of header blocks** — converting `header:` from a mapping to a list lets one `Check License Headers` CI step enforce differently-worded headers across `api/` (PHP → "Poppy Seed Pets API") and `webapp/` (TS/HTML/SCSS → "Poppy Seed Pets Webapp") without touching the workflow YAML.
- **COPYING resolution**: The PHP block uses `api/COPYING`; the webapp block points to the top-level `COPYING` at repo root. Both files exist (verified during implementation).

### Related areas
- **`webapp/scripts/` is new.** The backfill script is the first entry in it; kept it alongside the app for discoverability rather than burying in `webapp/src/`. Future one-off maintenance scripts can live here.
- **`.gitattributes` changed repo-wide behavior.** Currently-checked-out CRLF files on Windows machines won't flip to LF until touched (or until someone runs `git add --renormalize .`). Non-urgent — gradual migration is fine.

### Rejected alternatives
- **Install Go and run `license-eye header fix`**: user explicitly declined installing either Docker or Go; CI will be the validation surface.
- **Keep `* text=auto` alone in `.gitattributes`**: would have left a CRLF-vs-LF warning attached to every future edit on Windows, causing noisy working-tree state. Not worth preserving the legacy behavior.
