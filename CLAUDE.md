# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

For running status, what's done, what's next, and a deeper context dump, read `PROGRESS.md` first. This file is the technical briefing — conventions, commands, and project layout.

## Project Status

Redesign of the CropX website (2026). Current phase: porting the static block library into a custom Gutenberg block theme for WordPress.

- Design tokens: complete (`tokens/tokens.css`)
- Static HTML/CSS block library: 20 blocks, complete (`blocks/`)
- WordPress theme scaffold + build pipeline: complete (`wp-theme/cropx/`)
- Hero block ported as Gutenberg dynamic block (the proof-of-concept template): complete
- **Next**: port the remaining ~19 blocks following the Hero pattern (see "WordPress block development" below)

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

More detail and additional gotchas live in `PROGRESS.md` under "WordPress block development gotchas."

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
