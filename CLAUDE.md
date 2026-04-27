# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Status

Early-stage redesign of the CropX website (2026). Currently only the design token layer exists — no framework or build system has been chosen yet. When a framework is added, update this file with build/dev/test commands.

## Repository Structure

```
tokens/
  tokens.css           — Single source of truth for all design values (CSS custom properties)
  tokens-reference.html — Visual browser-based reference for all tokens
```

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

- Font: **Author** (weights 400/500/600/700), fallback: Aptos, Arial, sans-serif
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
