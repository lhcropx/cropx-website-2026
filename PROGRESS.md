# Project Progress & Handoff

Full state of the CropX website rebuild as of **July 17, 2026**. Use this as a context primer for any new Claude session so we never lose progress.

<!-- last updated: July 17, 2026 -->

---

## Phase 2: ✅ All 19 blocks ported to WordPress Gutenberg as of May 17, 2026.
## Nav: ✅ Wired to WordPress native menus as of June 5, 2026.
## CPT Layer: ✅ All post types, taxonomies, and admin UX complete as of June 8, 2026.

---

## ▶ Start here for next session

**Phase 2 is complete.** All 19 custom Gutenberg blocks are implemented, committed, and running on the local WP install. Several additional WordPress-only blocks have been built beyond the original 19 (see "WordPress-only Blocks" table).

**Nav is fully WP-native.** Three registered menus (`cropx-solutions`, `cropx-platform`, `cropx-utility`) with custom walkers. Login button remains a block attribute. Nav CSS loads globally via `inc/enqueue.php`.

**CPT layer is complete.** Five custom post types with full editorial UX (see CPT section below for details).

**Ten page pattern templates complete and deploy-ready.** Homepage, Enterprise, Service Provider, On-Farm, Products Hub, Blog Archive, About CropX, Hardware Product Page, and Contact — all in `wp-theme/cropx/patterns/`, registered in `inc/patterns.php`, all using staging-server URLs.

**Single templates are complete.** `single.php` (blog posts) and `single-cropx_publication.php` (case studies + white papers) are both polished and in the theme. No local URLs hardcoded.

**Block background color system overhauled (July 7–8, 2026).** All 20 blocks now default to "white" (or the appropriate deep-blue default for mid-page-cta and testimonial-single). Deep-blue option added to two-column, two-column-alternating, two-column-overlay, and hardware-lineup. Taupe option added to feature-stat. Gray option removed from people-showcase and video. Full details in the July 7–8 entry below.

**Native block content width fixed (July 9, 2026).** Paragraph, Heading, List, Image, Table, Columns, etc. placed directly on a Page now respect the same 72rem max-width and 2rem side padding as CropX custom block inner containers. Fix is in `styles/content.css` — no build needed, just rsync.

**Pending build + rsync.** All block source changes since the last build need `npm run build` + rsync before they appear in WordPress. This now includes: Gutenberg sidebar panel standardization (Task #171), shared IconPicker component, hero swoop editor-preview simplification, background color system overhaul, the five zoom-bug fixes from July 10, the contact-form body-text color fix from July 13, AND the newsletter-cta full overhaul from July 13 session 2 (5 source files: `newsletter-cta/block.json`, `newsletter-cta/render.php`, `newsletter-cta/style.css`, `newsletter-cta/edit.js`, `newsletter-cta/editor.css`). `home.php` and `single.php` changes are PHP-only — no build needed, just rsync.

**⚠️ When deploying to staging**: manually create the `segment-navigation` Synced Pattern in the staging WP admin (Appearance → Patterns → Add New, slug: `segment-navigation`). The pattern files silently skip the block until it exists in the database.

**Next tasks:** Product page template (#131), cookie consent banner implementation (#132), blog post import research (#134), regional contact page template.

**Today (July 17, 2026):** Split Header + Contact Columns block built; contact form opacity/paragraph improvements; global `p + p` spacing; Contact page pattern created and registered; six-column-icons and split-column-icons blocks built (prior sessions, uncommitted until now).

**Today (July 14, 2026):** Hero swoop fill bug fixed (pages showing `#ffffff` gap), eyebrow/footer-nav font size fixed at three locations (hardware-lineup, logo-strip, footer), 404.php hero corrected + anchor links added, Page Workflow feature built (status + assignee sidebar panel + admin list columns — no build needed).

To orient: read this file, then `CLAUDE.md`, then `wp-theme/cropx/src/blocks/hero/` as the block reference template.

---

## Quick Status

**Phase:** WordPress port — Phase 1 ✅, Phase 2 ✅, Phase 3 (page templates) in progress
**Approach:** Build standalone HTML/CSS blocks first, port into WordPress as custom Gutenberg blocks
**Stack:** WordPress (Local by Flywheel) + GitHub + Claude Code
**Repo:** https://github.com/lhcropx/cropx-website-2026

---

### Done ✅
- Design token system (`tokens/tokens.css` + visual reference page)
- **All 20 HTML/CSS blocks** committed to `blocks/`
- Image optimization pass (WebP + responsive sizes + srcset)
- Claude Code installed and authenticated locally
- `CLAUDE.md` project briefing
- **WordPress Phase 1**: Theme scaffolded (`wp-theme/cropx/`), build pipeline working (`@wordpress/scripts`), Hero block ported as Gutenberg dynamic block, installed and activated on local WP site (`cropx-2026-2`), Author font self-hosted via Fontshare

### Done since last update ✅ (July 15–17, 2026)

- **`cropx/split-contact-columns` block — new** (Tasks #189–192): Five-file dynamic block for office/contact location listings. CSS namespace: `scc-`. Structure: two-zone grid — left third (`scc-header`) holds eyebrow, H2, body paragraph; right two-thirds (`scc-grid`) holds a 3-column-per-row contact grid. Each column (`scc-item`) has an H3 location name, optional Phone row (H4 label + `<p>`), and optional Address row (H4 label + `<p>` with `nl2br()` for multi-line addresses). No item cap — columns added freely. Background variants: white / taupe / deep-blue. `position: static` on `.scc-header` (sticky positioning was intentionally removed after review). Key render.php detail: `nl2br( esc_html( $address ) )` keeps address lines in a single `<p>` so the global `p + p` margin-top doesn't apply between address lines. **Build gotcha fixed**: `render.php` and `"render": "file:./render.php"` in `block.json` must both be present in the *build* output, not just in `src/` — `npm run build` copies them; running build before adding render.php left the build directory without them. Fixed by manually copying both files into `build/blocks/split-contact-columns/` after the fact.

- **`cropx/six-column-icons` block — new**: Six-file dynamic block. Full-width 6-column icon grid with optional eyebrow, heading, and body. Each column: icon box, sub-heading, body, optional CTA link. Supports white and Deep Blue backgrounds with per-segment icon box accent tinting. DnD reorder via `src/shared/reorder.js`. CSS namespace: `sci-`.

- **`cropx/split-column-icons` block — new**: Five-file dynamic block. Two-zone layout — left third holds eyebrow, H2, body paragraph; right two-thirds holds a 3-column icon grid. Each column: icon box, sub-heading, body, optional CTA link. Essentially a split-header variant of `three-column-icons`. Supports white / taupe / Deep Blue backgrounds. CSS namespace: `scic-`.

- **Contact form — opacity and paragraph improvements** (three files):
  - `style.css`: placeholder text opacity raised `0.32 → 0.5` on deep-blue card; disclaimer/legal text opacity raised `0.38 → 0.7` on deep-blue card.
  - `render.php`: intro text now processed with `wpautop()` (double newlines → `<p>` tags, single newlines → `<br>`). Wrapper changed from `<p>` to `<div class="cf-intro-body">` and `'p' => array()` added to `wp_kses` allowed tags so the generated `<p>` tags survive sanitization.
  - `edit.js`: canvas preview mirrors `wpautop()` behavior — splits on `/\n\n+/`, wraps each chunk in `<p>`, converts single `\n` to `<br>`. Previously the preview was a plain `<p>` with no newline handling.

- **Global paragraph spacing added** (two CSS files, no build needed — static files):
  - `styles/shared.css`: `p + p { margin-top: 0.85em; }` — specificity 0-0-2, beats per-block `:where()` resets (0-0-1), so it applies inside custom block markup too.
  - `styles/content.css`: `.wp-block-paragraph + .wp-block-paragraph { margin-top: 1.25rem; }` — covers native WordPress Paragraph blocks placed sequentially.
  - Address fields in `split-contact-columns` use `nl2br()` within a single `<p>` to avoid the `p + p` margin applying between address lines.

- **Contact page pattern** (`wp-theme/cropx/patterns/page-contact.php`): nav → hero-curved-standard (Contact us, bgImageId:505, bgFocalY:0.64) → contact-form (white bg, deep-blue card, multi-paragraph intro with mailto link) → split-contact-columns (6 global offices, showEyebrow:false, showBody:false) → field-photos (3 photos, showEyebrow/showHeading:false). **JSON escaping gotcha**: the mailto `href` attribute inside `introText` contains `<`, `>`, and `"` which must be JSON-safe unicode escapes (`<`, `>`, `"`) in the PHP file. Raw `"` characters inside a JSON string break WordPress block comment parsing. File written via Python to guarantee literal escape sequences. Registered in `inc/patterns.php` under the `cropx-pages` category with slug `cropx/page-contact`.

- **`inc/patterns.php` — Contact pattern registered**: Added `'cropx/page-contact'` entry to the `$patterns` array. This was the root cause of the pattern not appearing in the inserter — the file existed in `/patterns/` but was not listed for registration.

### Done since last update ✅ (July 14, 2026)

- **Hero swoop fill bug fixed** (`inc/helpers.php`): On pages where native WordPress content (paragraphs, headings — blocks with no `bgColor` attribute) followed the hero, the curved bottom-right swoop was rendering in `#ffffff` (white) instead of `#fbfaf9` (the actual site body background per `theme.json`). Root cause: two early-return fallbacks inside `cropx_get_swoop_fill()` were returning `'#ffffff'`; the correct `'#fbfaf9'` fallthrough at the end of the function only fires when the hero is the *last* block, but for native content blocks the wrong color was hit first. Fixed both to `'#fbfaf9'`. PHP-only change — no build needed, just rsync.

- **Eyebrow and footer-nav font size fixed at three locations** — three CSS files updated:
  - `src/blocks/hardware-lineup/style.css`: Added `.wp-block-cropx-hardware-lineup .hwf-header .section-eyebrow { font-size: 0.75rem; grid-column: 2; text-align: center; margin: 0; }`. Key gotcha: `.wp-block-cropx-hardware-lineup` and `.hwf-section` are on the SAME element — using `.hwf-section` as a descendant selector is a no-op. Must descend through `.hwf-header` (a genuine child div) to reach `.section-eyebrow`. Three-class chain (0,3,0) beats WP global styles at (0,2,1).
  - `src/blocks/logo-strip/style.css`: Added `.wp-block-cropx-logo-strip .logo-strip-inner .section-eyebrow { font-size: 0.75rem; text-align: center; margin-bottom: 2rem; }`.
  - `src/blocks/footer/style.css`: Added parent-class scoping to bump `.footer-nav-heading` specificity: `.footer-nav-group .footer-nav-heading { font-size: 0.75rem; ... }`.
  - **Requires `npm run build` + rsync** to take effect in WordPress.

- **404.php hero block corrected + anchor added**:
  - Hero attributes updated to use the correct `bgImageId:181` + staging URL with `bgFocalY:0.57`, `bgZoom:108`, all three show* attributes false.
  - `ctaUrl:"#contact"` added to the hero CTA button so "Contact Us" scrolls to the form.
  - `{"anchor":"contact"}` added to the `cropx/contact-form` block (the block already has `"anchor": true` in its `block.json` supports).
  - PHP-only — no build needed, just rsync.

- **Page Workflow feature built** (status + assignee for all Pages — no build needed):
  - **`assets/js/page-workflow.js`** (new): Vanilla JS Gutenberg sidebar plugin. Adds a "Page Workflow" panel to the Document sidebar when editing any Page. Two `SelectControl` dropdowns:
    - *Status*: I — Design Phase / II — Copy Phase / III — Visual Polish Phase / IV — Review Phase / V — Complete
    - *Assigned to*: Larissa / Lauren / Julia
  - **`inc/admin-ui.php`** (updated): Three additions appended at the end of the existing file:
    - `register_post_meta()` for `_cropx_page_status` and `_cropx_page_assignee` — both `show_in_rest: true` so the block editor can read/write them via the REST API.
    - `enqueue_block_editor_assets` hook enqueues `page-workflow.js` only when editing a Page (screen post_type check).
    - `manage_pages_columns` + `manage_pages_custom_column` filters add **Status** and **Assigned To** columns to the Pages admin list, with colored phase badges (blue/yellow/purple/orange/green per phase).
  - No plugin needed — fully in-theme.

### Done since last update ✅ (July 13, 2026 — session 3)

- **Publication archive — complete** (Tasks #184–188): Four files created + `inc/enqueue.php` updated to serve the `cropx_publication` CPT archive and `cropx_content_type` taxonomy archive pages.

  **`archive-cropx_publication.php`**: Mirrors `home.php` (blog archive) exactly — same page structure, same white card design — but sources data from the `cropx_publication` CPT and `cropx_content_type` taxonomy. Key details:
  - Archive hero: reads `cropx_pub_archive_page_id` WP option (set once via `update_option()` in functions.php or WP CLI; page ID 1259 on local install), renders that page's blocks via `do_blocks()`. Identical mechanism to `home.php`'s Posts Page hero.
  - Filter pills in grid section header (right column): "All" → CPT archive URL; per-term pills → `get_term_link()`. Active state detected via `is_tax()` + `get_queried_object()`.
  - `rewind_posts()` called defensively before the while loop (matches `home.php`).
  - `pa_type_plural()` helper: lookup map for plural names (avoids "Case Studys").
  - `cropx_pa_type_class()` helper: maps content-type slugs to CSS tag-chip modifier classes.
  - Gradient placeholder cycle: 8 placeholders (blue, wheat, soil, sky, grove, dusk, forest, gold) matching the blog archive.
  - Ends with `do_blocks()` calls for `newsletter-cta` (taupe bg) and `pre-footer-cta`.
  - **Featured Publications carousel intentionally excluded**: removed at user request — the page goes directly from the card grid + Show More to the newsletter CTA.

  **`taxonomy-cropx_content_type.php`**: One-liner — `get_template_part('archive', 'cropx_publication')`. Both `/publications/` and `/content-type/{term}/` routes share the same template; active filter state is determined inside the shared template via `is_tax()`.

  **`styles/pub-archive.css`** (CSS namespace: `pa-*`): Mirrors `blog-archive.css` (`ba-*`) exactly — same white card design, same 3-col grid, same asymmetric `--radius-card`, same `--card-shadow`/`--card-shadow-hover`. Content-type badge variants: `.pa-card-badge` (deep blue, default), `.pa-card-badge--wp` (teal, for White Papers). Filter pills: `.pa-filter-pill` + `.pa-filter-pill--active` (deep blue fill). Tag chips: `.pa-tag--dark`, `.pa-tag--teal`, `.pa-tag--gold`, `.pa-tag--terra`, `.pa-tag--leaf`.

  **`assets/js/pub-archive.js`**: IIFE pattern mirroring `blog-archive.js`. Reads `data-max-pages`/`data-per-page` from `.pa-grid`; reads `window.cropxPubArchive.restUrl` and `.termId` from `wp_localize_script`. `buildCard()` uses DOM construction (XSS-safe) and finds `cropx_content_type` from `_embedded['wp:term']` groups. Removes `.pa-show-more` wrapper entirely when all pages loaded (matches blog-archive.js).

  **`inc/enqueue.php`**: Condition `is_post_type_archive('cropx_publication') || is_tax('cropx_content_type')` enqueues `pub-archive.css` and `pub-archive.js`; `wp_localize_script` passes `restUrl` and `termId` (0 for non-filtered archive, term ID for taxonomy views).

  **`cropx_pub_archive_page_id` option setup**: WP-CLI not available in local environment. Workaround: temporarily added `update_option('cropx_pub_archive_page_id', 1259)` to `functions.php`, rsynced, loaded any page (option written to DB), then removed the line and rsynced again. Option is now persisted in the local WP database.

### Done since last update ✅ (July 13, 2026 — session 2)

- **`wp-theme/cropx/404.php` template created**: WordPress auto-loads this file on any 404 response. Structure: `get_header()` → `do_blocks($blocks)` → `get_footer()` — identical pattern to `page.php` but with hardcoded block markup instead of `the_content()`. Blocks: nav, hero-curved-standard ("404 Error" heading + soil-health photo bgImageId:590, bgFocalY:0.61, showEyebrow/showDeviceImage/showAppImage false), two cards blocks (both `queryMode:"auto"` — both default to blog posts unless `queryPostType` is specified), contact-form, pre-footer-cta (smart-farm photo bgImageId:578). All image URLs use the staging server prefix. Note: if "Recent Case Studies" should pull `cropx_publication` entries, add `"queryPostType":"cropx_publication"` to that cards block attribute string.

- **`cropx/newsletter-cta` block — major overhaul (5 source files + 2 PHP templates)**:

  **Background color system** (`block.json`, `render.php`, `style.css`, `edit.js`): New `bgColor` attribute — `"white"` (default) | `"taupe"` | `"deep-blue"`. Editor exposes this in a "Section Settings" panel at the top of the sidebar (same standardized panel name used across all other blocks). On deep-blue, a per-instance `<style>` tag injects the topo drift SVG URL (same technique used by the contact-form block — can't reference `CROPX_THEME_URI` in webpack-bundled CSS). The `::before` pseudo-element animates via `@keyframes ncta-topo-drift` at 80s linear infinite; `prefers-reduced-motion` guard included.

  **Typography** (`render.php`, `edit.js`): Heading now uses `<h2 class="section-heading ncta-heading">` (the global `section-heading` class from `shared.css`, same as contact-form). Body text is `<p class="section-body ncta-desc">` (matching contact-form). Both adapt automatically: `section-heading` is deep-blue by default; on deep-blue sections, `.ncta--bg-deep-blue .section-heading` overrides to white (specificity 0,2,0 beats shared.css's 0,1,0). Same WP Global Styles belt-and-suspenders pattern from the contact-form fix: selector targets both the container AND bare `<p>` descendants.

  **Icon box** (`render.php`, `style.css`, `edit.js`): The bare envelope SVG is now wrapped in `.ncta-icon-box` — 56×56px, 4px border-radius (matches the site-wide icon-box system). Colors adapt per background: light bg (white/taupe) → CropX Blue fill, white SVG; deep-blue bg → white fill, deep-blue SVG. The SVG uses `stroke="currentColor"`, so the icon color follows the CSS `color` property on the box.

  **Form styling** (`render.php`, `style.css`, `edit.js`): The email `<input>` now uses class `.ncta-input` with styles that exactly match `.cf-input` from the contact-form block: `padding: 0.6875rem 0.875rem`, `font-size: 1rem`, `border-width: 1.5px`, `border-radius: 2px`, focus ring `box-shadow: 0 0 0 3px rgba(12,168,192,0.14)`. The person icon that was previously inside the input has been removed (contact-form doesn't use interior icons; it's a cleaner UX). On deep-blue, the input background switches to `rgba(255,255,255,0.07)` with light placeholder text — matching the contact-form dark-input style exactly.

  **Button** (`render.php`, `style.css`): Subscribe button now uses the global `.btn-primary` class instead of a hand-rolled `.cropx-btn-subscribe` style. On deep-blue backgrounds, `.ncta--bg-deep-blue .ncta-btn-subscribe.btn-primary` overrides to `background-color: var(--cropx-blue)` — you can't put a Deep Blue button on a Deep Blue section.

  **Privacy note** (`style.css`): Font size changed to `0.8rem`. Colors adapt per bg: gray-400 on white/taupe, `rgba(255,255,255,0.38)` on deep-blue.

  **CSS namespace**: All class names renamed from `cropx-newsletter-*` to the shorter `ncta-*` prefix (consistent with `cf-*` for contact-form, `mcta-*` for mid-page-cta). This is a compile-time-only change — dynamic blocks re-render from PHP on every page load, so no stored HTML migration is needed.

  **`editor.css`**: Updated to use new class names. Keeps the dashed-outline visible block boundary and the `opacity: 1` override for disabled form fields in the editor preview.

  **`home.php` + `single.php`** (PHP-only, no build needed): Both templates previously called `do_blocks('<!-- wp:cropx/newsletter-cta /-->')` with no attributes (default white background). Now both pass `{"bgColor":"taupe"}` so the auto-included newsletter section on the blog archive and blog post singles renders with the taupe-50 background. Newsletter blocks placed manually via the block editor are unaffected — they still default to white unless changed in the block inspector.

### Done since last update ✅ (July 13, 2026)

- **`cropx/contact-form` block — body text color bug fixed (deep-blue background):** On the published page with the "deep blue" background option selected, the intro body text was rendering dark (invisible) against the dark background, even though the editor preview showed it correctly in white.

  **Root cause:** `render.php` wrapped the intro text in `<div class="section-body cf-intro-body"><p>text</p></div>`. WordPress Global Styles (generated from `theme.json`'s `styles.color.text: "#202121"` and `core/paragraph color.text: "#3C3A36"`) produces explicit element-level CSS on bare `<p>` tags. These explicit declarations beat CSS inheritance — so even though the parent `.section-body` div had `color: rgba(255,255,255,0.78)`, the `<p>` inside received an explicit dark color override from WordPress that won regardless. The editor didn't exhibit the bug because `edit.js` was rendering the text directly in a `<div>` (no nested `<p>`), so the white color was applied directly to the element, not inherited through a parent.

  **Fix — three source files modified:**
  - **`render.php`**: changed intro text output from `<div class="section-body cf-intro-body"><p>…</p></div>` to `<p class="section-body cf-intro-body">…</p>`. White color is now an explicit direct declaration on the text element rather than inherited through a parent div.
  - **`style.css`**: extended the deep-blue override selector to explicitly target bare `<p>` descendants as belt-and-suspenders: `.cf-section--bg-deep-blue .section-body, .cf-section--bg-deep-blue .section-body p { color: rgba(255,255,255,0.78) }`. Also removed now-obsolete `.cf-intro-body p` margin rules — `.cf-intro-body` is now itself a `<p>`, not a `<div>`.
  - **`edit.js`**: changed matching editor canvas `<div>` to `<p>` so the editor preview stays in sync with the front-end render.

  **Key takeaway for other blocks:** WordPress `theme.json` `styles.color.text` and `styles.blocks.core/paragraph.color.text` generate explicit element-level color declarations that beat CSS inheritance at any specificity. Whenever a custom block needs to override text color on a dark background, apply the color directly to the text-bearing element (not just a parent container), or add an explicit descendant `p` selector in addition to the container rule.

### Done since last update ✅ (July 10, 2026 — session 3)

- **Three-column-icons block crash fixed**: `TextControl` was used in the JSX for CTA label/URL fields but was missing from the `@wordpress/components` import in `edit.js`. Added it. Block now previews correctly in the editor.

- **Mid-page CTA button accent stripe standardized**: The `.mcta-btn--primary` and `.mcta-btn--white` buttons were using `border-left: 4px solid` (4px, hard border) instead of the standard `box-shadow: inset 0.5rem 0 0 var(--hero-em-color)` (8px inset) used on all other CTA buttons. Fixed in `mid-page-cta/style.css`. Also added `padding-left: calc(2.125rem + 4px)` to push text clear of the stripe. Ghost button left unchanged (no stripe on ghost style).

- **Segments editor card overflow fixed**: At narrow editor canvas widths (sidebar open), the fixed `aspect-ratio: 5/6` or `10/11` made cards too short to hold tag + name + desc + CTA — text started overlapping. Fixed in `segments/editor.css` with `aspect-ratio: auto !important; min-height: 360px !important`. The `!important` is required to override the `@media (min-width: 1180px)` rule in `style.css` that also sets `aspect-ratio`. Frontend is unaffected (editor.css only).

- **Hero swoop indicator simplified** (all 3 hero blocks): The `editor.css` gradient-based swoop preview (60px fade) was replaced with a simple 20px solid `background: var(--accent)` bar across the bottom of all three hero editor canvases (`hero-curved`, `hero-curved-standard`, `hero-blog`). Cleaner and less distracting while editing.

- **Photo zoom bug fixed** (three blocks, 5 files total): Increasing the Zoom slider above 100% was scaling the photo's FRAME rather than zooming within the frame. Root causes differed per block:
  - `two-column-overlay`: `transform: scale()` was applied directly to `.tco-photo` (the background-image div that IS the frame). Fix: introduced an inner `.tco-photo-bg` div (position: absolute; inset: 0; background-size: cover) that receives the background-image and transform. Outer `.tco-photo` gains `position: relative; overflow: hidden`. Changes in `style.css`, `render.php`, and `edit.js`.
  - `two-column`: `.tcv-photo-wrap` already had `overflow: hidden` but was missing `position: relative`. Without a positioning context, `overflow: hidden` doesn't reliably clip CSS-transformed children in all rendering environments (particularly the editor iframe). Added `position: relative` to `.tcv-photo-wrap` in `style.css`. No changes to `edit.js` or `render.php`.
  - `two-column-alternating`: Same `position: relative` fix for `.tca-photo-col`, PLUS the `border-radius` was on `.tca-photo` (the img) rather than `.tca-photo-col` (the clipping parent). Moved `border-radius: var(--radius-photo)` to `.tca-photo-col`; added the flipped-diagonal rule `.tca-row--photo-left .tca-photo-col`; updated the mobile media query to use `.tca-photo-col` selectors. This ensures rounded corners come from the overflow-clipping boundary, not from the img. Changes in `style.css` only.

### Done since last update ✅ (July 9, 2026 — session 2)

- **Gutenberg sidebar panel standardization — all blocks (Tasks #171 + #172)**:
  Standardized every block's InspectorControls to follow a consistent panel layout. Rules applied across the entire block library:
  - First panel is always **"Section Settings"** (both words capitalized, `initialOpen: true`)
  - Section Settings order: (1) background color, (2) segment accent color if applicable, (3) show/hide toggles only, (4) single icon picker if applicable
  - All single-icon blocks use the visual `IconPicker` component (not a `SelectControl` dropdown)
  - Layout controls (photo position, video position, mobile stack, float image) moved to dedicated **"Photo Positioning"** or **"Video Positioning"** panels
  - Text content (eyebrow `TextControl`) stays in Section Settings only if it is NOT an inline `RichText` on the canvas; if it IS a canvas `RichText`, the `TextControl` is removed from the sidebar
  - `testimonial-single` block was intentionally left alone (its layout attributes don't belong on this block)
  - **Blocks modified this session** (8 remaining after prior session completed four icon-picker blocks):
    - `pre-footer-cta/edit.js` — reordered Section Settings: segmentAccent → showEyebrow → eyebrowColor → showCta
    - `resource-downloads/edit.js` — renamed "Section Header" → "Section Settings"; moved bgColor to top; bgColor removed from Layout panel
    - `field-photos/edit.js` — full restructure: new Section Settings panel (bgColor → showEyebrow → showHeading → eyebrowColor), Photos panel repositioned below it; old "Section Header" and "Background" panels eliminated
    - `testimonials-carousel/edit.js` — made eyebrowColor conditional on `showEyebrow !== false`; moved eyebrow `TextControl` to new "Section Header" panel (correct because eyebrow is a static `<span>` on the canvas, not inline RichText)
    - `mid-page-cta/edit.js` — renamed "Appearance" → "Section Settings"; merged Content panel into Section Settings; removed eyebrow `TextControl` (eyebrow IS inline RichText on canvas); eliminated Content panel entirely
    - `people-showcase/edit.js` — renamed "Section settings" (lowercase) → "Section Settings"; merged "Intro / header" panel into Section Settings with proper conditional nesting
    - `cards/block.json` + `cards/edit.js` — added `showEyebrow` attribute; added showEyebrow `ToggleControl` nested inside the `showHeader` conditional; made eyebrow `TextControl` and eyebrowColor conditional on `showEyebrow !== false`
    - `hardware-lineup/block.json` + `hardware-lineup/edit.js` — added `showEyebrow` attribute; updated Section Settings; updated canvas to gate eyebrow rendering on `showEyebrow !== false`
  - **Verification grep (Task #172)**: No `ICON_OPTIONS` references anywhere. The four out-of-scope blocks (`hero-blog`, `product-sections`, `product-tabs`, `product-grid`) still have lowercase "Section settings" — intentionally left unchanged.

- **Shared IconPicker component extracted (Task #170, prior session)**:
  - `wp-theme/cropx/src/shared/IconPicker.js` — searchable visual grid with 118 icons across 8 categories; auto-imports `./icon-picker.css`
  - `wp-theme/cropx/src/shared/icon-picker.css` — all `.cropx-icon-*` CSS rules extracted from `three-column-icons/editor.css`
  - **Blocks updated to use shared IconPicker** (completed prior session): `two-column`, `two-column-overlay`, `two-column-video`, `feature-stat` — all now have Section Settings (bgColor → segmentAccent → showEyebrow → eyebrowColor → showIcon → IconPicker → showCta) and a separate "Photo Positioning" or "Video Positioning" panel
  - `three-column-icons/editor.css` — icon-picker CSS removed (now in shared); only `.tci-cta-preview { cursor: default; pointer-events: none; }` remains

- **Hero editor-preview swoop indicator fix (prior session)**:
  Three `editor.css` files updated: `hero-curved`, `hero-curved-standard`, `hero-blog`. The "swoop curve at bottom" editor indicator was using a `border-top` which produced a hard horizontal line. Replaced with a `linear-gradient` that fades from transparent to a semi-opaque wave shape, matching the actual front-end swoop appearance in the editor canvas.

### Done since last update ✅ (July 7–9, 2026)

- **Block background color system overhauled — all 20 blocks**:
  - **Group A — default → "white"** (9 blocks): `bgColor` default changed from "taupe" to "white" in `block.json` for: `cards`, `faq-accordion`, `logo-strip`, `product-grid`, `product-sections`, `product-tabs`, `stats-grid`, `testimonials-carousel`. `backgroundVariant` default changed for `three-column-icons`.
  - **Group B — default → deep blue**: `mid-page-cta` `backgroundStyle` default changed to "dark"; `testimonial-single` `backgroundVariant` default changed to "blue". Both already had full CSS for those variants.
  - **Feature Stat — taupe option added**: `backgroundVariant` enum expanded from `["white","blue"]` to `["white","taupe","blue"]`. Added `fstat-section--bg-taupe` CSS, editor SelectControl option, and `render.php` class logic.
  - **Hardware Lineup — deep-blue option added + default → "white"**: `bgColor` enum expanded to `["taupe","white","deep-blue"]`, default changed to "white". `render.php` updated. CSS added for `.hwf-section--bg-deep-blue` including carousel arrow overrides (rgba white tones).
  - **People Showcase — gray option removed + default → "white"**: Removed "gray" from `bgColor` enum in `block.json`; removed the option from `edit.js`; updated `render.php` fallback. Existing `.people--gray` CSS left in place (harmless since option is no longer selectable).
  - **Video — gray option removed + default → "white"**: Same approach as People Showcase. Removed "gray" from `bgColor` enum, `edit.js` SelectControl, `render.php` fallback. Existing `.vid-bg--gray` CSS left in place.
  - **Two-column, Two-column Alternating, Two-column Overlay — deep-blue added + default → "white"**: All three blocks had `bgColor` enum expanded to `["taupe","white","deep-blue"]` and default changed to "white". All three `block.json`, `edit.js`, `render.php`, and `style.css` files updated. Deep-blue CSS includes: section-heading/body/body-link overrides (white text), and block-specific CTA overrides (white button on deep blue for two-column; white color for tco-link/tco-cta; white color for tca-intro-cta/tca-heading/tca-body).

- **Hardware Lineup — taupe background pill fix**: Pills default to `var(--gray-50)` background. On a taupe section, gray-50 (`#F9F8F7`) barely contrasts against taupe (`#F3F1EE`). Fixed in `hardware-lineup/style.css`: `.hwf-section--bg-taupe .hwf-pill` → `background: var(--white)`; hover state → `var(--gray-50)`.

- **`cropx/resource-downloads` block — complete**: Five-file dynamic block for PDF/brochure resources. Features: 4-column grid layout, optional section eyebrow/heading/body, per-card image upload, card title/description (RichText), dual download buttons (Letter + A4 format), card order controllable via drag-and-drop reorder UI. New CPT meta fields added to `inc/cpts.php`: `download_url_letter` and `download_url_a4` (both use the WP media picker). Fully wired into build pipeline.

- **`cropx/two-column-video` block — complete**: New WordPress-only block (beyond the original 19). Two-column layout: rich-text content column + video column with inline playback. Five files: `block.json`, `index.js`, `edit.js`, `render.php`, `style.css` + `editor.css`. CSS namespace: `tcvid-`. Supports: YouTube/Vimeo URL or WP media library video; autoplay toggle; section eyebrow, heading, and body; CTA button or link style; video-left/right column order. Registered in `inc/theme-setup.php`.

- **Field-photos block — deep-blue background variant added**: `block.json` enum, `edit.js` SelectControl, `render.php` class logic, and `style.css` all updated to support a "deep-blue" background alongside existing "taupe" and "white" options. Editor CSS updated for consistency.

- **Cards block — deep-blue background variant added**: Same treatment as field-photos. `block.json`, `edit.js`, `render.php`, and `style.css` updated for deep-blue variant. Editor CSS updated.

- **Block category refinements**: Registered a new `cropx-global` block category in `inc/theme-setup.php`. Moved `cropx/nav`, `cropx/segments`, and `cropx/footer` into this category (they're sitewide infrastructure, not page-composition blocks). Hidden from the block inserter entirely: `cropx/hero`, `cropx/segment-hero`, `cropx/product-grid` (these are placed via patterns only, not manually).

- **Products Hub pattern updated** (`patterns/page-products-hub.php`): Revised block structure with new markup to reflect current block API.

- **Hardware Product page template pattern created** (`patterns/page-hardware-product.php`): New pattern file for hardware product pages (sensors, physical devices). Includes: hero-curved-standard, product specs two-column layout, hardware-lineup carousel, feature-stat, testimonial-single, related products, pre-footer CTA.

- **Native block content width fixed** (`styles/content.css`): Paragraph, Heading, List, Image, Gallery, Quote, Pullquote, Table, Separator, Buttons, Code, Columns, Embed, File, Audio, Video, Group, and other native WordPress blocks placed directly on a Page now have `max-width: var(--max-w)`, `margin-inline: auto`, `padding-inline: 2rem`, `box-sizing: border-box`. Previously they went edge-to-edge with no horizontal margin. Fix uses `article.type-page > .wp-block-*` direct-child selector so only top-level blocks are constrained — blocks nested inside Columns or Group use their parent's narrower width. Editor equivalents added using `.editor-styles-wrapper > .is-root-container >` selectors. This is a static CSS file loaded directly; no build step required, just rsync.

### Done since last update ✅ (July 6, 2026)

- **CSS consolidation — shared utilities, token fixes, 20+ block files de-duplicated** (Task #128):
  - Created `wp-theme/cropx/styles/shared.css` — globally enqueued via `inc/enqueue.php`, not webpack-compiled. Contains: `.section-padded--lg`, `.btn-primary`, `.icon-box` (56×56px), `.pg-*` product card CSS (moved from `product-sections` and `product-tabs`), `@keyframes cropx-hero-fade-up`, `@keyframes cropx-drift-left`.
  - **Token additions** — applied to BOTH `tokens/tokens.css` AND `wp-theme/cropx/styles/tokens.css` (important: the theme loads from the `wp-theme/` copy, not the project root):
    - `--fs-h2` bumped: `clamp(1.75rem, 3vw, 2.375rem)` → `clamp(1.875rem, 3vw, 2.5rem)`
    - Added `--section-py-lg: calc(var(--section-py) + 1.25rem)` (≈6.25rem — tall section variant)
  - **Token bug fixed**: `--gray-600` doesn't exist in the token palette (scale goes `--gray-500` → `--gray-700`). Fixed `color: var(--gray-600)` → `var(--gray-700)` in `styles/shared.css`, `product-tabs/editor.css`, and `product-sections/editor.css`.
  - **Removed from 20+ block `style.css` files**: duplicate `@keyframes cropx-hero-fade-up` and `cropx-drift-left`; hardcoded `#fbfaf9` and `#1b284c` color fallbacks; redundant `color: var(--gray-700)` on `.section-body` rules (now in shared.css); duplicate `.pg-*` CSS (now in shared.css); hardcoded `font-size: clamp(1.875rem, 3vw, 2.5rem)` overrides (now covered by the updated `--fs-h2` token); `calc(var(--section-py) + 1.25rem)` replaced with `var(--section-py-lg)` across 8 blocks.
  - **Build required**: block CSS changes live in `src/blocks/*/style.css` and must be compiled with `npm run build` before the changes appear in WordPress.

- **Segments block — whole card clickable**: Every segment panel is now a single `<a>` element rather than a `<div>` containing nested links. `render.php`: outer panel `<div>` promoted to `<a href="...">`; the heading's inner `<a>` wrapper removed (text is a direct child of `<h3>` now); the CTA `<a>` converted to `<span aria-hidden="true">` (decorative). `style.css`: added `text-decoration: none; color: inherit; cursor: pointer` to the panel rule. No invalid nested anchors.

- **`cropx/hero-curved` and `cropx/hero-curved-standard` — wide-content mode**: When both PNG overlay images (device + app) are hidden, the hero content expands by 33.33% at viewport widths ≥901px (where those PNGs would otherwise be visible). Implementation: `render.php` adds `hc-content--wide` / `shc-content--wide` modifier class when both `show_device_image` and `show_app_image` are false. CSS `@media (min-width: 901px)` widens headline and subheadline `max-width` from 16ch → ~21.3ch and 44ch → ~58.7ch respectively. No change at ≤900px (PNGs never show there anyway). `blocks/hero-curved-bottom.html` is locked — was not touched.

- **Synced Pattern infrastructure** (Task #129):
  - Added `cropx_get_synced_block_ref( string $slug ): int` to `inc/helpers.php` — looks up a `wp_block` CPT post by slug at runtime. Returns the post ID or 0. Slug is consistent across environments; numeric IDs are not.
  - Updated all 4 page pattern files to replace hardcoded `<!-- wp:cropx/segments {…} /-->` with the PHP helper:
    - `patterns/page-homepage.php` ✅
    - `patterns/page-segment-enterprise.php` ✅
    - `patterns/page-segment-service-provider.php` ✅
    - `patterns/page-segment-on-farm.php` ✅
  - Pattern files use `if ($_seg_ref)` guard — silently skips the block if the Synced Pattern doesn't exist yet (safe on a fresh environment).
  - **⚠️ Staging action required**: After uploading the theme to staging, go to Appearance → Patterns → Add New, recreate the Segments block, and set slug to exactly `segment-navigation`. The patterns won't render the segments section until this database record exists.

### Done since last update ✅ (July 5, 2026)

- **All page patterns updated to `hero-curved` / `hero-curved-standard`**: All 5 existing patterns updated to replace `cropx/hero` and `cropx/segment-hero` with the new curved hero blocks. Focal point and zoom values updated to match local WP editor state. All `cropx-2026-2.local` image URLs replaced with `http://ec2-100-25-145-190.compute-1.amazonaws.com`.
  - `page-homepage.php` — `cropx/hero` → `cropx/hero-curved-standard` (bgImageId:458, bgFocalX:0.26, bgFocalY:0.36, bgZoom:115, showDeviceImage/showAppImage false)
  - `page-segment-enterprise.php` — `cropx/segment-hero` → `cropx/hero-curved` (bgImageId:446, default focal/zoom)
  - `page-segment-service-provider.php` — `cropx/segment-hero` → `cropx/hero-curved` (segment:"service-provider", bgImageId:548, bgFocalY:0.63, bgZoom:114)
  - `page-segment-on-farm.php` — `cropx/segment-hero` → `cropx/hero-curved` (segment:"on-farm", bgImageId:441); testimonial quote artifact cleaned ("impressed. unimpressed." → clean)
  - `page-products-hub.php` — `cropx/hero` → `cropx/hero-curved-standard` (bgImageId:517, bgFocalX:0.03, bgFocalY:0.31, bgZoom:115, showDeviceImage/showAppImage false)

- **Blog archive pattern + `home.php` CSS fix**: `page-blog-archive.php` created — single-block pattern containing `cropx/hero-blog` (heading:"Agronomy Insights", bgImageId:652, vineyard tablet photo). Applied to the WP Posts page; `home.php` reads and renders it automatically. CSS fix in the same session: `.ba-popular-label` in `styles/blog-archive.css` now has `text-align: center` and `margin-bottom: 8px`.

- **About CropX pattern created** (`page-about.php`): nav, hero-curved-standard (bgImageId:544, bgFocalY:0.53, bgZoom:111, showCta/showDeviceImage/showAppImage false), 4×two-column (placeholder photoId:651 — vineyard specialist; showEyebrow/showCta false; content TBD), people-showcase (backgroundStyle:"dark", 4 US team members: Todd C/622, Tim D/621, Shelley A/620, Rebecca S/619, all with LinkedIn placeholders), logo-strip (eyebrow:"Our Investors"), mid-page-cta (eyebrow:"Join the Team", showSecondary false), video (backgroundStyle:"deep-blue", displayMode:"lightbox", layout:"two-column", 2 YouTube placeholder videos), pre-footer-cta.

### Done since last update ✅ (July 1, 2026)

- **`cropx/hero-curved` block ("Segment Hero — Curved Edge")**: New WordPress-only hero block. Full-bleed Deep Blue hero with an animated quadratic-bezier SVG swoop at the bottom. CSS namespace: `hc-`. Key mechanics and features:
  - **Swoop fill auto-detection**: `view.js` runs on `DOMContentLoaded`, reads `getComputedStyle(nextSibling).backgroundColor`, and writes it as `--hc-swoop-fill` on `.hc-bleed-wrap`. CSS fallback `#f3f1f1` keeps the seam invisible before JS runs. SVG fill path uses `fill: var(--hc-swoop-fill, #f3f1f1)` — no inline `fill` attribute, so the CSS variable works.
  - **Animated gradient swoop stroke**: `hc-curve-flow` linear gradient, 14s loop, inlines hex accent stop colors from PHP so they resolve at render time rather than relying on CSS variables (which SVG can't see).
  - **Image z-architecture**: Device (sensor) PNG lives inside `.hc-hero` (clipped by `overflow:hidden` at swoop); phone PNG lives as a sibling of `.hc-hero` inside `.hc-bleed-wrap` — it bleeds 30px below the curve without being clipped.
  - **`--hc-edge` system**: CSS custom property on `.hc-bleed-wrap` = `max(0px, (100vw − max-w) / 2)` — the inset from the viewport edge to the container boundary. Both images reference it so they stay locked to the container-right edge as the viewport resizes.
  - **Image position/scale controls**: Six attributes (`deviceScale`, `deviceOffsetX`, `deviceOffsetY`, `phoneScale`, `phoneOffsetX`, `phoneOffsetY`) written as inline CSS custom properties on `.hc-bleed-wrap` in render.php. Editor shows RangeControls (40–200% scale, ±400px offsets) in the "Product image overlay" inspector panel, but only after an image has been selected.
  - **Content padding**: 7.5rem top / 11.25rem bottom (desktop); 5.25rem / 7.5rem (≤600px). 50% larger than the old segment-hero.
  - **Segment**: enterprise / service-provider / on-farm — controls `--hc-accent` for the heading underline and swoop stroke gradient.
  - **Navigation**: rendered via `cropx_render_nav()` with segment badge included (same as segment-hero).
  - **Show/hide toggles**: eyebrow, CTA, device image, app image.

- **`cropx/hero-curved-standard` block ("Standard Hero — Curved Edge")**: Copied from `hero-curved` and modified for non-segment pages. CSS namespace: `shc-`. Key differences from `hero-curved`:
  - **No navigation**: `cropx_render_nav()` call removed from `render.php`. Nav is expected to come from `header.php` or a standalone nav block placed above the hero.
  - **No segment badge**: Badge config removed from `render.php`; badge div and all `.shc-nav-preview` styles removed from `edit.js` and `editor.css`; `.cnav-badge` and `.cnav-mobile-login` rules removed from `style.css`.
  - **CropX Blue added as accent color**: New `cropx` segment value (`#0CA8C0` base / `#0A8FA3` dark) added to `block.json` enum, `render.php` `$allowed_segments`/`$accent_config`, `SEGMENT_OPTIONS` in `edit.js`, and both CSS files. Set as the default (`"default": "cropx"`) — the natural accent for non-segment general pages. Full enum: `cropx`, `enterprise`, `service-provider`, `on-farm`.
  - All image controls, swoop auto-detection (`view.js`), and layout mechanics are identical to `hero-curved`.

### Done since last update ✅ (June 29, 2026)

- **Related-cards rewrite — blog singles** (`inc/parts/related-posts.php`): Rewrote the "More from CropX" card loop from the old `.card-*` classes to the `crd-*` system, matching the `cropx/cards` block markup exactly. Dark card variant (`crd-card--dark`) throughout. Tag (`a.crd-tag.crd-tag--white`) placed in `.crd-meta` inside the card body (not on the image). No date shown. Title: `h3.crd-title.crd-title--lines-2` (CSS line-clamp: 2). Excerpt: `p.crd-excerpt.crd-excerpt--lines-3` (CSS line-clamp: 3). CTA: `a.crd-cta` with "Read more" and the exact arrow SVG from `cards/render.php` (width="14", stroke-width="1.5", stroke-linecap/linejoin="round"). Grid class: `crd-grid`.

- **Related-cards rewrite — publication singles** (`single-cropx_publication.php`): Same `crd-*` treatment as blog singles. Added a separate `$crd_arrow` variable (different SVG dimensions than the existing `$icon_arrow` used for "View All") to match `cards/render.php` exactly. Tag linked to content-type term archive. Removed the `<p class="pub-related-sub">` description paragraph from the section heading.

- **Cards CSS explicitly enqueued on singles** (`inc/enqueue.php`): WordPress only auto-enqueues a block's stylesheet when that block is on the page. Since related sections use `crd-*` classes in PHP templates (not inside the block), `build/blocks/cards/style-index.css` is now manually enqueued via `wp_enqueue_style()` inside both the `is_singular('post')` and `is_singular('cropx_publication')` conditional hooks.

- **Unscoped `.crd-card-body` margin reset added** (`src/blocks/cards/style.css`): The existing margin/padding reset in `cards/style.css` was scoped to `:where(.wp-block-cropx-cards)`, so it only fired when cards were rendered inside the block wrapper. On singles, the default browser/theme margins on `p`, `h3`, etc. inside `.crd-card-body` stacked on top of the flex `gap`, creating excessive vertical spacing. Fixed by adding an unscoped `.crd-card-body :is(p, h1, h2, h3, h4, h5, h6, ul, ol) { margin: 0; padding: 0; }` rule.

- **`.crd-title--lines-2` utility class added** (`src/blocks/cards/style.css`): `display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; -webkit-line-clamp: 2;` — applied to related-section card titles on both singles. (`.crd-excerpt--lines-3` already existed.)

- **"Keep Reading" eyebrow color** (`styles/single.css`): Changed `.bsingle-related-posts-eyebrow` color from `var(--cropx-blue)` → `var(--deep-blue)`.

- **Heading font-weight: 700 → 600 on blog single** (`styles/single.css`): Changed five heading rules from `font-weight: 700` to `font-weight: 600`: `.bsingle-article-title`, `.bsingle-article-content h2`, `.bsingle-article-content h3`, `.bsingle-article-content h4`, `.bsingle-related-posts-title`. Left at 700: `.bsingle-toc-label`, `.bsingle-tags-footer-label`, and tag chip (small uppercase UI labels — weight 700 needed at those sizes for visual impact).

- **Heading font-weight: 700 → 600 on publication single** (`styles/pub-single.css`): Changed six heading rules: `.pub-title`, `.pub-content h2`, `.pub-content h3`, `.pub-content h4`, `.pub-download-heading`, `.pub-related-title`. Left at 700: `.pub-share-label`, `.pub-cs-details-eyebrow`, and field label classes (uppercase UI labels).

- **Breadcrumb pluralization fix** (`single-cropx_publication.php`): The breadcrumb was using `$primary_type->name . 's'` — correct for "White Papers" but produced "Case Studys". Fixed with an explicit `$type_plurals` lookup array (`'case study' => 'Case Studies'`, `'white paper' => 'White Papers'`) and a `??` fallback for any future content types. Pattern is documented for future CPT additions.

### Done since last update ✅ (June 26, 2026 — session 2)

- **`cropx_publication` single-page template** (`single-cropx_publication.php`): Full WP template for the `cropx_publication` CPT (Case Studies and White Papers). New publication meta fields registered in `inc/cpts.php`: `pub_download_url`, `pub_location`, and three key-finding stat pairs (`pub_stat_1..3_value` / `pub_stat_1..3_label`). Meta boxes: "How to complete this publication" (field guide), "Publication Details" (location + download URL), "Key Findings (up to 3)". Template structure: reading progress bar (reuses `blog-single.js`), nav, breadcrumb (Resources → [Case Studies|White Papers] → Title), publication header (content-type pills, H1, excerpt-as-lead, date/reading-time/location meta row), 21:9 hero image with `--radius-photo`, key-findings dark card (conditional on at least one stat value), two-column body layout (sticky share sidebar + `the_content()` + optional download CTA), inline share row, related publications (same taxonomy, padded to 3), pre-footer CTA, footer. New stylesheet: `styles/pub-single.css` enqueued conditionally via `is_singular('cropx_publication')` in `inc/enqueue.php`. Copy-link button reuses `id="bsingle-copy-link"` so no new JS was needed.

- **Hero/segment-hero accent band animation redesign**: Replaced the pseudo-element `repeating-linear-gradient` ripple approach on `.hero-band` and `.sgh-border` with a smoother `background-size: 400% 100%` + `background-position` keyframe sweep. The gradient uses only two colors: the accent token and a slightly darker derived shade via `color-mix(in srgb, <accent> 82%, black)`. No pseudo-elements or transparent overlays — the animation is entirely on the element itself. `prefers-reduced-motion: reduce` guard on both. Keyframe names: `cropx-band-flow` (hero) and `cropx-sgh-band-flow` (segment-hero).

- **Accent band / strip height reductions**: `.hero-band` reduced from `1.875rem` → `1.275rem`. `.pf-strip` (pre-footer CTA top accent stripe) reduced from `25px` → `1.275rem`. Segment-hero `.sgh-border` left at `25px` (unchanged by design).

### Done since last update ✅ (June 26, 2026)

- **Hero band animation loop — seamless fix**: The accent-color stripe at the bottom of the hero and segment-hero blocks had a 1-frame stutter at the reset point despite using `linear` timing. Root cause: any finite `background-position` keyframe animation has a conceptual "reset" frame when it loops, and browsers can render it. True fix: replace the animation entirely with `repeating-linear-gradient` (2400px period) and animate `background-position` by exactly one full period. Because any offset by one period is pixel-identical, there is no reset frame — the loop is mathematically seamless regardless of browser or timing function. Applied to both blocks:
  - `wp-theme/cropx/src/blocks/hero/style.css` — `.hero-band::before` and `.hero-band::after`
  - `wp-theme/cropx/src/blocks/segment-hero/style.css` — `.sgh-border::before` and `.sgh-border::after`
  - Both use the same 2400px-period `repeating-linear-gradient` with transparent/semi-black stops, `strip-ripple-fwd` (7s, forward) and `strip-ripple-rev` (9s, reverse) keyframes, `linear infinite`.

- **Cards block: 1-card and 2-card centering** (`wp-theme/cropx/src/blocks/cards/style.css`): Added `:has()`-based CSS rules after the `.crd-grid` base rule. Single card is constrained to one third-column width and centered; two-card grid is constrained to two thirds-column widths plus one gap and centered; three or more cards remain standard left-aligned 3-column. Math: with `gap: 1.5rem` and 3 columns, `col_width = 33.333% − 1rem`, `two_col_width = 66.667% − 0.5rem`. Uses `margin-inline: auto` to center.

- **Five page pattern files written with real content + staging URLs**: All patterns now contain real block content (supplied by Lauren) and all `cropx-2026-2.local` references replaced with `http://ec2-100-25-145-190.compute-1.amazonaws.com`. Deployment: ZIP-upload via the WP admin. Files:
  - `wp-theme/cropx/patterns/page-homepage.php` — nav, hero (showBottomBand), segments, logo-strip, two-column, three-column-icons, cards (3 case studies), stats-grid, testimonial-single, testimonials-carousel, faq-accordion (5 real FAQs), pre-footer-cta
  - `wp-theme/cropx/patterns/page-segment-enterprise.php` — segment-hero, segments, logo-strip, two-column-overlay ×2, two-column (API), testimonial-single (Ritter Sport/Elizabeth Rizo), stats-grid, cards ×3, faq-accordion, pre-footer-cta (`segmentAccent:"enterprise"`)
  - `wp-theme/cropx/patterns/page-segment-service-provider.php` — segment-hero (`"segment":"service-provider"`), segments with page-linked body copy, hardware-lineup (6), two-column-overlay, feature-stat, two-column, testimonial-single, stats-grid, cards ×3, faq-accordion, pre-footer-cta (`segmentAccent:"service-provider"`)
  - `wp-theme/cropx/patterns/page-segment-on-farm.php` — segment-hero (`"segment":"on-farm"`, coffee plantation photo ID 441), hardware-lineup (6), two-column-overlay ×2, feature-stat (citrus yield card), testimonial-single (Kevin Matthews), two-column-alternating (4 crop categories with real photo IDs), cards ×3, stats-grid, faq-accordion, pre-footer-cta (`segmentAccent:"on-farm"`)
  - `wp-theme/cropx/patterns/page-products-hub.php` — nav, hero (no CTA, showBottomBand), product-sections (8 platform + 6 hardware items, photo placeholders), testimonial-single (showPhoto:false), cards (queryMode:"auto"), pre-footer-cta

- **Staging server established**: `http://ec2-100-25-145-190.compute-1.amazonaws.com/` — deploy by uploading the theme as a ZIP through WP Admin → Appearance → Themes.

### Done since last update ✅ (June 24, 2026)

- **Content width increased to 1280px**: `--max-w` token updated from `72rem` (1152px) to `80rem` (1280px) in both `tokens/tokens.css` and `wp-theme/cropx/styles/tokens.css` (the WordPress-active copy). Also cleaned up three stale hardcoded fallbacks that were using the old value: `nav/style.css` (×2 instances of `var(--max-w, 72rem)` → `var(--max-w)`), `segments/style.css` (same), and `segment-hero/editor.css` (`max-width: 72rem` → `var(--max-w)`). All blocks now derive width from the single token with no stale overrides. Note: the two `tokens.css` files must be kept in sync — the theme loads from `wp-theme/cropx/styles/tokens.css`, not the project-root `tokens/tokens.css`.

- **Cards block typography + spacing polish**:
  - `.crd-card-body` padding: `1.5rem` → `2.5rem 1.75rem` (top/bottom 2.5rem, left/right 1.75rem)
  - `.crd-title` font size: `clamp(1rem, 1.4vw, 1.125rem)` → `1.25rem` (fixed)
  - `.crd-excerpt` font size: `0.9375rem` → `1rem`; added `opacity: 0.9`
  - `.crd-card--dark .crd-excerpt` color: `rgba(255,255,255,0.75)` → `rgba(255,255,255,0.9)`
  - `.crd-cta` font size: `0.9375rem` → `1rem`

- **Blog post design explorations (3 HTML variations)**: Three layout directions prototyped in `blog-post-v1-hero-author.html`, `blog-post-v2-toc-sidebar.html`, and `blog-post-v3-key-insights.html`. V1 and V2 are the preferred directions. Key design decisions captured:
  - **V1 (Hero + Author Bio)**: Adds a `21:9` cinematic hero image with `--radius-photo` asymmetric radius above the article header; title and lead move below the hero; author bio card at the bottom of the article (name, title, bio blurb).
  - **V2 (TOC Sidebar + Reading Progress)**: Replaces the left share sidebar with a sticky right-column table of contents (active section highlighted). Reading progress bar at the top of the viewport via JS. Share actions move inline to the end of the article.
  - **V3 (Key Insights + Numbered Sections)**: Deep-blue stats summary card ("Key findings") at top; drop cap on first paragraph; numbered circle badges on H3 headings; topic tags at the bottom. Not a current priority.
  - **Decided**: Hero image, TOC sidebar, and Key Insights card should all be **optional/toggleable block attributes**, off by default:
    - **Hero image**: show only when a suitable photo is available.
    - **TOC sidebar**: show only when the article is long enough to warrant navigation.
    - **Key Insights card** (deep blue stats summary): useful for both blog posts and case studies. Stat count is configurable — 1, 2, or 3 stats. Each stat has a number/value field and a label field. The card should render correctly at all three counts (1-stat: single wide column; 2-stat: two equal columns; 3-stat: three equal columns as shown in V3 mockup).

- **`text-wrap: balance` applied to all H2 headings site-wide**: Ensures multi-line headings break into even line lengths rather than producing a long first line and a short widow last line. Added to `styles/shared.css` on `.section-heading` (covers stats-grid, cards, testimonials carousel, three-column icons, two-column, two-column alternating intro, pre-footer CTA, mid-page CTA, and more), plus four block-specific H2 selectors: `.fstat-h2` (feature-stat), `.seg-section-heading` (segments), `.psec-panel-heading` (product-sections), `.ptabs-panel-heading` (product-tabs). Gracefully ignored by older browsers with no visual regression.

- **`cropx/dealer-finder` block — complete**: New block (beyond the original 19), not in Phase 2 scope. Six-file dynamic block (adds `view.js` to the standard five-file pattern). Features:
  - **Split-panel layout**: 380px scrollable sidebar (list + search) + Mapbox GL JS v3.4.0 map (flex: 1). Light and dark (Deep Blue) color scheme variants, both editor-selectable. Map height and default center/zoom editable in block inspector.
  - **Custom Mapbox style**: `mapbox://styles/lhcropx/cmqllpb3d005z01rgc4qt2mvk`
  - **Search**: Zip code search via Mapbox Geocoding API (`types=postcode&country=us&limit=1`). 100-mile radius. Results sorted nearest → farthest with distance badge on each list row.
  - **Geolocation**: "Use current location" button via `navigator.geolocation.getCurrentPosition`. Hides automatically if browser doesn't support it.
  - **Map clustering**: Native Mapbox GeoJSON clustering (halo + fill + count label layers). Cluster click → `getClusterExpansionZoom` + `easeTo`. Individual pin click → popup card.
  - **Popup card**: Dealer name, address, website link, Google Maps directions link. `map.flyTo()` with `offset: [0, 120]` centers the card (not the pin) in the viewport.
  - **Sidebar list**: Clicking a row activates it (`is-active` border + bg), scrolls to the pin on the map, opens its popup. Left-padding uses `calc(1.5rem - 3px)` to compensate for 3px `border-left` so all sidebar elements left-align flush.
  - **Clear button + search pin**: Orange `df-search-pin` marker placed at the searched location. "✕ Clear" button resets to all-dealers view and removes marker.
  - **REST API endpoint**: `cropx/v1/dealers` returns all published `cropx_dealer` CPT entries as JSON with lat/lng, name, address, city, state, website. Endpoint registered in `inc/dealer-finder-api.php`.
  - **Per-block inline config**: `window.cropxDealerFinder = {...}` injected as an inline `<script>` inside the block HTML, guaranteed to execute before the footer `view.js` script.
  - **Conditional CDN enqueue**: Mapbox GL CSS + JS loaded only on pages that contain the block (`has_block()` check in `inc/dealer-finder-api.php`).
  - **Mapbox token**: stored in WordPress options via Admin → Settings → CropX (settings page added). Domain-restricted public token (`pk.` prefix). Never hardcoded in source.
  - **UI polish**: `font-variant-numeric: lining-nums tabular-nums` on the block. Button UA stylesheet override — both `.df-location-btn` and `.df-search-clear` share a consolidated rule with explicit `font-family: inherit`, `font-weight: normal`, `line-height: inherit` to defeat browser defaults. Empty-state: only the meta text line ("No dealers found within 100 miles of X") shows — list element is cleared to avoid duplicate messaging.
  - **Pending before launch**: populate `cropx_dealer` CPT with real dealer data (currently 2 test entries). Task #71 (hide non-CropX blocks from inserter) is still outstanding.

- **`cropx/segments` block — major visual redesign**: Photo-panel design iterated extensively through 14 exploration HTML files (`segments-exploration-1.html` through `segments-exploration-14.html`). Final design decisions locked in:
  - **Card shape**: `border-radius: 0 40px 0 0` (top-right curved only)
  - **Gap**: `1.75rem` between cards
  - **Darkening overlay**: two-layer — flat `rgba(20,28,55,0.25)` base scrim across the whole image (takes the edge off bright highlights) + `linear-gradient` on top (0 → 0.06 at 35% → 0.88 at 100%). In memory but not currently active: grayscale + multiply blend wash per segment accent color (can be re-enabled if needed).
  - **Aspect ratios by breakpoint** (all in `style.css`):
    - `> 1080px`: `aspect-ratio: 5 / 6` (portrait)
    - `900–1080px`: `aspect-ratio: auto` + `min-height: calc((100vw - 7.5rem) * 5 / 12)` gives a 4:5 base; CSS grid's `align-items: stretch` equalises all three cards to the height of the tallest
    - `500–900px` (single-column tab layout): `aspect-ratio: 16 / 9`, purely proportional
    - `< 500px`: `aspect-ratio: auto` + `min-height: calc(100vw - 4rem)` gives a ~1:1 square floor; card can grow beyond that if text needs more space
  - **Section padding**: `3.125rem top (~50px) / 4.375rem bottom (~70px)` (overrides global `--section-py`)
  - **Tag text**: all white across Enterprise, Service, On-Farm (previously mixed)
  - **Text styles**: `seg-panel-desc` at `0.9735rem / 1.5 line-height / opacity 0.95`; `seg-panel-cta` at `opacity 0.85`
  - **New `showSectionEyebrow` attribute** (`boolean`, default `true`): independently hides the section eyebrow ("Who we serve") without affecting the section heading. Wired in `block.json`, `edit.js` (sidebar toggle appears only when section header is visible), and `render.php`. The "Show section eyebrow" toggle is nested under "Show section header" in the sidebar so it disappears when the whole header is hidden.

- **`cropx/video` block**: Fully built and live on local WP. Five-file dynamic block. Features: white/gray/deep-blue background variants; `inline` or `lightbox` display mode (`displayMode` attribute) — lightbox opens a full-screen overlay, closes on backdrop click or ESC; YouTube/Vimeo external URL or WP media library source per video; auto-fetch YouTube `maxresdefault.jpg` thumbnail when URL field is empty; single or two-column layout with center/left alignment; collapsible per-video sidebar panels with click-to-focus (clicking canvas video opens that video's panel); section intro (eyebrow/heading/body) with show/hide toggle; per-video caption (title + description) with show/hide toggle; eyebrow color picker (CropX Blue / Deep Blue / White) — context-aware: "Deep Blue" hidden on deep-blue backgrounds, "White" hidden on light backgrounds. Deep-blue variant: white text, inverted play button, drift pattern animation injected via render.php inline `<style>` (same pattern as people-showcase). Typography: `vid-title` 1.25rem, `vid-desc` 0.925rem. Two-column breakpoint at 900px.

- **CPT architecture**: `cropx_publication` (case studies + white papers), `cropx_resource` (brochures, datasheets, reports), `cropx_dealer` (directory, Phase 2), `cropx_team_member`, `cropx_testimonial`
- **Custom fields**: `job_title` (team), `bio` (team), `quote_text` (testimonial), `attribution` (testimonial), `download_url` + media picker (resource), dealer contact fields
- **Admin editor UX**: classic editor layout for Dealer/Team/Testimonial, field guide tables on all 4 CPTs, descriptive title placeholders, admin notices on editor pages, admin sidebar reordered
- **Cards block**: `queryPostType` attribute (Publications vs Blog Posts), content type filter checkboxes
- **`cropx_url()` helper**: strips same-domain URLs to relative paths at render time across all block render.php files, nav, and menu walkers — links work correctly across local → staging → production without database search-replace
- **Nav hover gap**: fixed via `box-sizing: content-box` on `.cnav-btn`
- **Mega dropdown**: fixed via direct JS class toggle (more reliable than CSS `:has()`)
- **Repo cleanup**: removed macOS duplicate theme folders (`cropx 2/3/4`), stale zip
- **webpack.config.js**: extends default to add `src/admin/editor-panels.js` entry (Gutenberg sidebar panels for CPT meta fields)
- **Phase 3 polish backlog**: cleared
- **Hero block**: renamed "Standard Hero"; `showCta` + `showEyebrow` toggles; optional `showBottomBand` (CropX-blue stripe); editor CTA preview now matches front-end styling
- **Product Grid block**: eyebrow color picker trimmed to CropX Blue / Deep Blue / White only (segment accent colors removed)
- **Product Tabs block** (`cropx/product-tabs`): fully built and live on local WP — two-tab grid (CropX Platform / CropX Hardware) with `pg-*` horizontal cards (photo focal point + zoom + illustration overlay controls identical to product-grid), WAI-ARIA tabs pattern, Option 2 soft-underline tab strip (hairline gradient fade, `::after` scaleX underline, flush first-tab alignment, no sticky), section eyebrow/H2 removed, tab font 1.275rem, vertical padding 20px. PHP `function_exists()` guard added to `render.php` to prevent fatal error when multiple block instances appear on the same page. Static prototypes at `blocks/product-grid-tabs-nav-options.html` (5-option comparison) and `blocks/product-grid-tabs-opt2-focus.html` (final design reference).
- **Icon library**: expanded to 118 icons. Visual icon picker built into `cropx/three-column-icons` editor. New icons added with recommended naming pattern: drop SVGs into `assets/icons/`, register slug in `render.php` `$allowed_icons` array.
- **About page prototypes refined** (`blocks/about-page.html`): Video block (centered caption option), Mid-page CTA, People Showcase (three background variants, LinkedIn badge inline with name, per-card LinkedIn toggle), Values block — all prototyped with final designs.
- **People Showcase block** (`cropx/people-showcase`): fully built and live on local WP. Five-file dynamic block. Features: gray/white/deep-blue background variants, 1:1 or 9:10 photo crop, show/hide intro section with center/left alignment, per-card photo upload, name, role, LinkedIn toggle + URL. LinkedIn badge sits inline to the right of the name. Optional section subheadings: `teamMembers` is a flat mixed array of `{ type: 'member', ... }` and `{ type: 'group', label }` items — group items render as a left- or center-aligned `<h3>` above the next grid of cards. Sidebar editor: collapsible panels (click header/chevron to collapse), drag-and-drop reordering (HTML5 DnD, same `reorderByDrag` from `src/shared/reorder.js`), ↑↓ buttons, click-to-focus (clicking a canvas card scrolls to and auto-expands that card's sidebar panel). All CSS scoped to `.cropx-people-showcase`. Drift pattern URL injected via render.php inline `<style>` (can't use PHP constants in webpack-bundled CSS).

### Remaining ⬜

**Immediate / pending build:**
- Run `npm run build` + rsync to apply all block source changes (background color overhaul, two-column-video, field-photos/cards deep-blue, resource-downloads) to the local WP install. `content.css` change is static and only needs rsync.

**In progress / next up:**
- **Product page template** (Task #131)
- **Cookie consent banner** (Task #132) — HTML mockup in progress
- **404 error page** (Task #133) — HTML mockup in progress
- **Blog post import research** from old CropX Elementor site (Task #134) — in progress
- **Staging deploy**: run build + rsync + create `segment-navigation` Synced Pattern on staging (Task #136)
- **Case Studies & Testimonials archive page** (Task #135)

**Near-term:**
- **Values block** — not yet built; needed before About page `two-column` sections can use real content
- **Publications archive page** — case studies + white papers listing; no template yet
- **Hardware product page template** — sensors and physical hardware
- **Software product page template** — farm management app and platform software
- **Solutions pages** — after product pages
- **Contact page**

**Deferred / low priority:**
- **Dealer data population**: only 2 test entries in `cropx_dealer` CPT; needs real dealer records before launch
- **Task #71**: hide non-CropX core blocks from editor inserter via `allowed_block_types_all` filter (long-standing, low priority)
- **Content entry testing**: add a few test entries to each CPT, verify blocks pull correctly
- **Block/pattern screenshot thumbnails**: grab screenshots for the WP block browser preview images
- **Publication template decision**: decide on one vs. two templates (single `single-cropx_publication.php` vs. separate Case Study / White Paper templates)
- **Blog migration**: 30 priority posts (Week 7)
- **Pre-launch technical**: redirects, sitemaps, hreflang, Core Web Vitals (Week 8)
- Switch back to multisite (deferred until closer to launch)
- Internal staging review
- Production go-live (July 31)

---

## WordPress-only Blocks (beyond the static library)

These blocks were built directly as Gutenberg blocks with no corresponding static HTML file in `blocks/`. They extend the original 20 with designs developed entirely in the WordPress editor.

| Block | Name | Notes |
|---|---|---|
| `cropx/hero-curved` | Segment Hero — Curved Edge | Full-bleed segment hero with animated quadratic-bezier SVG swoop. Nav + segment badge included. Auto-detects swoop fill from next block via `view.js`. Device + phone PNGs with 6-axis position/scale controls. Segments: enterprise / service-provider / on-farm. |
| `cropx/hero-curved-standard` | Standard Hero — Curved Edge | Like `hero-curved` but nav-free (nav comes from header.php). No badge. CropX Blue added as a 4th accent option and set as default. Full segment enum: cropx / enterprise / service-provider / on-farm. |
| `cropx/dealer-finder` | Dealer Finder | Split-panel locator with Mapbox GL JS, zip search, geolocation, 100-mi radius, clustering, sidebar list. See June 24 entry for full feature list. |
| `cropx/video` | Video | Inline + lightbox modes, YouTube auto-thumbnail, deep-blue variant with drift animation, single/two-column layout. See June 24 entry for full feature list. |
| `cropx/people-showcase` | People Showcase | gray/white/deep-blue variants, 1:1 or 9:10 crop, optional group subheadings, LinkedIn toggle, DnD reorder. See June 26 entry for full feature list. |
| `cropx/mid-page-cta` | Mid-Page CTA | Inline CTA section with eyebrow color picker. |
| `cropx/newsletter-cta` | Newsletter CTA | Email signup CTA. Background variants: white (default) / taupe / deep-blue (with animated topo drift). Icon-box system (56×56px). Form input and button styles match contact-form. Auto-included on blog archive and singles via home.php + single.php with taupe bg. |

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
