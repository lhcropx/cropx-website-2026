# Project Progress & Handoff

Full state of the CropX website rebuild as of **June 5, 2026**. Use this as a context primer for any new Claude session so we never lose progress.

---

## Phase 2: ✅ All 19 blocks ported to WordPress Gutenberg as of May 17, 2026.
## Nav: ✅ Wired to WordPress native menus as of June 5, 2026.

---

## ▶ Start here for next session

**Phase 2 is complete.** All 19 custom Gutenberg blocks are implemented, committed, and running on the local WP install.

**Nav is fully WP-native.** Three registered menus (`cropx-solutions`, `cropx-platform`, `cropx-utility`) with custom walkers output the exact cnav-* HTML. Login button remains a block attribute. Menus are managed through Appearance → Menus in WP Admin. Both `cropx/nav` and `cropx/segment-hero` share a single PHP partial (`inc/parts/nav.php`) and JS module (`src/shared/nav-init.js`). Nav CSS loads globally via `inc/enqueue.php`.

**Currently in: Phase 3 polish backlog.** Two items done (nav extraction + nav menus). Remaining polish items listed below. After polish, move to Phase 3 page templates.

**Roadmap position: End of Week 1 / Start of Week 2 (June 5).** Dev is significantly ahead of the roadmap schedule — all 19 blocks done, nav menus wired. The roadmap's Batch A page builds start Week 4 (June 28). Use Weeks 2–3 for polish backlog + staging environment setup + beginning page templates.

To orient: read this file, then `CLAUDE.md`, then `wp-theme/cropx/src/blocks/hero/` as the block reference template.

---

## Quick Status

**Phase:** WordPress port — Phase 1 ✅, Phase 2 ✅, Phase 3 (page templates) next
**Approach:** Build standalone HTML/CSS blocks first, port into WordPress as custom Gutenberg blocks
**Stack:** WordPress (Local by Flywheel) + GitHub + Claude Code
**Repo:** https://github.com/lhcropx/cropx-website-2026

### Done ✅
- Design token system (`tokens/tokens.css` + visual reference page)
- **All 20 HTML/CSS blocks** committed to `blocks/`
- Image optimization pass (WebP + responsive sizes + srcset)
- Claude Code installed and authenticated locally
- `CLAUDE.md` project briefing
- **WordPress Phase 1**: Theme scaffolded (`wp-theme/cropx/`), build pipeline working (`@wordpress/scripts`), Hero block ported as Gutenberg dynamic block, installed and activated on local WP site (`cropx-2026-2`), Author font self-hosted via Fontshare

### Remaining ⬜
- WordPress Phase 3: pre-built page templates ("Segment Landing Page", "Product Page", etc.)
- Phase 3 polish backlog (see "Phase 3 — Polish backlog" section below)
- Switch back to multisite (deferred until closer to launch)
- Internal staging review
- Production go-live

---

## Block Library — Committed (20)

| Block | File | Notes |
|---|---|---|
| Nav (standard) | `blocks/nav.html` | White bar, full-color logo, click-to-open mega menu (Platform) + simple dropdown (Solutions). Underline pinned to bottom edge. Login button right-aligned. |
| Nav (segment) | `blocks/nav-segment.html` | Three segment variants with colored badge between logo and links. Stays white on scroll. |
| Hero (general) | `blocks/hero.html` | Photo bg with flat dark mobile gradient (rgba(10,12,20,0.55) at <=600px), accent-edge CTA. **Uses canonical nav.html (synced).** |
| Hero (segment) | `blocks/segment-hero.html` | 3 segment variants. Each has its own nav with colored badge + sensor PNG (right) + phone PNG (bleeds 30px below) + 25px segment color border below hero. |
| Segments | `blocks/segments.html` | Three segments (Enterprise/Gold, Service Provider/Terra, In-Field/Green). Center-aligned, no dividers, mobile tab navigation, SVG arrow on hover. |
| Logo strip | `blocks/logo-strip.html` | White bg, full color logos, marquee, edge fades, Deep Blue eyebrow. |
| Feature + stat card | `blocks/feature-stat.html` | 4 variants: Variant A/B (photo right/left) × white/Deep Blue backgrounds. Photo inset so stat card never crosses into text column. White stat card on Deep Blue sections. Lining figures. |
| Stats grid | `blocks/stats-grid.html` | Left column (eyebrow + heading + body + CTA) + 2×2 grid of stats. Each stat = 6px segment-accent left border + Deep Blue number + warm near-black sentence-case descriptor. **Accent overridable via inline style for segment pages.** |
| Cards | `blocks/cards.html` | White and Deep Blue card variants. Photo, title, "Read [x]" CTA all clickable. Tags wired to placeholder URLs. |
| Pre-footer CTA | `blocks/pre-footer-cta.html` | Photographic background with combined overlay treatment. 25px accent stripe along top edge using `--accent`. |
| Testimonials carousel | `blocks/testimonials-carousel.html` | 5-card horizontal scroll-snap row, 3 visible on desktop. 2rem bleed for shadow rendering. **Manual nav only (no auto-advance).** Card chrome modeled on cards.html. |
| Footer | `blocks/footer.html` | Variant C — 4 equal columns (brand left-aligned + 3 nav groups). Fully responsive. |
| FAQ accordion | `blocks/faq-accordion.html` | Expandable Q/A list with +/− toggle, smooth open/close. |
| Testimonial (single) | `blocks/testimonial-single.html` | Big-quote spotlight version. One large quote with attribution, no carousel. |
| Three-column with icons | `blocks/three-column-icons.html` | Eyebrow + heading + 3 columns. Each column has icon box (Deep Blue square, white icon), sub-heading, body, "See how it works →" link. |
| Two-column alternating | `blocks/two-column-alternating.html` | Repeating feature-stat pattern without the stat card. Photo alternates left/right between rows. |
| Two-column text + photo | `blocks/two-column-text-photo.html` | Single row of feature-stat without the stat card. |
| Two-column text + PNG | `blocks/two-column-text-png.html` | Same pattern with transparent product PNG instead of photo. |
| Two-column with overlay | `blocks/two-column-text-photo-overlay.html` | Same pattern with product PNG floating over photo. Column gap derives from overlay edge; 3 vertical positions for the overlay. |
| Hardware lineup | `blocks/hardware-lineup.html` | Product lineup row: sensor + phone + supporting hardware with names and descriptions. |

---

## Design Decisions Locked In

### Color rules
- **NO accent-color text:** Never use New Leaf green, Muted Gold, or Terra cotta as text color anywhere on the site. These colors are for borders, underlines, icon box backgrounds, and accent stripes ONLY. Approved text colors: Deep Blue, white, gray scale, CropX Blue.
- Documented as a comment in `tokens.css` under `:root`.

### Segment color application
Sections that need to switch color on segment pages all use a `--accent` CSS variable on the section element with `var(--cropx-blue)` as the default. Override per segment via inline style:

```html
<section class="stats-grid" style="--accent: var(--muted-gold);">  <!-- Enterprise -->
<section class="stats-grid" style="--accent: var(--terra);">       <!-- Service Provider -->
<section class="stats-grid" style="--accent: var(--new-leaf);">    <!-- On-Farm -->
```

Currently used by: `pre-footer-cta`, `stats-grid`, `feature-stat`. The `--hero-em-color` variable plays the same role on heroes.

### Greyscale
- Warm grays (Option A from token review). `--gray-700: #3C3A36`. Not the cooler Tailwind defaults.

### Hero gradient overlay
- **Desktop:** Left-heavy linear gradient from rgba(10,12,20,0.70) to rgba(10,12,20,0.08).
- **Mobile (≤600px):** Flat overlay at rgba(10,12,20,0.55). Lighter and uniform so text remains readable when it stretches full width.

### Hero emphasis underline
- Two thicknesses: 6px for hero H1 and pre-footer CTA only, 3px everywhere else.
- Tokens: `--hero-em-thickness` and `--em-thickness`.

### Buttons
- **Primary** is Deep Blue (`#243565`) — never used on Deep Blue backgrounds.
- **White** and **outline-white** for dark backgrounds.
- **Accent-edge** variant: Deep Blue fill with colored left stripe via `--btn-accent-color`. Available in all four segment colors.
- Hover/active for white button: `--gray-100` / `--gray-200` (warm grays).
- Disabled: `--gray-100` background + `--gray-400` text.

### Border radius
- All in `px` (not rem) — asymmetric shapes need to stay fixed regardless of font scaling.
- `--radius-card: 40px 2px 40px 2px` — used by `cards.html`
- Tighter asymmetric `1rem 0.125rem 1rem 0.125rem` (16px) for testimonials cards
- `--radius-photo: 60px 2px 60px 2px`
- `--radius-photo-mobile: 40px 2px 40px 2px`
- `--radius-md: 4px` — icon-square radius (also used by testimonials author squares)

### Typography
- Body font: Author 400/500/600/700, fallback Aptos / Arial / sans-serif
- `--fs-feature-heading`: weight 700, letter-spacing `-0.015em`
- `--fs-segment-name`: 2rem fixed, line-height 1.05
- Standard CTA link pattern (matches feature-stat `.fstat-link`): 0.9375rem, weight 500, CropX Blue → CropX Blue Dark on hover, with 16×16 SVG arrow that nudges 3px right on hover. Used in stats-grid, feature-stat.

### Stat card
- Solid Deep Blue on white sections, solid white on Deep Blue sections.
- Always 6px left or right accent border using `--accent`.
- Number: lining figures, special characters (`%`, `+`, `$`) at full height (not superscript).
- Context text: weight 600, opaque white (or Deep Blue on white card).
- Metric text: weight 600, opaque white (or Deep Blue on white card).

### Stats-grid stat (different from stat card)
- Borderless minimal — 6px segment-accent **left border** on each stat, sized cap-to-baseline + ~6px past baseline for visual balance.
- Number: clamp(3rem, 7vw, 5rem), Deep Blue, line-height 0.85, with negative top margin to trim half-leading.
- Descriptor: clamp(0.9375rem, 1.4vw, 1.0625rem), weight 400 (matches body paragraph), gray-700, sentence case.

### Card chrome (modeled on cards.html for white-bg cards)
- background: white
- border-radius: `--radius-card` for content cards, `1rem 0.125rem 1rem 0.125rem` for testimonial cards
- box-shadow: `--card-shadow` (0 2px 20px rgba(36,53,101,0.09)), Deep-Blue-tinted
- hover: translateY(-3px) + `--card-shadow-hover` (0 10px 36px rgba(36,53,101,0.17))

### Animated background pattern
- Option A (slow horizontal drift, fades on the left) is the standard.

### Logo strip
- Logo height: 38px, opacity 0.85 default / 1.0 hover, marquee 35s, edge fade 6rem.

### Content tags (Option B locked)
- `tag--dark`: Deep Blue bg, white text — for light card backgrounds
- `tag--white`: White bg, Deep Blue text — for dark card backgrounds
- Tags are clickable — wired to placeholder URLs like `/resources/case-studies` with HTML comment documenting the WordPress integration intent.

### Pre-footer overlay treatment
- All four techniques combined: radial gradient + 0.58 base opacity + full white subtext + text shadows.
- 25px top accent stripe using `--accent` (defaults to CropX Blue, set per segment).

### Footer (Variant C)
- 4 equal columns: brand (logo left-aligned + tagline + contact + social) + Platform + Solutions + Company
- Responsive: 4-col desktop → 2-col tablet (brand spans full width on top) → 1-col mobile
- Social: LinkedIn, X, YouTube
- Legal bar: copyright + Privacy Policy, Terms of Use, Cookie Settings

### Segment nav badge — locked specs (used in nav-segment + segment-hero)
- Padding: `1.25em` left/right, `0.75rem` bottom
- White text, weight 600, font-size `0.9375rem`, letter-spacing `0.08em`, text-shadow `0 1px 2px rgba(0,0,0,0.2)`
- Bottom-aligned within badge with `0.75rem` margin beneath text
- Badge hangs ~1.5rem below nav via negative bottom margin + `overflow: visible` on nav and inner container

### Testimonials carousel
- 5 cards in markup; 3 visible desktop, 2 at <=900px, 1 at <=600px
- Card width: `calc((100% - 3rem) / 3 - 1px)` — the -1px absorbs subpixel rounding so no card-4 sliver peek
- Track has 2rem bleed via negative margin + matching padding so card shadows render fully on the leftmost/rightmost cards
- Native scroll-snap for touch + trackpad swipe; circular outlined arrows + dots in centered row below
- **No auto-advance** — manual nav only (so reading isn't disrupted)
- Author square: 3.5rem with `--radius-md` (4px) — accepts logo SVG or profile photo

---

## WordPress Port

### Phase 1 — done ✅

The Hero block is shipped end-to-end as a Gutenberg dynamic block. Everything below is committed in `wp-theme/cropx/`:

- **Theme scaffold** (`style.css`, `functions.php`, `header.php`, `footer.php`, `index.php`, `page.php`, `inc/` for setup/enqueue/blocks).
- **Build pipeline**: `@wordpress/scripts ^27.9.0` via `npm run build` / `npm start`. Source in `src/blocks/<name>/`, compiled output in `build/blocks/<name>/`.
- **Block registration**: `inc/blocks.php` auto-registers every block in `build/blocks/*` via `register_block_type()`.
- **"CropX" block category** registered so all custom blocks sit at the top of the inserter.
- **Design tokens** load via `enqueue_block_assets` so they apply on the front-end AND inside the block editor iframe (this is the modern reliable way — see gotcha #2 below).
- **Author font** loaded from Fontshare's CDN (`api.fontshare.com/v2/css?f[]=author@1,2`) for both contexts, with preconnect resource hints.
- **Hero block** (`cropx/hero`): editable eyebrow / heading / subheading / CTA / background image / segment accent. Three-layer composition (photo + 105° dark-left gradient + drifting decorative SVG) matching the static design. Editor preview mirrors the front-end render.

Installed and activated on local site `cropx-2026-2`. Theme synced via rsync from project repo to `~/Local Sites/cropx-2026-2/app/public/wp-content/themes/cropx/`. Multisite was tried but switched to single-site due to REST API routing issues; multisite is deferred until closer to launch.

### Phase 2 — port the remaining ~19 blocks

Each block follows the same five-file pattern as Hero:

```
wp-theme/cropx/src/blocks/<block-name>/
├── block.json       Block metadata + attributes
├── index.js         registerBlockType call + CSS imports
├── edit.js          Editor UI component (RichText, InspectorControls)
├── render.php       Front-end render template
└── style.css        Front-end + editor shared styles
```

Then `npm run build` and rsync to the WP install. Order of attack — start with simpler static-content blocks, end with carousel/JS-heavy ones:

1. ✅ Footer
2. ✅ Logo strip
3. ✅ Pre-footer CTA
4. ✅ Three-column with icons
5. ✅ Stats grid
6. ✅ Feature + stat card
7. ✅ Two-column text + photo (merged into cropx/two-column — see #8)
8. ✅ Two-column text + PNG (merged with #7; single block via visualType enum)
9. ✅ Two-column alternating
10. ✅ Two-column with overlay
11. ✅ Cards
12. ✅ FAQ accordion
13. ✅ Testimonial (single)
14. ✅ Hardware lineup
15. ✅ Segments
16. ✅ Segment hero
17. ✅ Nav (standard)
18. ✅ Testimonials carousel

Phase 2 complete. All 19 blocks ported as of May 17, 2026.

### Phase 3 — Polish backlog

Items noted during Phase 2 that are not blockers; revisit during Phase 3 polish.

- **Stats grid responsive spacing** — at the 900px breakpoint where the 5fr/7fr two-column layout collapses to stacked (content on top, stat cards below), the vertical gap between the body paragraph and the first stat row is too large. The grid's `gap` value is shared between row and column directions. Fix: set an explicit smaller `row-gap` on `.sg-inner` at the stacked breakpoint, or split `gap` into `column-gap` / `row-gap`. Not a visual blocker for block development.

- **cropx/two-column-overlay mobile layout** — on single-column collapse (768px), the overlay PNG currently pins to 70% width inside the photo bounds. Preferred behavior: PNG should be horizontally centered with the photo and hang below the photo's bottom edge (bleeding down rather than sitting inside). Acceptable fallback if technically awkward: hide the photo entirely on mobile and show only the PNG. Not a blocker; revisit during Phase 3 polish.

- **cropx/two-column-overlay image performance** — overlay PNG and photo render as CSS background-image divs (correct for the desktop sizing model: width auto-resolved from CSS offsets, height from aspect-ratio). Trade-off: misses native `<img>` lazy-loading and srcset. If performance becomes a launch concern, evaluate whether the sizing model can be adapted to use `<img>` for either or both assets.

- **Extract reusable array-repeater component** — Three blocks now use the array-attribute repeater pattern inline (no shared abstraction): `cropx/two-column-alternating` (rows, 5 fields), `cropx/cards` (cards, 9 fields), `cropx/faq-accordion` (items, 2 fields). The trigger condition for extraction (3+ usages) has been met. Honest assessment: each repeater is small enough that the duplication isn't expensive; the per-item shapes differ enough that a clean generic abstraction is non-trivial; and ~2 more Phase 2 blocks may use the pattern (Testimonials carousel). Recommendation: defer extraction to Phase 3 polish, once the full set of repeater shapes across all blocks is known and refactoring won't risk regressions mid-Phase-2.

- ~~**cropx/nav and cropx/segment-hero nav extraction**~~ ✅ **Done (June 5, 2026).** Shared `inc/parts/nav.php` partial + `src/shared/nav-init.js` module. Both blocks' view.js import the same JS; render.php calls `cropx_render_nav()`. Nav also wired to WP native menus — three registered locations (`cropx-solutions`, `cropx-platform`, `cropx-utility`) with custom Walker classes. Login button stays as block attribute. Sticky nav removed site-wide. Mega dropdown JS toggle changed from CSS `:has()` to direct `.is-open` class on the dropdown element (more reliable cross-browser).

- ~~**Nav hover-gap on dropdown triggers**~~ ✅ **Done (June 5, 2026).** Root cause: Chrome UA stylesheet sets `box-sizing: border-box` on `<button>` elements but `content-box` on `<a>` elements, causing the teal underline to sit 1px above the nav's bottom border on hover. Fix: explicit `box-sizing: content-box` on `.cnav-btn` in nav/style.css.

- **Stat cards in feature-stat block should be clickable** — when a stat card (the number + context + metric callout in the `cropx/feature-stat` two-column layout) is sourced from a specific case study, it should link through to that case study. Currently the stat card has no URL. Fix: add an optional `statUrl` attribute to the feature-stat block's stat card data, render as a wrapping `<a>` when present. Revisit in the next block polish round.

### Phase 2 — WordPress block development gotchas

**Save your future self hours by reading this before porting any block.**

1. **NEVER use `source: "html"` + `selector: "..."` on string attributes in dynamic blocks.** That tells WordPress to re-parse the value from the block's saved HTML — but dynamic blocks have no saved HTML (save returns null), so the attribute always comes back empty and you fall through to the default. Symptom: editor shows the user's typed content, front-end shows the default. Fix: plain `{"type": "string", "default": "..."}` attributes, no `source`, no `selector`. They get stored in the block delimiter comment and round-trip cleanly.

2. **`enqueue_block_editor_assets` does NOT reach the editor preview iframe.** In WordPress 6.x, the editor preview lives inside an iframe; styles enqueued via `enqueue_block_editor_assets` only land in the admin chrome OUTSIDE the iframe. To inject tokens.css (or any global styles) into both front-end AND the editor iframe, use `enqueue_block_assets`. See `wp-theme/cropx/inc/enqueue.php`.

3. **`tokens.css` declares variables but doesn't apply them.** The original `tokens.css` defined `--font-base` etc. but didn't have a `body { font-family: var(--font-base); }` rule. Without that, nothing on the page actually uses the variables and you get system fallback fonts. Base body rule is now at the bottom of `tokens.css` — applies to both `body` and `.editor-styles-wrapper` (the editor iframe wrapper).

4. **CSS files need to be imported in JS source for webpack to bundle them.** webpack only processes CSS that's imported by a JS entry. Put `import './style.css';` in `index.js` and `import './editor.css';` in `edit.js`. Then reference webpack's OUTPUT names in `block.json`: `"style": "file:./style-index.css"` (front-end) and `"editorStyle": "file:./index.css"` (editor only). NOT `style.css` / `editor.css` — those are the source names.

    **block.json needs all four file references**: `editorScript`, `editorStyle`, `style`, and `render`. Missing `editorScript` is especially dangerous — the block silently fails to compile and won't appear in the block inserter at all. The symptom is a thin build output: only `block.json` and `render.php` copied to `build/`, with no JS or CSS bundles. All five ported blocks have it; don't omit it on new ones. Canonical shape:
    ```json
    "editorScript": "file:./index.js",
    "editorStyle":  "file:./index.css",
    "style":        "file:./style-index.css",
    "render":       "file:./render.php"
    ```

5. **Webpack inlines small assets as base64 data URIs.** When you reference an asset in source CSS with a relative URL like `url('../../../assets/decorative/foo.svg')`, webpack's url-loader will base64-inline anything under ~10KB into the compiled CSS. That's fine for SVGs (still works), but if you need a real URL (e.g. an `<img>` src, or a larger asset), inject it via PHP/JS instead: `CROPX_THEME_URI . 'assets/...'` in render.php, or use `wp_localize_script()` to expose it to edit.js.

6. **Re-insert old block instances after schema changes.** WordPress doesn't migrate stored block attributes when block.json changes. If you change an attribute's name or `source`, existing blocks on saved pages keep the old (now invalid) data. During Phase 2, you'll be the only editor, so just delete and re-insert when you change a block. For production, you'd write a deprecation/migration.

7. **The editor iframe needs the same DOM structure as render.php.** For blocks with layered backgrounds (gradient + photo + pattern), make sure `edit.js` renders the same nested divs as `render.php` does — otherwise the editor preview won't match the front-end. See the Hero block's three stacked layer divs.

8. **Dev workflow: `npm start` (watch mode) + manual rsync.** `npm start` watches and rebuilds the JS/CSS on save. After it rebuilds, you still need to rsync to the WP install. Hard-refresh the editor (`Cmd+Shift+R`) to bypass browser cache. Local doesn't follow symlinks well, so rsync is the path of least resistance.

9. **Permalinks setting matters.** Settings → Permalinks → "Post name" is required for REST API routes to work. Without it the editor errors with `Updating failed. The response is not a valid JSON response.`

10. **wp-cli is your friend for theme activation.** `wp theme activate cropx` from the Local site shell handles it cleanly. The Appearance → Themes UI also works.

11. **Every ported block needs a scoped CSS reset to match the static design's global `* { margin: 0 }`.** The static `blocks/*.html` files each include `*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }` in their embedded `<style>`. WordPress has no equivalent global reset, so `<p>` elements (and others) inside blocks inherit browser default margins — most visibly `margin-bottom: 1em` on `<p>`, which throws off column spacing when `<p>` tags are used for headings or labels inside grid/flex layouts. Fix per block: add these two rules to the block's `style.css`, scoped to the WP block class:
    ```css
    .wp-block-cropx-<name> *,
    .wp-block-cropx-<name> *::before,
    .wp-block-cropx-<name> *::after { box-sizing: border-box; }
    .wp-block-cropx-<name> p { margin: 0; padding: 0; }
    ```
    The four already-ported blocks (Footer, Logo Strip, Pre-footer CTA, Hero) use this pattern — **do not retrofit them**.

    **Better pattern for new blocks going forward:** the scoped `p { margin: 0 }` approach has a hidden trap — its specificity (0,1,1) beats single-class rules (0,1,0) for intentional margins, and it only covers `<p>`, not `<h2>` or other elements with UA margins. The Pre-footer CTA hit both issues and needed two specificity-boosting fixes as a result. For all new blocks use `:where()` to give the reset zero specificity, and cover the full set of elements with UA margins:
    ```css
    :where(.wp-block-cropx-<name>) *,
    :where(.wp-block-cropx-<name>) *::before,
    :where(.wp-block-cropx-<name>) *::after { box-sizing: border-box; }
    :where(.wp-block-cropx-<name>) :is(p, h1, h2, h3, h4, h5, h6, ul, ol, blockquote, figure) {
      margin: 0;
      padding: 0;
    }
    ```
    With `:where()` the reset has specificity (0,0,0), so any single-class rule like `.my-element { margin-bottom: 1.25rem }` automatically wins — no specificity boosting needed, ever. And covering `h1`–`h6` from the start means no per-element `margin-top: 0` patches either.

12. **Before testing a new block, verify all referenced theme assets exist in `wp-theme/cropx/assets/`.** Many design-system assets live in the project root's `assets/` folder; not all were copied during the initial theme scaffold. The bulk sync after Phase 2 block #5 closed the immediate gaps — run `rsync -av --ignore-existing /assets/ /wp-theme/cropx/assets/` if anything seems off — but it's worth a quick `ls wp-theme/cropx/assets/icons/` (or whichever subfolder) before testing each new block.

13. **The `:where()` source-order trap: `:where()` flattens specificity to zero, so when multiple `:where()` rules can match the same element, source order decides — not specificity.** A "fallback default" rule placed AFTER more-specific rules will silently overwrite them, because at zero specificity, "later wins." Discovered during Segment Hero: a `:where(.sgh-block) { --sgh-accent-color: var(--muted-gold); }` fallback placed last clobbered all three segment-specific accent colors — only Enterprise looked correct because it happened to share the same value. Fix patterns: (a) ensure fallbacks come FIRST in source order so the variant rules that follow override them; (b) skip fallbacks entirely when PHP guarantees a valid class is always emitted — the fallback buys nothing and is an active hazard; (c) use a normal class selector (not wrapped in `:where()`) for the fallback so it loses to the more-specific `:where()` rules on actual specificity.

14. **Admin URL fields that reference media should use the WordPress media picker, not a plain `<input type="url">`.** A plain URL input works but forces editors to copy-paste URLs manually. The pattern: in the meta box callback, add a `<button type="button" class="button" id="my-field-btn">Choose from Media Library</button>` alongside the input; in `admin_enqueue_scripts` (gated to the relevant post type), call `wp_enqueue_media()` and add an inline script that uses `wp.media()` to open the media browser and populate the input on selection. See `inc/admin-ui.php` and the Resource download URL field in `inc/cpts.php` for the reference implementation.

15. **WordPress UA stylesheet overrides `:where()` block CSS for `<a>` properties.** WordPress injects a UA-level rule `a { background-color: transparent }` (and similar bare-element rules) that has specificity (0,0,1) — higher than any `:where()` rule's (0,0,0). This means block CSS wrapped in `:where(.wp-block-cropx-<name>)` cannot override bare-element defaults on `<a>` tags. Discovered during the mobile login button visibility fix (Phase 3 polish, commit 78dbc09): the login button `<a>` was invisible because WP's `a { background-color: transparent }` won over the `:where()` rule setting the button's background color. Fix: use a real scoped selector (not wrapped in `:where()`) for any property on `<a>` elements that needs to beat UA defaults. A selector like `.wp-block-cropx-<name> .my-class` has specificity (0,2,0) and cleanly overrides the (0,0,1) UA rule. General pattern: use `:where()` for resets (where zero specificity is the goal), but use real class selectors for any intentional styles on `<a>` tags or other elements that WordPress explicitly resets.

---

## Asset Library

### Photos — optimized library (June 3, 2026)

Optimized WebP files in `assets/images/photos/`. All filenames are SEO-friendly slugs starting with `cropx-`. Responsive size variants (768w, 480w, thumb) and JPG fallbacks are also in this folder alongside the full-size WebPs.

**Content categories (by filename prefix):**
- **Hardware/sensors** — `cropx-evato-*` (sensor in orchard, potato fields ×5, on Reinke pivot), `cropx-vertex-v4-*` (sensor close-up install, vineyard install, orchard soil, Cory/Pierced Heart Solutions install), `cropx-rivo-rain-gauge-*` (field photos ×2), `cropx-strato-weather-station-*` (field photos ×3 + front transparent PNG)
- **Reinke partner** — `cropx-reinke-e3-*` (corn field ×10, bean field ×1, sunset/golden-hour corn ×8)
- **Team headshots** — `cropx-team-*`: Bradley Darden, Brooke, Ben T, Elijah O, Felix B, Gabriela G (v2), Jason F, Joe W, Kurt G, Lee B, Naomi C, Nic S, Nick L, Rebecca S, Shelley A, Tim D, Todd C (17 total)
- **Agronomy / field work** — `cropx-agronomist-*`, `cropx-agronomy-*` (farmers + advisors with tablets, vineyard leaf inspection, field greens, wheat, orchard)
- **Precision agriculture** — `cropx-precision-ag-*`, `cropx-precision-agriculture-*`, `cropx-precision-farming-*`, `cropx-precision-crop-*` (vineyard panoramics, advisor series, soybean, citrus, sugarcane, potato harvest)
- **Vineyard / wine** — `cropx-vineyard-*`, `cropx-vision-vineyard-*`, `cropx-sensors-vineyard-*`, `cropx-farm-technology-solutions-vineyard-*`, `cropx-technology-ripening-grapes-*`, `cropx-corn-vineyard-ripe-wine-grapes`, `cropx-digital-farming-platform-vineyards-aerial-view`
- **Orchard** — `cropx-orchard-*`, `cropx-evapotranspiration-almond-orchard-*`, `cropx-sustainable-crop-production-almond-orchard-*`, `cropx-farm-irrigation-almond-orchard-*`, `cropx-agriculture-digital-platform-apple-orchard-*`, `cropx-smart-farm-sensors-high-density-orchard`, `cropx-smart-agriculture-monitoring-orchard-aerial-view`, `cropx-citrus-*`, `cropx-agronomy-system-citrus-*`
- **Row crops / field** — `cropx-agricultural-technology-corn-field-*`, `cropx-vertex-v4-carrot-field-*` (×7), `cropx-vertex-v4-corn-field-*` (×6), `cropx-vertex-v4-blueberry-orchard`, `cropx-agriculture-wheat-field-*`, `cropx-canola-rapeseed-*`, `cropx-cotton-*`, `cropx-crop-yield-potatoes-*`, `cropx-farming-tomato-plants-ripe`, `cropx-smart-irrigation-sensors-bananas`, `cropx-banana-plantation-agriculture`, `cropx-precision-farming-soybean-field`, `cropx-precision-farming-sensors-sugarcane-*`, `cropx-tree-nuts-pecan-orchard`, `cropx-tulip-flower-field`, `cropx-agronomist-tulip-flower-field`
- **Smart farming / digital platform** — `cropx-digital-farming-platform-*`, `cropx-digital-farm-management-*`, `cropx-smart-farm-*`, `cropx-smart-farming-*`, `cropx-farm-app-tablet-*`, `cropx-mobile-app-dashboard-*`, `cropx-agronomy-app-tablet`, `cropx-field-monitoring-sensors-mobile-app-farmer`
- **Soil health** — `cropx-soil-health-*`, `cropx-plant-roots-cutaway-soil-health-*`, `cropx-corn-plant-roots-cutaway-soil-health-vertical`, `cropx-platform-soil-health-nutrition-management`, `cropx-soil-sensor-technology-potatoes-*`
- **Supply chain / enterprise** — `cropx-supply-chain-*`, `cropx-enterprise-farming-*`, `cropx-factory-supply-chain-*`, `cropx-harvest-supply-chain-*`, `cropx-agriculture-supply-chain-silo`, `cropx-agronomy-machinery-connections-*`, `cropx-harvest-machinery-connections-*`, `cropx-smart-farming-supply-chain-silos`
- **Aerial / landscape** — `cropx-digital-farming-platform-fields-aerial-view-*`, `cropx-digital-farming-platform-new-zealand-fields-aerial-view`, `cropx-vineyard-water-management-aerial-view`, `cropx-precision-agriculture-orchard-scene-aerial`, `cropx-smart-farming-new-zealand-aerial-view`, `cropx-field-aerial-view-harvester`
- **Cattle / livestock** — `cropx-agriculture-cattle-*`, `cropx-smart-farming-cattle-*`, `cropx-netherlands-cattle-europe-potatoes`
- **Misc** — `cropx-zambia-vertex-v4field-deployment`, `cropx-apex-corn-field-landscape`, `cropx-engineering-team-office-development`, `cropx-precision-agriculture-vineyard-scene-ai` (AI-generated), `cropx-sand-county-foundation-samuel-agronomist-installing-sensor`, `cropx-night-agricultural-landscape-silos`

**Known issues in the folder:**
- ~~`Not Uploaded`~~ ✅ deleted
- `cropx-citrus-orchard-yield-optimization-2.webp` ✅ renamed (was missing leading `c`)

**Note:** 2 HEIC files were skipped (`IMG_20260513_110352.heic`, `IMG_7205.HEIC`) — convert manually in Preview (Export As → JPEG), add to `raw-photos/`, and re-run `python3 optimize-photos.py`.

Source files: `raw-photos/` | Rename map: `raw-photos/rename-map.json` | Optimizer script: `optimize-photos.py`

### Icons (10)
alarm-clock, antenna, corn, field-sun, fields, language, nutrition, sensor-cloud, speed, valve-irrigation

### Logos
- CropX full color
- CropX white
- 11 customer logos: AB InBev, Dairy Holdings, General Mills, Rotoplas, NASA, NEC, HZPC, McCain, Nestlé, PepsiCo, Ritter Sport

### Product PNGs
- Hardware sensor (transparent background)
- Phone with app screenshot (transparent background)

### Other
- `hero-pattern.svg` — animated background pattern (Option A drift)

---

## Workflow Reminders

### Saving work to GitHub
After updating any file in `~/Documents/github/cropx-website-2026/`:
```bash
cd ~/Documents/github/cropx-website-2026
git add .
git status                      # check what's about to be committed
git commit -m "Descriptive message"
git push
```

If git complains about a stale lock file (`fatal: Unable to create ... index.lock: File exists`), run:
```bash
rm .git/index.lock
```

### Running Claude Code
```bash
cd ~/Documents/github/cropx-website-2026
claude
```

### Local Git identity
- Name: Lauren Hostetter
- Email: lauren.hostetter@cropx.com

---

## How to Use This Doc

If starting a new Claude session:
1. Have Claude read this `PROGRESS.md` first
2. Have Claude read `CLAUDE.md` for the technical briefing
3. Have Claude read `tokens/tokens.css` for the design system
4. **If working on WordPress Phase 2**, also have Claude read:
   - `wp-theme/cropx/README.md` — theme setup and build pipeline
   - `wp-theme/cropx/src/blocks/hero/` — all five files (block.json, index.js, edit.js, render.php, style.css) as the reference template every new block follows
5. Then describe what you want to work on next — or just say "let's pick up where we left off" and reference the **▶ Start here for next session** banner at the top.

That gives Claude full context without needing the entire chat history re-pasted.
