# Asset Validation Pipeline

## Summary
Add a GitHub Actions workflow that validates new/changed SVG assets in PRs: enforces SVG-only format, correct canvas dimensions, and warns on poorly-centered item graphics.

## Context
**Current behavior**: No automated checks on asset files. Incorrectly sized or formatted images can be merged without anyone noticing until they look wrong in-game.

**New behavior**: A new `asset-validation.yml` workflow runs on PRs that touch files under `proprietary-assets/images/items/` or `proprietary-assets/images/pets/`. It performs three checks:
1. **Format check (hard fail)**: All files must be `.svg`
2. **Dimension check (hard fail)**: Items must have a 32x32 canvas; pets must have a 48x48 canvas
3. **Centering check (warning only)**: Item SVGs should have their visual bounding box roughly centered — the distance from any edge to the content shouldn't be more than double the distance from the opposite edge

## Acceptance Criteria
- [ ] PRs adding non-SVG files under `proprietary-assets/images/items/` or `proprietary-assets/images/pets/` fail the workflow
- [ ] PRs adding item SVGs with root dimensions other than 32x32 fail the workflow
- [ ] PRs adding pet SVGs with root dimensions other than 48x48 fail the workflow
- [ ] PRs adding item SVGs with poorly-centered content produce a warning annotation but do not fail the workflow
- [ ] The workflow only checks files that are new or modified in the PR (not all existing assets)
- [ ] The workflow does not run on PRs that don't touch asset files

## Scope
New GitHub Actions workflow file + a Python validation script. No changes to existing code.

## Implementation

### 1. Create the workflow file
Add `.github/workflows/asset-validation.yml`. This is separate from the existing `php.yml` because it covers a different concern (assets, not PHP). Use the same trigger pattern as `php.yml` (push to main + PRs targeting main).

Use `paths` filtering so the workflow only runs when relevant files change:
```yaml
on:
  push:
    branches: ["main"]
    paths: ["proprietary-assets/images/**"]
  pull_request:
    branches: ["main"]
    paths: ["proprietary-assets/images/**"]
```

The job should:
1. Check out the repo with `fetch-depth: 0` (needed to diff against base)
2. Compute the list of changed/added files under `proprietary-assets/images/items/` and `proprietary-assets/images/pets/` using `git diff` against the PR base SHA (same pattern as the "Get changed PHP files" step in `php.yml`)
3. If no relevant files changed, exit early with success
4. Set up Python 3 (ubuntu-latest includes it, but use `actions/setup-python@v5` for reproducibility)
5. Install `cairosvg` and `Pillow` via pip
6. Run the validation script (see step 2), passing the list of changed files

### 2. Create the validation script
Add `.github/scripts/validate-assets.py`. This script receives file paths as command-line arguments and performs three checks on each:

**Check A — SVG format (hard fail)**:
For each changed file under `proprietary-assets/images/items/` or `proprietary-assets/images/pets/`, verify the file extension is `.svg`. If not, print an error and set the exit code to 1.

**Check B — Canvas dimensions (hard fail)**:
Parse each SVG's root `<svg>` element using `xml.etree.ElementTree`. Read the `width` and `height` attributes. Strip any `px` suffix before comparing. Items (files under `items/`) must be `32`x`32`. Pets (files under `pets/`) must be `48`x`48`. If dimensions don't match, print an error and set the exit code to 1.

**Check C — Centering (warning, items only)**:
For item SVGs that passed checks A and B, compute the visual bounding box:
1. Use `cairosvg` to render the SVG to a PNG in memory (at 1x scale so pixels map to SVG units)
2. Use `Pillow` to load the PNG and find the bounding box of non-transparent pixels (`Image.getbbox()`)
3. Compute the margins: left, right (canvas_width - bbox_right), top, bottom (canvas_height - bbox_bottom). Cap each margin to a minimum of 2 (i.e., `max(2, computed_margin)`) — this prevents tiny or zero margins from producing misleading ratios when content intentionally touches or nearly touches an edge.
4. For each axis pair (left/right, top/bottom): if the larger margin is more than 2x the smaller margin, flag it.
5. Emit centering issues as GitHub Actions warning annotations (`::warning file={path}::{message}`) so they show up on the PR's files tab without failing the workflow.

Use `sys.exit(1)` only for hard-fail checks (A and B). Centering warnings should not affect the exit code.

### 3. Wire up the script in the workflow
The workflow step that runs the script should pass the changed file list. A clean approach:

```yaml
- name: Validate assets
  run: |
    if [ -z "$CHANGED_ASSETS" ]; then
      echo "No asset files changed."
      exit 0
    fi
    echo "$CHANGED_ASSETS" | xargs python .github/scripts/validate-assets.py
```

Where `CHANGED_ASSETS` was computed in an earlier step and exported via `$GITHUB_OUTPUT` or environment variable.

## Test Plan
- [ ] Create a test PR adding a `.png` file under `proprietary-assets/images/items/test/` — workflow should fail with a format error
- [ ] Create a test PR adding a valid SVG with wrong dimensions (e.g., 64x64) under `items/` — workflow should fail with a dimension error
- [ ] Create a test PR adding a valid 32x32 item SVG with content shoved into a corner — workflow should pass but show a centering warning annotation
- [ ] Create a test PR adding a valid 32x32 item SVG with roughly centered content — workflow should pass with no warnings
- [ ] Create a test PR adding a valid 48x48 pet SVG — workflow should pass (no centering check for pets)
- [ ] Create a test PR that only touches `.php` files — workflow should not run at all

## Learnings

- **Architectural decisions**: Used a separate workflow file (`asset-validation.yml`) rather than adding steps to the existing `php.yml`. The concerns are independent and this lets each workflow use `paths` filtering to skip entirely when irrelevant files change. The validation logic lives in a standalone Python script rather than inline bash — it's more readable and testable than trying to do XML parsing and image rendering in shell.
- **Problems encountered**: None significant. The SVG files use `xml.etree.ElementTree` namespaced tags (`{http://www.w3.org/2000/svg}svg`), so the dimension check uses `endswith("svg")` rather than comparing the tag directly.
- **Interesting tidbits**: Item SVGs sometimes use `width="32px"` and sometimes `width="32"` (no suffix). The script strips `px` to handle both. Pet SVGs similarly vary between `48` and `48px`. The `cairosvg` + `Pillow` approach for bounding box detection is much more reliable than trying to parse SVG path geometry, since it handles all SVG features (transforms, masks, filters, etc.).
- **Workarounds / limitations**: The centering check caps margins to `min 2` before comparing ratios, to avoid false positives when content intentionally touches or nearly touches an edge. This is a design decision, not a workaround — a 0px margin vs 5px margin shouldn't flag as off-center.
- **Rejected alternatives**: Considered using Inkscape CLI (`--query-all`) to compute bounding boxes, but that would require installing Inkscape in the CI runner (large dependency). The `cairosvg` + `Pillow` approach is lightweight and pip-installable.
