# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

For running status, what's done, what's next, and a deeper context dump, read `PROGRESS.md` first. This file is the technical briefing — conventions, commands, and project layout.

## Keeping PROGRESS.md current

**Always update `PROGRESS.md` before the conversation context gets compacted.** Signs that compaction is approaching: the conversation is very long, you notice a "long conversation" system reminder, or you're wrapping up a major chunk of work. When any of these apply, write a full status update to `PROGRESS.md` covering: what was just completed, what's in progress, what's next, and any gotchas or decisions made. This file is the handoff briefing for the next session — keep it detailed enough that a fresh context can pick up exactly where we left off without losing anything.

## Project Status

Redesign of the CropX website (2026). Current phase: Phase 3 — building page templates from the completed Gutenberg block library.

- Design tokens: complete (`tokens/tokens.css`)
- Static HTML/CSS block library: 20 blocks, complete (`blocks/`)
- WordPress theme scaffold + build pipeline: complete (`wp-theme/cropx/`)
- **All 19 Gutenberg blocks ported and live** on local WP install (`cropx-2026-2`) as of May 17, 2026
- Photo library: 279 photos optimized (WebP + JPG, 4 responsive sizes each) in `assets/images/photos/`
- **Next**: Phase 3 — compose blocks into page templates (Segment Landing Page, Product Page, etc.) + work through Phase 3 polish backlog (see PROGRESS.md)

## Repository Structure

```
blocks/                 20 standalone HTML/CSS block files (the design source-of-truth)
tokens/
  tokens.css            Single source of truth for all design values (CSS custom properties)
  tokens-reference.html Visual browser-based reference for all tokens
assets/                 Photos, icons, logos, decorative SVGs (organized by type)
wp-theme/cropx/         Custom Gutenberg block theme — see "WordPress" section below
PROGRESS.md             Running status doc and handoff briefing
CLAUDE.md               This file
```

## WordPress block development

The custom Gutenberg block theme lives in `wp-theme/cropx/`. Every block is a folder under `src/blocks/` with five files following the Hero block's pattern.

### Build commands

Run from `wp-theme/cropx/`:

```bash
npm install              # first time only
npm run build            # compile JSX/CSS once
npm start                # watch mode — rebuilds on save
npm run format           # auto-format source
npm run lint:js          # lint JS
npm run lint:css         # lint CSS
```

After building, sync to the local WordPress install (path may differ on other machines):

```bash
rsync -av --delete \
  --exclude='node_modules' --exclude='node_modules 2' \
  --exclude='.git' --exclude='.DS_Store' \
  ~/Documents/github/cropx-website-2026/wp-theme/cropx/ \
  "$HOME/Local Sites/cropx-2026-2/app/public/wp-content/themes/cropx/"
```

Then hard-refresh the editor (Cmd+Shift+R).

### Deploying to live staging (EC2, via WP File Manager)

Lauren doesn't have SSH/SFTP to the staging server — all deploys go through the WP File Manager plugin in wp-admin: upload a zip of the theme, extract it over `wp-content/themes/cropx/`. This extraction is **not reliable on large zips**. Confirmed firsthand (Aug 2026, `two-column-video` deep-blue feature): after re-uploading the full ~600-file theme zip several times, the block's folder ended up with three different files each frozen at a different upload's state — some files updated, others silently didn't, with no error shown anywhere. It isn't purely a file-size thing either (a plain 9KB `render.php` failed to update on one attempt while a bigger `index.js` right next to it succeeded) — it seems to be extraction on a ~600-file zip timing out or getting interrupted partway through, non-deterministically.

**When Lauren reports a change "isn't taking" even after a fresh theme upload, suggest a small targeted patch zip instead of another full-theme re-upload** — it worked immediately where repeated full re-uploads hadn't:

1. Zip just the affected block's build folder, e.g. from `wp-theme/cropx/build/blocks/`: `zip -r patch.zip <block-name>/`
2. Have Lauren upload that zip directly into `wp-content/themes/cropx/build/blocks/<block-name>/` in File Manager and extract it there, overwriting just those files in place.

A handful of files extracting cleanly beats 600 files extracting unreliably. If verifying a live deploy, don't just check whether one changed file made it through — check the whole block folder for mixed file generations (mismatched byte sizes / mtimes across files that should all be from the same build), since that mixed state is the actual signature of this bug, not just "it didn't work."

### Five-file pattern per block

```
wp-theme/cropx/src/blocks/<block-name>/
├── block.json     Metadata + attributes
├── index.js       registerBlockType + CSS imports
├── edit.js        Editor UI (RichText, InspectorControls)
├── render.php     Front-end render template
└── style.css      Front-end + editor shared styles
```

The Hero block (`src/blocks/hero/`) is the reference implementation — read all five files before porting a new block.

### Critical conventions and gotchas

These were learned the hard way during the Hero port. Follow them on every new block:

1. **Dynamic blocks only** — `save: () => null`, all front-end markup in `render.php`. We are not using static-saved blocks.

2. **NEVER use `source: "html"` + `selector` on attributes.** That combo tells WordPress to re-parse the value from the block's saved HTML, but dynamic blocks have no saved HTML. Symptom: editor shows typed content, front-end shows the default. Use plain `{"type": "string", "default": "..."}` attributes — they round-trip cleanly via the block comment delimiter.

3. **Global CSS must use `enqueue_block_assets`** (not `enqueue_block_editor_assets`) to reach the editor preview iframe. See `inc/enqueue.php`. The same applies to web fonts.

4. **Import CSS in JS source so webpack bundles it.** `import './style.css';` in `index.js` and `import './editor.css';` in `edit.js`. In `block.json`, reference webpack's OUTPUT names: `"style": "file:./style-index.css"` and `"editorStyle": "file:./index.css"`.

5. **`edit.js` must render the same DOM structure as `render.php`.** For layered hero-style designs (photo + gradient + pattern divs), include all the layer divs in both. Otherwise the editor preview won't match the front-end.

6. **Re-insert old block instances after `block.json` changes.** WordPress doesn't migrate stored attributes when schemas change.

7. **Webpack inlines small assets (~under 10KB) as base64 data URIs** when referenced via relative paths in CSS. Fine for small SVGs — they still render correctly. For anything larger, or anywhere you need a real URL (an `<img>` src, a JS-side asset reference), inject via PHP instead: `CROPX_THEME_URI . 'assets/...'` in `render.php`, or use `wp_localize_script()` to expose the URL to `edit.js`.

8. **The Author font is loaded centrally — don't redeclare it per block.** `inc/enqueue.php` enqueues the Fontshare CSS once on `enqueue_block_assets` so Author is available everywhere (front-end + editor iframe). In new blocks, just use `font-family: var(--font-base)` (or inherit from `body`) and it works. Don't add `@font-face` declarations or font URLs inside block stylesheets.

9. **Any full-bleed photo that's likely to be the page's LCP element (hero-style sections, above-the-fold photo panels) must be a real `<img fetchpriority="high" decoding="async">`, never a CSS `background-image`.** A background-image can't be discovered by the browser's preload scanner from the raw HTML — it has to wait for CSS to parse first — so it always loses the race for an above-the-fold photo. This was PageSpeed fix #7 (Aug 2026), applied retroactively to all 6 hero-family blocks. Build new hero/photo-panel blocks with the real-`<img>` pattern from day one: wrapper div gets `overflow: hidden` (to clip the zoom control) instead of `background-size`/`background-position`, and the `<img>` inside gets `object-fit: cover` + `object-position` (the `background-position` equivalent) plus `transform: scale()`/`transform-origin` for any zoom/focal-point control. See the Hero block (`src/blocks/hero/`) for the reference implementation.

10. **Every block.json needs a `"version"` field, kept in sync with `CROPX_THEME_VERSION`.** WordPress core only uses `metadata['version']` to build the `?ver=` query string on a block's auto-registered `style`/`editorStyle`; if it's absent (ours were, until Aug 2026), WP falls back to the **WordPress core version** — which never changes on a theme deploy. `inc/enqueue.php` now has a `block_type_metadata` filter that injects `CROPX_THEME_VERSION` for every `cropx/*` block automatically, so no per-block.json edits are needed — but this means **`CROPX_THEME_VERSION` in `functions.php` must be bumped on every deploy that changes any block's CSS/JS**, or browsers that already cached the old file will never refetch it. This bit us once already (Aug 2026 — the curved-hero PageSpeed fix landed correctly on staging but stayed invisible to Lauren's browser for this exact reason).

More detail and additional gotchas — including the full write-up of the drift-pattern.svg base64-bloat trap (gotcha #7 above), the LCP-image pattern, and this cache-busting fix — live in `PROGRESS.md` under "WordPress block development gotchas."

## Design System

All visual values live in `tokens/tokens.css` as CSS custom properties. **Never hardcode colors, spacing, typography, or radii** — always reference a token variable.

### Key token groups

| Group | Key variables |
|---|---|
| Brand colors | `--deep-blue`, `--cropx-blue`, `--muted-gold`, `--terra`, `--new-leaf` |
| Greyscale | `--gray-50` through `--gray-700` (warm gray palette) |
| Semantic | `--success`, `--warning`, `--error`, `--info` (each with `-tint` and `-text` variants) |
| Typography | `--fs-*` (type scale), `--fw-*` (weights), `--lh-*` (line heights), `--ls-*` (letter spacing) |
| Spacing | `--space-1` (4px) through `--space-24` (96px) |
| Layout | `--max-w: 72rem`, `--section-py: 5rem` |
| Radii | `--radius-sm` (2px) through `--radius-card` (asymmetric CropX signature shape) |
| Shadows | Deep Blue tinted — never generic black shadows |

### Segment accent system

CropX has four audience segments, each with its own accent color:

| Segment | Color variable |
|---|---|
| General / CropX | `--cropx-blue` |
| Enterprise | `--muted-gold` |
| Service Provider | `--terra` |
| On-Farm | `--new-leaf` |

Segment accent colors are used for: hero underlines (`--hero-em-color`), icon box backgrounds, stat card left border, button accent stripe (`--btn-accent-color`), and section underlays.

### Typography

- Font: **Author** (variable weight, ITF/Fontshare), fallback: Aptos, Arial, sans-serif. Loaded from Fontshare's CDN in the WordPress theme; declared via `local()` in the static block library so designers see it when they have it installed.
- Base: 16px
- Use `filter: brightness(0) invert(1)` on icon `<img>` elements — icons are always white on colored square boxes

### CropX visual signatures

- **Asymmetric card radius**: `--radius-card: 40px 2px 40px 2px` — do not substitute with symmetric values
- **Asymmetric photo radius**: `--radius-photo: 60px 2px 60px 2px`
- **Hero emphasis underline**: two thicknesses — 6px for hero H1 and pre-footer CTA only, 3px for all other emphasis
- **Shadows**: always Deep Blue tinted (`rgba(36, 53, 101, ...)`) — never black

### Button rules

- `btn-primary` (Deep Blue fill) — light backgrounds only, never on Deep Blue sections
- `btn-white` and `btn-outline-white` — use on Deep Blue / dark backgrounds
- `btn-accent-edge` — set `--btn-accent-color` per segment on the element

### Navigation

- White background, mobile breakpoint at `--nav-breakpoint: 900px`

## Visual Reference

Open `tokens/tokens-reference.html` directly in a browser to see all tokens rendered with swatches, type specimens, and usage notes.
