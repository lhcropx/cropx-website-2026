# Project Progress & Handoff

Full state of the CropX website rebuild as of **August 2, 2026**. Use this as a context primer for any new Claude session so we never lose progress.

<!-- last updated: August 6, 2026 -->

## August 6, 2026 — Platform mega-menu: added a "Products" entry point above Hardware/Software

Lauren wanted a way for visitors to reach a page listing all products, with Hardware and Software nested clearly beneath it in the mega-menu's hierarchy — plus removal of the (non-clickable) "Platform Overview" column from this menu entirely. Explored via mockups first (3 structural options: spanning label, dedicated column, bounding container), she picked the spanning-label option, then asked for 3 styling variations on the "View all products" CTA (hero-underline, footer-CTA, outlined-button), and landed on: the heavy full-width rule + top-row CTA placement (outlined-button variation's structure) but with the CTA styled as a plain text link (footer-CTA variation's link styling — no border/button chrome).

**Implementation is entirely in `inc/parts/nav.php` — `inc/menus.php`'s `CropX_Platform_Walker` is untouched.** Every top-level group in the `cropx-platform` WP menu (Hardware, Software, etc.) already rendered as its own `.cnav-mega-group` div via the walker; rather than rewriting the walker to add a 3rd depth level, `wp_nav_menu()`'s `items_wrap` argument is overridden per-call to wrap the walker's existing (unchanged) output in a new shared header:

```
.cnav-mega-products
  .cnav-mega-products-header   (flex row, 2px Deep Blue bottom rule)
    a.cnav-mega-heading.cnav-mega-products-heading  ("Products", reuses .cnav-mega-heading's exact type styles, just made clickable)
    a.cnav-mega-products-cta   ("View all products", plain text link — cropx-blue, 600 weight, no border, underline on hover)
  .cnav-mega-products-cols     (2-col grid — Hardware | Software, the walker's existing .cnav-mega-group output goes here unchanged)
```

`.cnav-mega-inner`'s `grid-template-columns: repeat(4, 1fr)` was removed (it now has exactly one child — `.cnav-mega-products` — instead of one column per top-level group) but its `max-width`/`margin`/`padding` are untouched. All the item-level styling Lauren didn't ask to change — `.cnav-mega-group`, `.cnav-mega-heading`, `.cnav-mega-link-title`, `.cnav-mega-link-desc`, the gray-200 hairline dividers — is completely unmodified.

Mobile gets the same idea at a smaller scale: a single "View all products" link (`.cnav-mobile-view-all`, plain cropx-blue text) is prepended above the existing Hardware/Software groups in the mobile Platform accordion via the same `items_wrap` technique — no structural change to the mobile groups themselves.

The "Products" URL isn't a real menu item (there's no live Products page yet), so it's hardcoded as `cropx_url('/products/')` in `nav.php` with a comment flagging it to be swapped once the actual page/slug exists.

**Lauren needs to delete "Platform Overview" from the `cropx-platform` menu in wp-admin (Appearance → Menus)** — she chose to remove it from this menu entirely rather than keep it as its own column. Deleting it is a normal menu edit, not a code change. `inc/menus.php`'s doc comment was updated to warn against re-adding a similar top-level item here, since there's no longer anywhere in the layout for an unrelated top-level group to render.

Verified via the scoped/chunked build (both `nav` and `segment-hero`, since segment-hero also renders this same nav partial): new `.cnav-mega-products*` classes and the updated `.cnav-mega-inner` rule compiled correctly into `style-index.css`; `.cnav-mobile-view-all` present; both blocks' JS compiled clean.

**This patch is shaped differently from prior overlay-block patches** — it touches `inc/menus.php` and `inc/parts/nav.php` (plain theme PHP, not part of any block's build folder) in addition to `build/blocks/nav/` and `build/blocks/segment-hero/`. Shipped as `nav-products-patch-0806a.zip`, structured to extract directly at the theme root (`wp-content/themes/cropx/`) rather than inside `build/blocks/<name>/` — upload and extract it there in File Manager, not inside the `build/blocks/` folder like the two-column-overlay patches.

**No attribute/schema changes** — this isn't a Gutenberg block attribute, so there's nothing to re-insert on existing pages. The mega-menu's content is still fully wp-admin-menu-driven (Appearance → Menus → cropx-platform).

---

## August 5, 2026 — Two-Column + Overlay: rounded-corners toggle converted to a 0-20px slider

Tenth same-day round. Lauren asked for the "Rounded corners" toggle (shipped last round) to become a slider ranging 0-20px instead of an on/off switch.

Replaced the `overlayRoundedCorners` boolean attribute with `overlayCornerRadius` (number, default 10, 0-20) and swapped the sidebar `ToggleControl` for a `RangeControl` ("Corner rounding (px)"). Since this is now a continuous value rather than a binary state, dropped the `.tco-overlay--rounded` fixed modifier class entirely — `border-radius` is now applied unconditionally on the base `.tco-overlay` rule via `border-radius: var(--tco-overlay-radius)`, with `--tco-overlay-radius` written inline (in both `render.php` and `edit.js`'s canvas preview) alongside the existing `--tco-overlay-scale`/`--tco-overlay-ratio` custom properties. 0px on the slider just means "no rounding" — no separate on/off state needed.

Built via the scoped/chunked config; verified `overlayCornerRadius: default 10` in the compiled `block.json`, `border-radius:var(--tco-overlay-radius)` in the compiled `style-index.css`, the radius logic in `render.php`, and confirmed zero leftover references to `overlayRoundedCorners`/`.tco-overlay--rounded` anywhere in the compiled output. Shipped `two-column-overlay-patch-0805k.zip` (supersedes `-0805j.zip`).

**Attribute renamed** (`overlayRoundedCorners` → `overlayCornerRadius`) — existing placed instances will silently fall back to the new default of 10px regardless of what the old toggle was set to (WordPress has no record of the old boolean under the new key). This only matters for instances where the toggle had been explicitly switched off; anyone who wants 0px rounding on an existing block will need to reopen it and drag the slider down after this patch goes up.

---

## August 5, 2026 — Two-Column + Overlay: optional 10px rounded corners on the overlay PNG

Ninth same-day round, small and independent of everything above. Lauren asked for a simple cosmetic option: all 4 corners of the overlay PNG rounded by 10px, as a toggle, on by default.

Added a new `overlayRoundedCorners` boolean attribute (default `true`) and a `.tco-overlay--rounded { border-radius: 10px }` modifier class — applied directly to `.tco-overlay` itself (safe since it's a plain background-image div with no nested `<img>`, so `border-radius` clips the box's own background without needing `overflow: hidden`). Orthogonal to position/full-bleed/size — just an extra class appended alongside whichever of those already apply. `edit.js` got a `ToggleControl` ("Rounded corners") right below the Size control in the Overlay PNG panel; `render.php` mirrors the same logic server-side.

Built via the scoped/chunked config; verified `overlayRoundedCorners: default true` in the compiled `block.json`, the `.tco-overlay--rounded{border-radius:10px}` rule in `style-index.css`, and the class wired into both `render.php` and the compiled `index.js`. Shipped `two-column-overlay-patch-0805j.zip` (supersedes `-0805i.zip`).

**No re-insertion needed for existing instances** — new boolean attribute with a sensible default; WordPress fills it in automatically for blocks saved before this attribute existed.

---

## August 5, 2026 — Two-Column + Overlay: no-crop fix, editor sidebar layout fix, and new block-edge bleed spacing

Eighth same-day round, on top of the full-bleed toggle below. Lauren reviewed the built editor panel and sent three corrections:

**1. Full-bleed overlay must never crop.** She walked back the "crop to fill" decision from the previous round: "this should NEVER result in the overlay PNG being cropped. It should only fit within the exact dimensions of its frame." Fix was a one-line CSS change — `.tco-overlay--full-bleed`'s `background-size` went from `cover` back to `contain`. The frame itself is unchanged (locked height via top+bottom, Scale-driven width), but now the image is only ever shown in full within that frame — letterboxing (empty space) if the frame's aspect ratio doesn't match the image's own, rather than cropping to fill it. Updated the Size control's help text in `edit.js` to match (removed the "crops to fill" language), and updated the doc comments in `style.css` and `render.php` that described the old cropping behavior.

**2. Editor sidebar layout collision.** Screenshot showed the `OverlayCompassPicker`'s bottom-row buttons (↙ ↓ ↘) visually overlapping the "Also hang off top & bottom" toggle directly below it. Root cause: the compass buttons are positioned at 0%/50%/100% with `translate(-50%, -50%)`, so any edge-row button sits HALF outside the box itself (an 11px overflow — half of the 22px button) — that overflow wasn't accounted for in the box's own layout footprint, so whatever followed (the toggle) started too close beneath it. Fix: wrapped the compass box in a padded container (`padding: 11px 11px 21px`) in `edit.js` so the overflow is absorbed into the wrapper's own box model instead of escaping into the next element's space.

**3. New block-edge bleed spacing.** Lauren's third ask: "If the overlay PNG bleeds over the top edge of the background photo at all, then 40px of additional space needs to be added between the top edge of the overlay PNG and the top edge of the block" — and symmetrically for the bottom. Implemented as two new modifier classes on `.tco-section` itself: `.tco-section--bleed-top` (`padding-top: calc(var(--section-py) + 2.5rem)`) and `.tco-section--bleed-bottom` (same for `padding-bottom`), added in addition to the section's normal padding — not a minimum/cap, an explicit extra 40px. `render.php` computes `$overlay_bleeds_top`/`$overlay_bleeds_bottom` (true for top/top-left/top-right positions, or bottom/bottom-left/bottom-right, or whenever `overlayFullBleed` is active since that always hangs both top AND bottom regardless of left/right); `edit.js` mirrors the same logic for the live canvas preview, computed before `useBlockProps` now (moved the whole overlay-computation block earlier in the component so these booleans are available when building `blockProps`'s className). No explicit mobile reset needed — the existing mobile `.tco-section { padding: 4rem 1.5rem }` rule already wins via source order (equal specificity, appears later in the file), exactly like the existing full-bleed/nudge mobile resets.

Built via the scoped/chunked config; verified `background-size:contain` (no `cover` anywhere) and the new `.tco-section--bleed-top/-bottom` padding rules compiled into `style-index.css`, the bleed class strings present in the compiled `index.js`, and sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805i.zip` (supersedes `-0805h.zip` and all earlier same-day zips for this block).

No attribute schema change this round (no new/removed/renamed attributes) — existing placed instances do NOT need re-inserting for this patch, unlike every round before it today.

---

## August 5, 2026 — Two-Column + Overlay: "also hang off top & bottom" toggle for Left/Right, plus text-column nudge when the overlay bleeds toward it

Seventh same-day round, on top of the compass-grid rebuild below. Lauren asked for two more things: (1) the ability for an overlay hanging off Left or Right to ALSO hang off the top and bottom simultaneously (so it bleeds off 3 edges at once, not just 1), and (2) when the overlay bleeds off the edge nearest the text column, the text's near boundary should step 40px clear rather than let the overlay cut into it. Mocked up two ways the first part could interact with Size (locked native proportions vs. crop-to-fill) before writing code; she picked crop-to-fill, then asked to confirm the existing 8 positions + their Size control were untouched before building — confirmed via a full panel mockup, then built for real.

**"Also hang off top & bottom" (new `overlayFullBleed` boolean attribute):** only shown/meaningful when Position is Left or Right. When on, the overlay also hangs 40px off the top and bottom — CSS achieves this by setting both `top: -2.5rem` and `bottom: -2.5rem` together (letting the browser compute height automatically as photo height + 80px) instead of the `height: calc(scale%)` used everywhere else. Since height is no longer Scale-driven, Scale now controls WIDTH directly (0-100% of the photo's own width) via the new `.tco-overlay--full-bleed` modifier class, and the image crops to fill (`background-size: cover`) since the box no longer matches the overlay's native aspect ratio. Nicely, no dynamic Size cap is needed for this combination at all (unlike the 6 non-full-bleed side-touching positions) — at Scale=100% the far edge lands exactly 40px inside the photo's opposite edge, satisfying the minimum-gap rule right at its boundary rather than needing to stay under it, and any lower Scale only adds more room.

**Text-column nudge (new `.tco-content--nudge-left` / `--nudge-right` modifier classes):** independent of the full-bleed toggle — applies to any of the existing positions that hang toward the text column. Since `photoPosition: 'left'` means text sits on the right (threatened by a right-hanging overlay) and the default `'right'` means text sits on the left (threatened by a left-hanging overlay), `render.php` computes this from the (photoPosition, overlayPosition) pair and adds 2.5rem of padding on the threatened side only — the far boundary of the text column stays put. Reset to 0 inside the mobile media query, since columns stack there and the side-by-side overlap this exists to prevent doesn't happen anymore.

Implementation touched all four files: `block.json` (new `overlayFullBleed` boolean, default false), `edit.js` (toggle control shown conditionally, `isFullBleed`/`contentNudgeClass` computed and mirrored into the live canvas preview), `render.php` (same logic server-side, feeding the `tco-overlay--full-bleed` and `tco-content--nudge-*` modifier classes), `style.css` (the new modifier class plus a mobile override that restores `aspect-ratio`/`background-size:contain` for full-bleed once `.tco-photo` disappears, and a nudge-reset in the same breakpoint).

Built via the scoped/chunked config, verified the full-bleed and nudge CSS compiled correctly on both the desktop and mobile-override sides, `overlayFullBleed` present in `block.json`, matching logic present in `render.php`, sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805h.zip` (supersedes all seven earlier same-day zips for this block).

**Attribute schema changed again** (added `overlayFullBleed`) — per the standing CLAUDE.md gotcha, existing placed instances need re-inserting/reconfiguring after this patch goes up.

---

## August 5, 2026 — Two-Column + Overlay: overlay positioning rebuilt around 8 fixed hang presets + a compass-grid picker

Sixth same-day round — a full reset of the requirements, not another patch on the free-position model. After the X-axis bug (previous entry below), Lauren stepped back and specified exactly what she actually needed: the overlay must ALWAYS hang 40px off some edge or corner of the photo — never floating free — with exactly 8 valid positions (left/right/top/bottom edges, each centered on the perpendicular axis; plus all 4 corners), each requiring the overlay stay at least 40px from the *opposite* edge/corner too. Mocked up two editor-UI directions first (a "compass grid" of 8 direction buttons on the photo preview vs. a "ghost preview" showing all 8 possible outcomes at once) before writing any code; she picked the compass grid.

This turned out to be a much cleaner model than the one it replaces, and actually eliminates the class of bug from the previous round rather than patching around it. Replaced `overlayX`/`overlayY` (free rem coordinates) with a single `overlayPosition` attribute — an enum of the 8 presets, no "centered/contained" 9th option since every preset must hang off something. Key realization while implementing: the fixed 40px hang and the required 40px minimum gap from the far side are the *same constant*, so they cancel out algebraically to one clean rule — the overlay's width can never exceed the photo's own width (for any preset touching a left/right side; top/bottom-only hangs have no such constraint since height alone governs Size and is already bounded at 100%). Because both the overlay's width and the photo's width scale together with the same fluid column width, that rule reduces to a pure ratio check — `125 / overlayRatio` where 125 = 100 / 0.8 and 0.8 is `.tco-photo`'s fixed 5:4 aspect-ratio as height/width — with no live measurement needed at all. That's a big simplification over the previous round's `ResizeObserver`-measured-photo-dimensions approach, and it's resolution-independent by construction, so there's no more risk of a range collapsing to zero on some viewport width.

Implementation:
- **`block.json`**: removed `overlayX`/`overlayY`; added `overlayPosition` (string enum, default `left`).
- **`edit.js`**: removed the entire `ResizeObserver`/`photoRef`/`photoWidthRem`/`photoHeightRem` measurement block (no longer needed — the width-cap math is now a pure ratio), removed the 6x6 `OverlayPositionGrid` and its numeric X/Y fields, added `OverlayCompassPicker` — 8 real `<button>` elements (Unicode arrow glyphs, no icon-font dependency) positioned at the corners/edge-midpoints of a photo-aspect-ratio preview box, keyboard-accessible for free since they're real buttons. `dynamicSizeMax` is now just `125 / overlayRatio` gated on whether the selected position touches a left/right side.
- **`render.php`**: mirrors the same width-cap formula server-side (defense-in-depth, and safe to do now since it's viewport-independent), outputs a `tco-overlay--{position}` modifier class instead of inline `top`/`left`/`x`/`y` custom properties.
- **`style.css`**: replaced the `clamp()`/`calc()` boundary math entirely with 8 simple modifier classes (`.tco-overlay--left`, `--right`, `--top`, `--bottom`, `--top-left`, `--top-right`, `--bottom-left`, `--bottom-right`), each just a fixed `2.5rem` (40px) offset on the relevant side(s) plus a `translate()` for centering edge presets on their perpendicular axis. Also added explicit `top/left/right/bottom/transform` resets in the mobile media query's `.tco-overlay` rule, since a modifier class is always present alongside it now and has equal specificity.

Built via the scoped/chunked config, verified all 8 modifier classes compiled into CSS, zero leftover `clamp(-2.5rem` formulas, `render.php` reads `overlayPosition` (no `overlayX`/`overlayY` left), `block.json`'s new enum attribute present, compass arrow glyphs present in compiled JS, sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805g.zip` (supersedes all six earlier same-day zips for this block).

**Attribute schema changed again** (removed `overlayX`/`overlayY`, added `overlayPosition`) — per the standing CLAUDE.md gotcha, existing placed instances need re-inserting/reconfiguring after this patch goes up.

---

## August 5, 2026 — Two-Column + Overlay: fixed X axis barely moving for wide overlay PNGs (Size slider now dynamically capped)

Fifth same-day round. Lauren found that on a wide/landscape overlay PNG (a dashboard-card screenshot), the X position grid and fields barely moved — every click kept landing back near the leftmost value — unless she turned Size down a lot first. Root cause wasn't a click-target bug: it's the two hard rules from earlier rounds mathematically fighting each other for wide images. Size 100% means "overlay height == photo height," but the photo has a fixed 5:4 shape — so a much-wider-than-5:4 overlay's width balloons far faster than the photo's width does as Size increases. Once that width exceeds the photo's width plus the 80px total overshoot budget (40px each side), there is no legal X position left at all, so the X range collapses to a single pinned value. This only affects landscape overlays at high Size; portrait/near-square ones were never affected.

Presented Lauren three ways to resolve the conflict — (1) dynamically cap how high the Size slider can go so it never drags into that zero-range zone, (2) relax the 40px cap when unavoidable, or (3) redefine what "Size" means around the overlay's longer side instead of always height. She chose #1, and asked that #3 stay easy to swap in later if #1 doesn't feel right in practice.

Implemented in `edit.js` only (no `render.php`/`style.css`/`block.json` changes — this is purely about what values the editor's Size slider offers, not how the front-end enforces the existing rules): added a `dynamicSizeMax` calculation derived from the overlay's real aspect ratio, the live-measured photo dimensions, and a 1rem `MIN_TRAVEL_REM` floor (guarantees some real sideways room rather than capping exactly on the degenerate point). The Size `RangeControl`'s `max` now uses this value instead of a flat `100`, with help text that explains the cap only when it's actually active (`Capped at N% for this image's proportions…`) — for normal-shaped overlays `dynamicSizeMax` resolves to 100 and nothing changes.

To keep Approach 3 easy to swap in later per Lauren's ask, the code carries a matching comment block right above the `dynamicSizeMax` calculation spelling out the exact replacement formula and which lines to touch — reverting is a small, self-contained edit rather than an archaeology exercise.

Built via the scoped/chunked config, verified `dynamicSizeMax` compiled in (visible post-minification as the mangled var feeding the `Capped at ${…}%` string), sanity-checked the formula against the actual reported scenario in Node (a 2.6:1 ratio overlay against a 32x25.6rem photo caps at 54% and leaves ~1rem of real X travel, matching the guarantee), sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805f.zip` (supersedes all five earlier same-day zips for this block).

---

## August 5, 2026 — Two-Column + Overlay: replaced the X/Y sliders with a 6x6 click-to-anchor position grid

Fourth same-day round on this block. Before building, mocked up the concept as a quick HTML preview (using the visualize/widget tool, not real code) so Lauren could react to the UX before committing — tried a 3x3 grid first, then 8x8, then landed on 6x6 (49 snap points) as the sweet spot between too coarse and too dense.

Replaced the "Horizontal position — X (rem)" / "Vertical position — Y (rem)" `RangeControl`s in `edit.js` with a new `OverlayPositionGrid` component: a clickable preview box (same 5/4 aspect ratio as the photo) with dashed lines at 1/6 intervals and 49 dots at every intersection. Clicking anywhere snaps to the nearest of the 49 points and sets `overlayX`/`overlayY` to that point's value along the existing `xMin..xMax` / `yMin..yMax` range — the same range the old sliders used, so the 40px-overshoot ceiling from the previous round is untouched, just picked visually instead of by dragging a slider blind. The nearest dot to the current position is highlighted in CropX Blue so the control shows state, not just accepts input.

Kept a pair of small numeric "X (rem)" / "Y (rem)" fields below the grid for exact fine-tuning and as the keyboard-accessible path (the grid itself is click/mouse-only — flagged as a known limitation, worth revisiting with arrow-key support if this becomes a heavily-used control).

This was a pure `edit.js` change — `overlayX`/`overlayY` still mean exactly what they meant before (an absolute rem offset from the photo's top-left corner, clamped by the same CSS in `style.css`), so `render.php`, `style.css`, and `block.json` were untouched this round; no new attribute-schema migration needed for already-placed instances.

Built via the scoped/chunked config (CSS unchanged at 4.5KB), verified the grid code compiled in, old slider help text fully gone, sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805e.zip` (supersedes the four earlier same-day zips for this block).

---

## August 5, 2026 — Two-Column + Overlay: re-added a Size control and enforced a strict 40px overshoot cap on the X/Y sliders

Third same-day round on this block's overlay positioning (see the two entries below for the earlier X/Y redesign and the mobile orientation-aware cap). Lauren asked for two more constraints on top of the free X/Y placement: (1) a way to resize the overlay again — capped so it can never exceed the background photo's own height — and (2) a hard rule that the overlay can never sit more than 40 *pixels* (explicitly not rems) past any of the photo's four edges, checked independently per side rather than as a combined/diagonal distance.

Re-added a "Size (%)" slider (`overlayScale`, 10–100, default 75) to the Overlay PNG panel, where 100% means the overlay's rendered height exactly equals the photo's rendered height. Converted the 40px rule to a fixed `2.5rem` constant (`OVERSHOOT_REM`) rather than anything viewport-dependent, since the site's root font-size is a fixed 16px everywhere — so 40px = 2.5rem always, no dynamic px→rem conversion needed.

The actual enforcement moved out of `edit.js`/`render.php` and into CSS itself, so front-end and editor share exactly one formula and it self-adjusts to any viewport without server-side guessing:
- `.tco-overlay`'s `height` is now `calc(var(--tco-overlay-scale) * 1%)` — a percentage of `.tco-visual`'s own height, which always equals the photo's aspect-ratio-driven height (the overlay is absolutely positioned, so it never contributes to that ancestor's auto-height calc). Width still follows from `aspect-ratio: var(--tco-overlay-ratio)`.
- `top`/`left` are now `clamp(-2.5rem, var(--tco-overlay-y|x), calc(100% + 2.5rem - <overlay's own size in that axis, as a %>))` — enforcing the 40px ceiling on all four sides independently. The X-axis ceiling has to convert the overlay's height-based size into a width percentage, which routes through `.tco-photo`'s hardcoded `aspect-ratio: 5/4` (baked in as a literal `0.8` factor in the calc — documented with a comment in `style.css` since it's not itself a variable, and will need updating by hand if that aspect-ratio ever changes).
- `render.php`/`edit.js` now write only raw custom properties (`--tco-overlay-x/-y/-scale/-ratio`) inline — no more literal `top`/`left` — so all the boundary math lives in exactly one place (`style.css`).

The editor's Size/X/Y `RangeControl`s get matching dynamic bounds computed the same way in `edit.js`, using the existing `ResizeObserver` (already measuring the live-rendered `.tco-photo`, extended this round to track width in addition to height). `overlayX`'s default shifted from `-4` to `-2.5` to land exactly on the new left-edge boundary rather than slightly past it.

Built via the scoped/chunked config (CSS came in at 4.5KB, no bloat-warning risk), verified all four `--tco-overlay-*` custom properties present in compiled CSS/PHP, zero literal `top:`/`left:` rem strings left in `render.php`, sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805d.zip` (supersedes the three earlier same-day zips for this block) — upload directly into `wp-content/themes/cropx/build/blocks/two-column-overlay/` in WP File Manager and extract in place.

**Attribute schema changed again** (added `overlayScale`, `overlayX` default shifted) — per the standing CLAUDE.md gotcha, existing placed instances need re-inserting/reconfiguring after this patch goes up.

---

## August 5, 2026 — Two-Column + Overlay: redesigned overlay positioning from anchor/bleed to free X/Y placement, plus closed out its CSS-bloat risk

Lauren asked for more direct control over where the overlay PNG sits on "2 Columns with Text & Product Illustration" (`two-column-overlay`). Removed the "Overlay position" dropdown (top/center/bottom) from the Photo Positioning panel entirely, and replaced the "Inner edge anchor (%)" and "Horizontal bleed (rem)" sliders — previously in a separate "Overlay sizing" panel — with two new sliders, "Horizontal position — X (rem)" and "Vertical position — Y (rem)", moved into the Overlay PNG panel directly below the Replace/Remove buttons as requested.

The old model positioned the overlay indirectly via two opposing CSS edge-offsets (`right: anchor%; left: -bleedX rem`), with width auto-resolving from the gap between them and height derived from a fixed `aspect-ratio: 7/5`. The new model sets `top`/`left` directly on the overlay div — an absolute rem offset from the photo's own top-left corner — with width now fixed at 85% of the visual column in CSS (no longer a slider, since removing anchor+bleed removed the only mechanism that controlled size; positioning was the explicit ask, so size is a static default for now — flag if a size control turns out to be wanted too). One real behavior change worth knowing: the old anchor/bleed model auto-mirrored when switching "Photo position" between left/right; the new X/Y coordinates are always measured from the photo's actual top-left corner regardless of layout, so flipping photo-left/right no longer auto-flips the overlay's position — X will need to be manually adjusted to compensate.

On the slider bounds ("-6rem to image height + 6rem"): the photo has no fixed height of its own — it's driven by a fixed `aspect-ratio: 5/4` against a fluid grid-column width, so it's a genuinely dynamic number that changes with viewport width and can't be hard-coded. Solved by measuring the actual rendered `.tco-photo` element's height live in the editor via `ResizeObserver` (no need to wait for the image itself to load — aspect-ratio determines the box's height from width alone, independent of the underlying asset), converting to rem, and using `measuredHeight + 6` as both sliders' live-updating max. The front-end doesn't need this at all — the chosen rem values are just baked into the block's saved attributes and applied directly as inline `top`/`left`, no slider involved.

While rebuilding, this block was also still on the drift-pattern.svg CSS-bloat "not yet fixed" list (its deep-blue background variant carries the same animated topo overlay). Applied the standard fix — `--tco-pattern-url` injected via `render.php`'s wrapper style (only when `bgColor === 'deep-blue'`) and `edit.js`'s `useBlockProps` style. CSS dropped from 124KB to 4.3KB.

Built via the scoped/chunked config, verified `overlayX`/`overlayY` present and `overlayAnchor`/`bleedX`/`overlayPosition` fully gone from compiled output, `--tco-pattern-url` present, zero base64 SVGs, sibling blocks' timestamps unchanged. Shipped `two-column-overlay-patch-0805.zip`.

**Attribute schema changed** (removed `overlayPosition`/`overlayAnchor`/`bleedX`, added `overlayX`/`overlayY`) — per the standing gotcha in CLAUDE.md, any existing instances of this block already placed on live pages will need to be re-inserted/reconfigured after this patch goes up, since WordPress doesn't migrate stored attributes across schema changes.

**Fixed so far (drift-pattern CSS-bloat sweep):** two-column-video, hero-curved-standard, testimonial-single, hero-curved, dealer-finder, two-column-overlay. **Still carrying the base64 drift-pattern risk, not yet fixed:** segments, hero-blog, feature-stat, un-goals-two-column, logo-strip, faq-accordion, icon-columns, segment-hero, three-column-icons, stats-grid, job-openings, hardware-lineup, product-grid, pre-footer-cta, testimonials-carousel, two-column-alternating, hero, un-goals-nav, two-column.

**Same-day follow-up:** Lauren also asked for a mobile-specific treatment on this block: below 769px viewport width, the background photo should disappear entirely and the overlay PNG becomes the sole visual (rather than the old desktop model of overlay-layered-on-photo, which doesn't translate to narrow viewports). Implemented in the `@media (max-width: 768px)` block — `.tco-photo { display: none; }`, and `.tco-overlay` switches from `position: absolute` (the desktop X/Y model, meaningless without a photo to anchor to) to `position: static; width: 100%; max-width: 500px; margin: 0 auto;`. Since the overlay is now back in normal document flow instead of absolutely positioned, `.tco-visual`'s height is now correctly driven by the overlay itself rather than collapsing to zero once the photo's gone.

**Second same-day correction:** Lauren clarified the 500px mobile ceiling should apply to whichever dimension is the overlay's "long side," not always to width — landscape PNGs (wider than tall) should be height-capped at 500px with width following proportionally; portrait PNGs (taller than wide) should stay width-capped at 500px as originally built. This required knowing each overlay's *real* aspect ratio rather than the block's hardcoded `7/5` — added `overlayWidth`/`overlayHeight` attributes (captured from `media.width`/`media.height` in `edit.js` on selection, and refreshed every render in `render.php` from `wp_get_attachment_image_src()` so pre-existing instances self-heal without needing a re-save). These feed a `--tco-overlay-ratio` custom property that now drives the CSS `aspect-ratio` everywhere (replacing the old hardcoded `7/5`), and the mobile cap became `max-width: calc(500px * max(1, var(--tco-overlay-ratio)))` — one formula that reduces to a straight 500px width-cap for portrait/square images (ratio ≤ 1) and scales up proportionally for landscape ones so the *height* lands at 500px once aspect-ratio derives it from that width. Rebuilt, verified the calc()/max() formula and ratio custom property compiled correctly, shipped as `two-column-overlay-patch-0805c.zip` (supersedes both earlier same-day zips).

---

## August 5, 2026 — Dealer Finder: fixed page-jump-to-top/bottom bug on second pin click, plus closed out its CSS-bloat risk

Lauren reported that clicking a second dealer location in the sidebar list (after already clicking a first one) would yank the whole page to its very top or bottom, away from the map — first click always worked fine. Root cause was in Mapbox GL JS itself, not our own code: `mapboxgl.Popup` defaults `focusAfterOpen` to `true`, which auto-focuses the first focusable element inside the popup (our close button, or one of the tel:/mailto:/website/directions links) the instant it's added to the map. Because the popup lives inside the Mapbox canvas rather than inside our scrollable `.df-list` sidebar, the browser's native "scroll the newly-focused element into view" has no local scroll container to target, so it scrolls the top-level document instead — landing wherever that particular popup happens to sit relative to the current scroll position, which is why the direction (top vs. bottom) seemed random and only showed up reliably from the second click onward. Confirmed there's no `.focus()` call anywhere in our own `view.js` — this is entirely a Mapbox default. Fix: added `focusAfterOpen: false` to the `new mapboxgl.Popup(...)` call in `activateDealer()`. Keyboard Tab navigation into the popup still works; only the automatic focus-on-open is disabled.

While rebuilding, the webpack entrypoint-size warning flagged `dealer-finder/style-index.css` at 128KB — this block was still on the drift-pattern.svg base64-bloat "not yet fixed" list from the sweep tracked below (its dark color-scheme variant has the same animated topo overlay as the other affected blocks). Applied the same standard fix: `.df-scheme-dark::before`'s `background-image` now reads `var(--df-pattern-url)`, injected via `render.php`'s wrapper `style` attribute (only when `colorScheme === 'dark'`) and `edit.js`'s `useBlockProps` style. CSS dropped to 8.6KB, zero base64 SVGs left in it.

Built via the scoped/chunked config, verified `focusAfterOpen` present in compiled `view.js`, `--df-pattern-url` present in `render.php`/`style-index.css`/editor `index.js`, zero base64 SVGs, and sibling blocks' timestamps unchanged. Shipped `dealer-finder-patch-0805.zip` — upload directly into `wp-content/themes/cropx/build/blocks/dealer-finder/` in WP File Manager and extract in place.

**Fixed so far (drift-pattern CSS-bloat sweep):** two-column-video, hero-curved-standard, testimonial-single, hero-curved, dealer-finder. **Still carrying the base64 drift-pattern risk, not yet fixed:** segments, hero-blog, two-column-overlay, feature-stat, un-goals-two-column, logo-strip, faq-accordion, icon-columns, segment-hero, three-column-icons, stats-grid, job-openings, hardware-lineup, product-grid, pre-footer-cta, testimonials-carousel, two-column-alternating, hero, un-goals-nav, two-column.

---

## August 5, 2026 — App/software image zoom raised to 400% on both curved-edge heroes, plus closed out `hero-curved`'s CSS-bloat risk

Lauren asked to raise the "App/Software image (bleeding past the curve)" size control's cap to 400% on both `hero-curved-standard` ("Standard Hero — Curved Edge") and `hero-curved` ("Segment Hero — Curved Edge") — same family of fix as the sensor-image 500% bump on `hero-curved-standard` from August 3, just for the other overlay image and on both blocks this time.

`hero-curved-standard` already had the `maxScale` prop on its shared `MediaPanel` component from the earlier sensor fix; the app/software panel invocation just never got a `maxScale={400}` passed to it (it was defaulting to 200). Added it. `hero-curved` has its own separate local `MediaPanel` component (not shared with `hero-curved-standard` — each block defines its own copy) which didn't have the `maxScale` prop at all yet; added it the same way (default 200, `maxScale={400}` passed only on the app/software panel, device/sensor panel left untouched).

While in `hero-curved`, found it was still on the "not yet fixed" list for the drift-pattern.svg base64-bloat issue (`.hc-pattern`'s `background-image` was a relative `url()`, same as every other block before its fix). Applied the standard fix: `render.php`'s `$image_css_vars` now injects `--hc-pattern-url` via `esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' )`, `style.css`'s `.hc-pattern` reads `var(--hc-pattern-url)`, and `edit.js`'s `useBlockProps` sets the same variable via `window.cropxThemeData.themeUri` for editor-preview parity. Neither block had the earlier "hardcoded inline style clobbers the CSS variable" bug from the August 3 sensor incident — checked both blocks' `wp_get_attachment_image()` calls for the phone/app image and neither passes a `style` override, so the scale control should work correctly on first try for both.

Built both blocks via the scoped/chunked config, verified: `400` present in both compiled `index.js` files, `--hc-pattern-url`/`--shc-pattern-url` present in both `render.php`/`style-index.css`, zero base64 SVGs left in `hero-curved`'s CSS (5.6KB, down from what would've been ~127KB), and sibling blocks' file timestamps (`segments`, `hero`, `testimonial-single`) unchanged. Shipped two patch zips — `hcs-patch-0805.zip` (hero-curved-standard) and `hc-patch-0805.zip` (hero-curved) — for upload directly into their respective `wp-content/themes/cropx/build/blocks/<name>/` folders in WP File Manager.

**Fixed so far (drift-pattern CSS-bloat sweep):** two-column-video, hero-curved-standard, testimonial-single, hero-curved. **Still carrying the base64 drift-pattern risk, not yet fixed:** segments, hero-blog, two-column-overlay, feature-stat, un-goals-two-column, logo-strip, faq-accordion, icon-columns, dealer-finder, segment-hero, three-column-icons, stats-grid, job-openings, hardware-lineup, product-grid, pre-footer-cta, testimonials-carousel, two-column-alternating, hero, un-goals-nav, two-column. Still worth a proper sweep across all of them next time one comes up for edit, rather than only fixing them reactively one at a time.

---

## August 4, 2026 — Testimonial (Single): attribution color fix + pre-emptive CSS-bloat fix

Lauren reported the attribution line (name + title) under a Single Full-Width Testimonial was rendering gray instead of Deep Blue on White/Taupe-50 backgrounds. Confirmed via devtools computed styles: `.ts-name`/`.ts-title` (both plain `<p>` tags) were losing to WordPress's global-styles `:root :where(p)` rule — same specificity (0,1,0 each), tie broken by source order rather than intent. The block's own `.ts-quote` rule already had the fix for this exact issue (scoped to `.ts-section .ts-quote`, specificity 0,2,0, with a comment explaining why) — `.ts-name`/`.ts-title` just never got the same treatment. Scoped both to `.ts-section .ts-name`/`.ts-section .ts-title` to match.

While rebuilding, `style-index.css` flagged the now-familiar drift-pattern.svg base64-bloat warning (this block's Deep Blue variant has the same animated topo overlay as two-column-video/hero-curved-standard). Applied the same `--ts-pattern-url` custom-property fix here too — `style.css`'s `.ts-section--blue::before` now reads `var(--ts-pattern-url)`, injected via `render.php`'s wrapper `style` attribute and `edit.js`'s `useBlockProps` style, exactly the established pattern. CSS dropped to 2.2KB, no more size warning.

Built via the scoped/chunked config, verified only this block's folder changed, shipped `testimonial-single-patch.zip`.

**Fixed so far:** two-column-video, hero-curved-standard, testimonial-single. **Still carrying the base64 drift-pattern risk, not yet fixed:** segments, hero-blog, two-column-overlay, feature-stat, un-goals-two-column, logo-strip, faq-accordion, icon-columns, dealer-finder, segment-hero, three-column-icons, stats-grid, job-openings, hardware-lineup, product-grid, pre-footer-cta, hero-curved, testimonials-carousel, two-column-alternating, hero, un-goals-nav, two-column. Worth a proper sweep across all of them next time one comes up for edit, rather than only fixing them reactively one at a time.

---

## August 3, 2026 — Standard Hero (Curved Edge): sensor PNG zoom raised to 500%, and pre-emptively fixed the same CSS-bloat risk here too

Lauren asked for a quick change: the "Device image (sensor PNG)" size control in `hero-curved-standard` was capped at 200%, needed 500%. The size/offset controls for both the device (sensor) image and the app/phone image share one `MediaPanel` component in `edit.js`, so gave that component a `maxScale` prop (default 200, unchanged for the phone/app image) and passed `maxScale={500}` only on the device-image panel — a one-line, scoped change rather than bumping the shared control's cap for both.

While rebuilding, the webpack output flagged `hero-curved-standard/style-index.css` at ~127KB — the exact same drift-pattern.svg-inlined-as-base64 issue diagnosed and fixed in `two-column-video` last session (see the entry below). Since this block was already open and the fix is proven, applied it here too before it could bite on some future edit: `.shc-pattern`'s `background-image` now reads a `--shc-pattern-url` custom property instead of a relative `url()`, injected via the existing `$image_css_vars` inline style in `render.php` and via `useBlockProps`'s `style` in `edit.js` (using the same `window.cropxThemeData.themeUri` global). `style-index.css` dropped from 127KB to 5.2KB, zero base64 SVGs left in it.

Built via the scoped/chunked config (entry-as-function + `CleanWebpackPlugin` stripped — see the build gotchas documented below), verified only this block's folder changed, and shipped a small 10-file patch zip (`hero-curved-standard-patch.zip`) per the now-standard "small targeted patch beats a full re-upload" workflow — upload it directly into `wp-content/themes/cropx/build/blocks/hero-curved-standard/` and extract in place.

**Follow-up, same day:** Lauren reported the higher zoom still wasn't visibly changing the sensor on the live Vertex product page. Live-inspected via Claude in Chrome — `--shc-device-scale` on `.shc-bleed-wrap` was correctly set to `451`, proving the editor control and CSS variable pipeline both worked. But the actual `<img class="shc-device">` had a **hardcoded inline `style="height: 100%; width: auto;"`**, which as an inline style always beats the `.shc-device { height: calc(1% * var(--shc-device-scale)) }` stylesheet rule regardless of specificity — so the Size control has silently done nothing for a real uploaded device image since this feature was first built. (The two fallback branches in `render.php` — external URL, and no-image-selected default — never had this hardcoded style, so they were never affected; only the normal "select an image from the Media Library" path was broken, which is the path everyone actually uses.) Removed the hardcoded style from `render.php`'s `wp_get_attachment_image()` call. Rebuilt, verified the string is gone from the compiled `render.php`, repackaged `hero-curved-standard-patch.zip` (same filename, updated contents) for Lauren to re-upload.

---

## Today (August 2, 2026), very late — found why the Deep Blue feature "wasn't deploying" (it was a WP File Manager extraction limit, not a code bug)

After the Deep Blue feature below shipped in `cropx-theme-20260802-224422.zip`, Lauren reported it still wasn't showing on staging even after re-uploading via WP File Manager — front-end still rendered `bg-taupe`, editor still showed dark heading/gray body/deep-blue button. Ruled out browser caching and "wrong zip uploaded" (confirmed via live `fetch()` with `cache:'no-store'` against the theme files: `block.json` on staging DID update with the correct `deep-blue` enum on the latest attempt) — but `style-index.css` in that exact same block folder stayed stale (`Last-Modified: Wed, 29 Jul`) across every re-upload.

**Root cause:** `two-column-video/style.css` references the topo-drift SVG (`assets/decorative/drift-pattern.svg`, ~90KB) via a relative `url()`. Webpack base64-inlines that straight into the CSS, ballooning `style-index.css` from a few KB to **~127KB**. That's large enough that WP File Manager's zip-extraction silently failed to overwrite it on Lauren's staging server, while every smaller file in the same folder (`block.json`, `index.js`, `render.php`) extracted fine — so the deploy looked "partially successful" with no error shown anywhere. Confirmed the zip itself was correct and complete (checked its contents directly) — this was purely an extraction-side limit, not a build or packaging bug.

This is exactly the failure mode CLAUDE.md's block-development gotcha #7 already warns about ("webpack inlines small assets... for anything larger, inject via PHP instead") — the drift-pattern SVG had just never hit a real deploy where its size mattered until now. **Same pattern (`url('../../../assets/decorative/drift-pattern.svg')`) is used in ~20+ other blocks** (segments, two-column, two-column-overlay, feature-stat, hero, icon-columns, stats-grid, hardware-lineup, and more) — those haven't broken yet because their build/deploy pass hasn't happened to land squarely on a large CSS file in this specific way, but the same risk is latent there. Worth a systemic pass later if this bites again.

**Fix applied to `two-column-video` (the one actually reported broken):**
1. `style.css` — `.tcvid-section--bg-deep-blue::before` now reads `background-image: var(--tcvid-pattern-url);` instead of a relative `url()`, so webpack never touches/inlines the asset.
2. `render.php` — injects `--tcvid-pattern-url: url(<CROPX_THEME_URI>assets/decorative/drift-pattern.svg)` as an inline style on the wrapper when `bgColor === 'deep-blue'`.
3. `edit.js` — sets the same custom property via `window.cropxThemeData.themeUri` (already exposed globally by `inc/enqueue.php` for editor scripts) so the editor preview matches.

Result: `style-index.css` dropped from 127KB → **5.3KB**. No more base64, nothing left in that file large enough to trip the same extraction limit.

**Build gotcha discovered while fixing this — scoped/chunked builds need two extra guards now:**
- In the currently-installed `@wordpress/scripts` (27.9.0), `webpack.config.js`'s `entry` is a **function** (resolved async by webpack), not a plain object. A `build-chunk.js` override that does `Object.keys(config.entry)` to filter to one block (the pattern used earlier in this session for `nav`/`product-sections`) now silently filters against `{}` and produces **zero entries** — webpack reports "compiled successfully" while building nothing. Must `await` the function and filter its resolved result instead.
- `CleanWebpackPlugin` (added by `@wordpress/scripts`) wipes the **entire** `build/` output directory once at the start of every build, by design — fine for a full build (everything regenerates), but fatal for a scoped build: with zero entries (from the bug above) it deleted every other block's compiled JS/CSS and rebuilt nothing to replace it. Had to restore `build/` from the last known-good zip and re-run with the entry bug fixed **and** `CleanWebpackPlugin` stripped from `config.plugins` for chunked runs specifically.
- Also: a real `npm run build` (all 40 blocks) reliably takes longer than this environment's 45-second command limit, and background/detached processes (`nohup`, `setsid`) do **not** survive past the end of a shell tool call in this sandbox — so full builds can't be run here at all. Scoped/chunked builds (with the two guards above) are the only way to build anything in this environment; budget for that in future block work.

Rebuilt just `two-column-video` with the corrected chunked config, verified only that block's folder changed (spot-checked `nav` and `segments` timestamps stayed untouched), and repackaged as `cropx-theme-20260802-233615.zip`.

**Update:** that zip still didn't land — Lauren re-uploaded it and downloaded the theme back for me to inspect. Found the block's folder had three different files each frozen at a different upload's state (`render.php` stale from an earlier attempt, `style-index.css`/`style-index-rtl.css` stale since July, `block.json`/`index.js` correctly on the latest fix) — proof the full ~600-file zip extraction via WP File Manager is dropping files unpredictably, not tied purely to size (a plain 9KB `render.php` failed here too). Lauren doesn't have SSH to the staging box, so switched tactics: zipped up just the `two-column-video` build folder (10 files, 17KB) as `two-column-video-patch.zip`, had her upload+extract that directly into `build/blocks/two-column-video/` instead of the whole theme. **That worked immediately.** Documented this as the standard move for future stuck deploys in CLAUDE.md's new "Deploying to live staging" section — small targeted patch zip beats re-uploading the full theme when a change won't land.

---

## Today (August 2, 2026), later still — Deep Blue + topo overlay background added to "2 Columns with Text & Video"

Lauren asked for a Deep Blue background option (with the same animated topographic-map overlay used on the segments block and other two-column-style blocks) on `cropx/two-column-video` — previously only Taupe 50 / White were available.

Turned out the deep-blue background + topo-drift CSS (`.tcvid-section--bg-deep-blue`, `::before` pattern layer, `tcvid-drift` keyframe) was **already sitting in `style.css`, fully written, just never wired up** — likely left over from when this block was scaffolded from `two-column`/`two-column-overlay` as a template. Connected it in the three places that gate it:

1. `block.json` — `bgColor` enum: `["taupe", "white"]` → `["taupe", "white", "deep-blue"]`.
2. `edit.js` — added a "Deep Blue" option to the existing `SelectControl` (same pattern as `two-column`/`two-column-overlay`).
3. `render.php` — the `in_array()` validation allowlist now includes `'deep-blue'` (it was silently falling back to `'taupe'` before, even if the attribute were somehow set).

Also found and fixed a real gap while verifying against `two-column`'s reference implementation: the existing deep-blue CSS had heading/body text color overrides but **no CTA button or text-link color overrides**. `.tcvid-cta`'s default styling is Deep-Blue-background/White-text (it inherits `--btn-primary-bg: var(--deep-blue)`) — so on a Deep Blue section background, the button would have rendered invisible (Deep Blue on Deep Blue). Added the same CTA/link inversion rules `two-column` and `two-column-overlay` use: `.tcvid-cta` flips to White background / Deep Blue text on hover-to-`--gray-100`, `.tcvid-link` flips to White text.

Rebuilt `two-column-video`, verified the compiled `block.json`, `style-index.css`, and `render.php` all contain the new deep-blue wiring. Packaged as `cropx-theme-20260802-224422.zip`.

---

## Today (August 2, 2026), night — live-verified the sticky-nav fix on staging + found the actual overflow source

Lauren connected Claude in Chrome and pointed me at the live staging Products page (`http://ec2-100-25-145-190.compute-1.amazonaws.com/products/`), so this is the first fix in this whole saga verified against the real site instead of just the compiled CSS.

**Verification method:** `window.resize_window` didn't actually shrink this sandboxed browser's viewport (it stayed pinned at ~1872px regardless of the requested size — worth knowing for next time, don't trust it for responsive testing here). Worked around it by injecting a same-origin `<iframe>` at each target width and measuring layout inside the iframe's own `contentWindow`/`contentDocument` — since it's a real nested browsing context with its own `innerWidth`, this reproduces true responsive layout (media queries, flex-basis, `position:sticky`, etc.) exactly like resizing a real window would, without needing OS-level window control.

**Result: the `overflow-x: clip` fix (from earlier tonight) is confirmed working.** Swept both reported bands (330–450px in ~10–15px steps) and scrolled the jump nav into its stuck state at every width: `.psec-jump-wrapper`'s `getBoundingClientRect().top` was `0` at every single width tested, in both the 336–388 and 401–440 bands. The gap is gone.

**Also found the actual source of the residual horizontal overflow** (the thing that was interacting with `overflow-x:hidden` to cause the gap in the first place, back when it was still `hidden`): `document.documentElement.scrollWidth` still measurably exceeds `innerWidth` in exactly those same bands (pinned at exactly 388px for the whole 336–388 range, then jumping to exactly 440px for the whole 401–440 range — the flatness across each band was the tell that something has a fixed dimension, not a fluid one). Walked every element on the page checking `getBoundingClientRect().right > innerWidth` and found it: `.cnav-mobile-login` (the "Login" button inside the site nav's mobile dropdown) renders at ~384px wide inside a 336px viewport. Root cause confirmed via computed-style inspection: `.cnav-mobile-login` doesn't declare its own `width`, so it inherits `width: 100%` from the more generic `.cnav-mobile-item > a, .cnav-mobile-btn` rule — but with the default `box-sizing: content-box`, `width: 100%` only sizes the *content* box, and `.cnav-mobile-login`'s own `2.75rem` horizontal padding gets added on top of that 100%, pushing the rendered element wider than its container. Because the mobile nav panel is *always* present in the layout below 900px regardless of open/closed state (a previously-noted quirk — see the "mobile Products page" entry above), this overflowing-but-invisible button was silently contributing to the page's horizontal scrollWidth on every single page, at every mobile width, whether the menu was open or not.

**Fix:** added `box-sizing: border-box` to both `.cnav-mobile-login` rule blocks in `nav/style.css` (the base `:where()` rule and the higher-specificity `.wp-block-cropx-nav` override) so `width: 100%` includes the padding instead of adding to it. This is a bonus/cleanup fix on top of the already-confirmed-working `clip` fix — not required to resolve the reported gap (that's independently confirmed fixed above), but worth doing since it's the actual root cause and a real, if invisible, overflow contributor sitewide. Rebuilt `nav`, verified the compiled CSS contains `box-sizing:border-box` in both places, packaged as `cropx-theme-20260802-223119.zip`.

---

## Today (August 2, 2026), even later still — sticky jump-nav gap reopened at two narrow widths: found the real interaction

Lauren re-tested the previous fix and the gap above the stuck jump nav was mostly gone, but still reappeared in two narrow, specific viewport-width bands: ~336–388px (largest at 336, shrinking to 0 by 388) and ~401–440px (largest at 401, shrinking to 0 by 440), with no gap in between (389–400) or outside either band.

**Root cause: the `overflow-x: hidden` safety net (added earlier today for the taupe-50 swipe-strip bug) was itself breaking `position: sticky`.** Setting `overflow-x: hidden` on an element also promotes its *other* axis to a computed value of `auto` (per the CSS overflow spec — a hidden/non-visible axis paired with a visible one forces the visible one to `auto`), which makes that element a scroll container. `body` became one. Any `position: sticky` descendant — the product-sections jump nav included — then sticks relative to `body`'s own scrollport instead of the true viewport. Normally invisible, since the two coincide, but at any width where something on the page still genuinely overflows horizontally by a few px (the original bug's root cause, never fully identified — see the "not 100% pinned down" note from earlier today), the two scrollports diverge just enough to reopen a gap above the stuck nav. That lines up exactly with Lauren's report: the gap only reappears in the narrow bands where residual overflow still exists, not everywhere.

**Two-part fix:**
1. `styles/shared.css` — changed `html, body { overflow-x: hidden }` to `overflow-x: clip`. `clip` clips identically for our purposes but does not establish a scroll container or promote the other axis, so it can't affect any descendant's sticky positioning, regardless of whether the underlying overflow is ever fully tracked down.
2. `product-sections/style.css` — added `min-width: 0; overflow: hidden; text-overflow: ellipsis;` to `.psec-jump-tab`. This is a concrete, likely source of the residual overflow itself: the tab is a `flex: 1` item with `white-space: nowrap` text, and flex items default to a min-width equal to their content's min-content size — so at container widths too narrow for both differently-sized labels ("CropX Platform" vs "CropX Hardware"), the row was forced wider than its container instead of shrinking, which fits the "two separate bands, one per label" shape of what Lauren saw. `min-width: 0` lets it shrink and truncate instead.

Rebuilt `product-sections`, verified the compiled CSS contains both `overflow-x:clip` (shared.css, no build step needed) and `text-overflow:ellipsis` (in the rebuilt `style-index.css`). Packaged as `cropx-theme-20260802-221326.zip`. Also hit a real build-tooling gotcha while doing this: `@wordpress/scripts`' auto-discovered webpack entry keys are `blocks/<name>/index` and `blocks/<name>/view`, not `<name>/index` — the scoped/chunked build script used in earlier sessions (and briefly again here) had been filtering against the wrong key format, so it silently built ZERO entries while still printing "compiled successfully," leaving stale output that got shipped in the *previous* zip without the sticky-nav fix actually taking effect. Fixed the filter and reconfirmed via direct content grep (not just exit code) on every subsequent build — worth doing that verification step every time a chunked build is used, since a false "success" doesn't rebuild anything and is easy to miss.

Couldn't verify any of this against the live staging site directly this session — Claude in Chrome's browser tools weren't connected (extension not reachable). If the gap turns up anywhere else after this fix, that's the next thing to set up.

---

## Today (August 2, 2026), even later — mobile Products page: horizontal-overflow strip + sticky jump-nav gap

Lauren reported two mobile-only bugs on the Products page via screenshot: (1) swiping/dragging horizontally reveals a thin strip of blank Taupe-50-colored space to the right of the visible content, and (2) once the "Product Grid with Tabs" sticky jump nav ("CropX Platform" / "CropX Hardware") is stuck to the top while scrolling, there's a visible gap between it and the actual top edge of the screen instead of sitting flush.

**Bug 2 (sticky nav gap) — root cause confirmed and fixed.** `product-sections/style.css` sets `--psec-nav-h` (the jump nav's sticky `top` offset) to `46px` inside `.admin-bar` at `max-width: 782px`, under the assumption that this compensates for the WP admin toolbar staying pinned to the top on mobile the same way it does on desktop (32px). It doesn't: WordPress core CSS switches `#wpadminbar` from `position: fixed` to `position: absolute` below 782px, so on mobile it scrolls away with the rest of the page instead of staying pinned. Once scrolled past it, the jump nav was still reserving 46px of top offset for a toolbar that's no longer there, leaving a permanent stale gap for the rest of the scroll. Also confirmed via source audit that the site's own nav (`cropx_render_nav()` / `.cnav-block`) is `position: relative`, not sticky/fixed at all (an inaccurate code comment above `.psec-block` claimed otherwise — corrected it) — so `top: 0` is the mathematically correct default for a real, logged-out visitor. Fix: reset `.admin-bar .psec-block { --psec-nav-h: 0px; }` inside the `@media (max-width: 782px)` block instead of `46px`. Rebuilt `product-sections` and verified the compiled CSS shows `psec-nav-h:0px` inside that media query.

**Bug 1 (horizontal overflow) — added a global safety net; exact single culprit not 100% pinned down.** Traced through the likeliest suspects: the hero's `.shc-device`/`.shc-phone` mockups are `display:none !important` below 900px, so they're not it; the nav's mega-dropdown already uses `display:none` (not just opacity/transform) specifically to avoid contributing to `scrollWidth` when closed; the card-bleed illustration overlay's `.pg-overlay-clip` clips flush on all sides except the (intentionally bleedable) top via `clip-path: inset(top 0 0 0 round ...)`, so it can't actually cause horizontal overflow despite `.pg-item` having `overflow: visible`. One real oddity found: the mobile nav panel (`.cnav-mobile-panel`) switches from `display:none` to an unconditional `display:block` at `≤900px` regardless of open/closed state (relying on `opacity`/`pointer-events` alone to hide it when closed) — a milder version of the exact "invisible element still contributes to scrollWidth" pitfall the mega-dropdown's own code comment warns about, though no concretely-overflowing child was confirmed inside it. Given the codebase leans heavily on `100vw`/edge-bleed positioning tricks in several places (nav mega-dropdown, hero `--shc-edge` calc, segments block, mobile nav panel), added a defensive, low-risk fix instead of chasing a single guaranteed root cause: `html, body { overflow-x: hidden; }` in `styles/shared.css` (section 0, directly enqueued sitewide, takes effect with no build step). This doesn't clip anything visually — it just removes the ability to scroll sideways to reveal whatever the few stray px of overflow turn out to be. If the strip still reappears anywhere after this, the mobile nav panel's always-`display:block` behavior is the next thing to dig into.

Both fixes are in `cropx-theme-20260802-200806.zip`. Verified post-build: `build/blocks/product-sections/style-index.css` contains `psec-nav-h:0px` inside the mobile media query, and `styles/shared.css` contains the new `overflow-x: hidden` rule. Also verified both edited source CSS files still parse cleanly via a direct Node `postcss.parse()` check (independent of the webpack build), same diagnostic technique as the Aug 2 shared.css incident earlier today.

**Build gotcha worth remembering:** the scoped/chunked build technique's `build-chunk.js` filter previously matched entry keys as `<block-name>/index`, but the real auto-discovered entry key format from `@wordpress/scripts` is `blocks/<block-name>/index` (and `blocks/<block-name>/view`) — confirmed by dumping `Object.keys(defaultEntry)`. Using the wrong prefix silently produces an EMPTY entry object, so webpack "compiles successfully" in about a second while writing zero files — no error, just stale output. Always verify a chunked build actually touched the target file's mtime/content before packaging a zip; don't trust "compiled successfully" alone. Fixed for future use (filter now checks `blocks/${b}` / `blocks/${b}/...`), but `build-chunk.js` is deleted after each use per the existing convention, so this note is the only record — worth restating in the next session if a chunked build is used again.

---

## Today (August 2, 2026), later still — top bleed max raised from 10px to 20px

Simple follow-up on the earlier top-bleed feature: raised the "Top bleed (px)" slider's max on both `cropx/product-grid` and `cropx/product-sections` ("Product Grid with Tabs") from 10 to 20. Updated the RangeControl `max` in both blocks' `edit.js`, the PHP clamp (`max( 0, min( ... ) )`) in both blocks' `render.php`, and the doc comments in `styles/shared.css` (the single source of the card CSS as of the earlier consolidation) and both render.php files referencing the old 0–10 range. No CSS changes needed — the `clip-path` mechanism already accepts any px value via the `--pg-overlay-top-bleed` custom property, the old 10px cap was JS/PHP-only. Rebuilt both blocks, verified the compiled JS reflects `max:20` and the compiled PHP files carry the `min( 20, ... )` clamp.

## Today (August 2, 2026), later still — "Show More" batch size: no code change needed, just a WP Admin setting

Lauren asked for the four paginated "Show More" archive pages (Customer Results, Insights, Press Room, Blog) to never show a partial last row before the button — except on the truly final batch when there's nothing left to load. All four grids are responsive 3-columns → 2-columns → 1-column (breakpoints differ slightly: Blog switches to 1-col at 900px, the other three at 640px/1024px — see `styles/blog-archive.css`, `styles/ag-archive.css`, `styles/pub-archive.css`). Because 1-column is trivially always "full," the only real constraint is that the running post count after every load (initial + each "Show More" click) must be a multiple of both 3 and 2 — i.e. a multiple of 6 (their LCM). Picked **12** with Lauren (4 rows at 3-per-row, 6 rows at 2-per-row).

Traced the batch size across all four pages/JS files (`inc/customer-stories.php`, `page-insights.php`, `page-press-room.php`, `inc/insights-archive.php`, `home.php`, `archive.php`, `archive-cropx_publication.php`, and their three JS modules `pub-archive.js`, `ag-archive.js`, `blog-archive.js`) — every single one of them, for both the initial PHP-rendered `WP_Query` and every subsequent JS `Show More` REST fetch (`per_page` param sourced from a `data-per-page` HTML attribute), pulls from the exact same site-wide value: `get_option( 'posts_per_page' )` (WP Admin → Settings → Reading → "Blog pages show at most"). No page has its own hardcoded batch size anywhere. The "reached the end" exception is also already handled correctly and identically in all three JS files via `data-max-pages` / `currentPage >= maxPages` — the button simply disappears once there are no more posts, so a shorter final batch (which may not be a multiple of 3 or 2) is exactly and only ever shown in that terminal case.

**Net result: this needed zero code changes.** Lauren just needs to set Settings → Reading → "Blog pages show at most" to **12** in WP Admin, and all four pages' full-row behavior is fixed simultaneously (that's also why it's called out as a *sitewide* setting — it affects search results and RSS too, which Lauren was told and opted to accept).

**Gotcha for later:** if any of these four grids' column counts ever change (e.g. a redesign moves Blog to 4-per-row), the batch size stops being safe and needs to be recalculated as the LCM of whatever the new set of non-1 column counts is across all four pages — it's a single shared WP setting, so it has to satisfy all four simultaneously. Re-audit this list of files if that happens: `inc/customer-stories.php:107,196`, `page-insights.php:66`, `page-press-room.php:65`, `inc/insights-archive.php:397`, `home.php:154`, `archive.php:127`, `archive-cropx_publication.php:232`.

---

## Today (August 2, 2026), later — the REAL cause of the top-bleed/vertical-offset bug: a third, competing copy of the card CSS in styles/shared.css

The block.json version-caching fix (below) turned out to be real but NOT the cause of what Lauren was seeing — she re-uploaded the theme with that fix applied and the top-bleed and vertical-offset sliders still had zero visible effect, while confirming the zoom-to-500% change (JS-only) DID work. That split was the clue that something CSS-specific, not cache-specific, was still wrong.

Root cause, found via a DevTools screenshot Lauren sent (Computed panel + Elements tree confirmed the correct `--pg-overlay-y`/`--pg-overlay-top-bleed` inline custom properties WERE present in the rendered HTML — so render.php and edit.js were never the problem) plus a source-level audit: `wp-theme/cropx/styles/shared.css` — a global stylesheet enqueued on every single page via `wp_enqueue_style()` — contained its OWN complete, older copy of `.pg-grid`/`.pg-item`/`.pg-thumb`/`.pg-overlay--card-bleed` and its variants (a leftover from an earlier "CSS consolidation" pass, predating this session, that was never cleaned up). Because both `shared.css` and each block's own `style-index.css` declared the exact same selectors, the browser's cascade rules pick a winner **per CSS property** for ties in specificity — whichever stylesheet is later in the page's `<head>` wins for any property the two disagree on. `shared.css`'s old `.pg-item` still had `overflow: hidden` (pre-dating the overflow:visible architecture change from earlier today) and its `.pg-overlay--card-bleed` transform had no `--pg-overlay-y` term at all — so for exactly the two properties that mattered (`.pg-item`'s `overflow`, `.pg-overlay--card-bleed`'s `transform`), the stale global copy silently won over the correct, newly-edited block-local copy, no matter how many times the block CSS was fixed and rebuilt.

**Fix — consolidated down to one copy instead of three:** removed the entire duplicate `.pg-grid`/`.pg-item`/`.pg-thumb`/`.pg-overlay-*`/`.pg-text`/etc. section from both `product-grid/style.css` and `product-sections/style.css` (each now carries only its block-unique CSS — `.pg-block`, `.pg-heading`/`.pg-body` for product-grid; `.psec-*` jump-nav/section CSS for product-sections), and made `styles/shared.css` (section 9) the single, sole, up-to-date source of the card grid CSS, including the top-bleed `clip-path` mechanism and the `--pg-overlay-y` vertical-offset term. This is safe/correct specifically because `shared.css` is a plain globally-enqueued stylesheet — not subject to the earlier-discovered webpack limitation (that limitation only affects CSS imported into a block's own JS bundle via `import './foo.css'` in `index.js`; a directly-`wp_enqueue_style()`'d file has no such restriction, so it can genuinely be shared). `product-tabs` (archived, `inserter:false`) still reads from this same shared.css copy and needed no changes. Added prominent comments in both block style.css files and in shared.css's section 9 explaining exactly what happened and pointing at shared.css as the one place to edit going forward, so a fourth copy never gets created.

Rebuilt both `product-grid` and `product-sections` (the only two blocks with source changes — `shared.css` itself isn't part of the webpack build, it's loaded straight from `styles/` at runtime, so editing it takes effect immediately on the next page load, no build step required for that file specifically). Verified: compiled `style-index.css` for both blocks no longer contains a `.pg-item` rule at all (confirmed via grep — zero matches), `shared.css` contains the correct `overflow: visible` and all 4 `--pg-overlay-y` transform variants, and all 40 blocks are still present after the build. `.pg-heading`/`.pg-body` (product-grid) and `.psec-panel-heading`/`.psec-panel-blurb` (product-sections) — the parts that stayed local — are still present and untouched.

Needs `npm run build` (already done, just needs rsync) — and this time `styles/shared.css` also needs to make it into the rsync/zip, since that's where the actual fix lives now. Double-check it's not accidentally excluded from any future lean deployment zip (it's a top-level `styles/` file, not `build/`, `src/`, or a dev-only file, so it should already be included by the existing exclude list — just flagging it since this is now the single most important file for this specific bug).

---

## Today (August 2, 2026) — Product Grid with Tabs: illustration top-bleed + a second "missing CSS" bug found

**Feature:** the card-bleed illustration overlay on `cropx/product-grid` and `cropx/product-sections` ("Product Grid with Tabs") can now be set to poke out up to 10px above the top of its card, via a new "Top bleed (px)" slider next to the existing overlay controls (Overlay height / Vertical anchor / Center on photo column / Horizontal offset). New per-item attribute: `overlayTopBleed` (0–10, default 0).

To make that possible, `.pg-item`'s `overflow: hidden` (which used to clip everything to the card's rounded corners) had to become `overflow: visible`, since nothing can visually escape an ancestor's `overflow: hidden` no matter what a descendant does. Clipping is now handled per-piece instead: the photo rounds its own corners via `border-radius` + `overflow: hidden` directly on `.pg-thumb`; the card-bleed overlay is wrapped in a new `.pg-overlay-clip` div that uses `clip-path: inset(...)` instead of `overflow`, with the top inset set to a *negative* px value (via `--pg-overlay-top-bleed`) so the visible region extends upward by exactly the configured bleed amount while staying flush on the other three sides — identical to the old clipping behavior when bleed is 0. The card's white background and box-shadow are unaffected either way, since `border-radius`/`box-shadow` are always self-clipped by an element regardless of its own `overflow` value.

**Second missing-CSS bug found and fixed (same shape as the July 30 global-styles bug, unrelated cause):** while implementing this, discovered that `cropx/product-sections` ("Product Grid with Tabs") had **zero** `.pg-item`/`.pg-grid`/card CSS in its own compiled stylesheet — its `style.css` had a comment claiming the card styles were "copied from product-grid," but they never actually were. This block's cards would render completely unstyled on the front end unless a `cropx/product-grid` block happened to also be present somewhere on the same page (whose CSS would incidentally also apply, since both blocks emit identical `pg-*` class names). Tried extracting the shared card CSS to `src/shared/product-card.css` and importing it from both blocks' `index.js` to fix this properly — but `@wordpress/scripts`'s webpack config silently dropped that shared file from both blocks' compiled `style-index.css` output (confirmed by direct inspection of the build output; the module was compiled but never emitted into either block's front-end stylesheet). There's no reliable way to share one CSS file's front-end output across two block bundles in this build setup, so real de-duplication isn't available here — the fix is a careful, explicitly-flagged copy-paste instead: both `product-grid/style.css` and `product-sections/style.css` now carry the full, identical, up-to-date card CSS (including the new top-bleed rules), each with a prominent comment pointing at the other file and explaining exactly what went wrong last time, so this doesn't quietly drift out of sync a second time.

Files changed: `product-grid/{style.css,render.php,edit.js,block.json,index.js}`, `product-sections/{style.css,render.php,edit.js,block.json,index.js}` (the `index.js` changes were the shared-CSS-import experiment being added then reverted — both now match their pre-existing import structure, just without the extra line). Built, verified both blocks' compiled CSS contains the new selectors and both blocks' compiled JS contains `overlayTopBleed`, and confirmed all 40 blocks are still complete after the build. Needs `npm run build` + rsync to appear in WordPress (see commands in CLAUDE.md).

**Same-day follow-up:** raised the "Photo zoom (%)" slider's max from 200 to 500 on both blocks (`product-grid/edit.js`, `product-sections/edit.js`) — the underlying CSS (`transform: scale(calc(var(--pg-photo-zoom, 100) / 100))`) has no hardcoded cap, so this was a UI-bound-only change, no CSS/PHP edits needed. Built + verified both blocks' compiled JS reflects `max:500` (and no longer `max:200`).

**Same-day follow-up #2 — vertical offset control for the card-bleed overlay:** added a new "Vertical offset (px)" range slider (−60 to 60) next to the existing "Horizontal offset" control, so the illustration can be nudged up/down from its anchor the same way it's nudged left/right. New per-item attribute: `overlayY` (default 0), rendered as `--pg-overlay-y` and folded into the existing transform: `translateY(calc(-50% + var(--pg-overlay-y, 0px))) translateX(...)` (and the `top:auto; bottom:0` bottom-anchor variant just drops the `-50%` term). Applied identically across both blocks: `style.css`, `render.php`, `block.json`, `edit.js` (default item shape, new RangeControl, canvas-preview inline style). Built + verified both blocks' compiled CSS contains `--pg-overlay-y` in the transform and both blocks' compiled JS contains `overlayY`; confirmed all 40 blocks still complete after the build.

**Staging "bleed not working" report — root cause found: sitewide CSS caching bug, not a code defect.** Lauren reported the Top Bleed slider having no visible effect, and separately that the new vertical-offset slider also had no effect — while the "Photo zoom" slider's raised max (200→500, a JS-only change) *did* work. That split (JS updates land, CSS doesn't) was the tell.

Root cause: every block's `block.json` declared a static, hand-set `"version"` field (`"1.0.0"`, `"0.1.0"`, etc. — never bumped since each block was first built). WordPress core uses `block.json`'s `version` field as the cache-busting `?ver=` query string for that block's registered style handle (`register_block_style_handle()`), and only falls back to `filemtime()`-based versioning when the field is absent. Because the string never changed between deploys, `style-index.css?ver=1.0.0` was byte-identical on every request as far as any cache was concerned — so a browser or intermediate cache holding an old copy kept serving it indefinitely after every rsync, no matter how many times the underlying CSS file changed. Editor and front-end scripts (`index.js`) aren't gated by this same field the same way, which is why JS-only changes (like the zoom max) always appeared to work while CSS-only changes silently didn't.

**This was never specific to product-grid/product-sections — it affects all 40 blocks in the theme.** Every single block.json had this static version field. Fixed by removing the `"version"` field from all 40 `src/blocks/*/block.json` files (confirmed via grep — all 40 were affected, values ranged from `0.1.0` to `1.0.0`, none had ever been bumped), so WordPress now falls back to automatic `filemtime()`-based cache busting on every future deploy. Also copied the updated `block.json` into each corresponding `build/blocks/*/block.json` directly (no webpack recompile needed for this specific fix — block.json is a straight file copy in the build pipeline) and verified all 40 parse as valid JSON with `version` absent.

**Practical implication going forward:** any *CSS-only* edit to any block (not just this one) may have been getting stuck on stale cache after every deploy since these blocks were first built — this fix should resolve that category of "I made the change but it's not showing up" reports theme-wide, not just for the top-bleed/vertical-offset features. Worth keeping an eye out for any other block CSS change that seemed to "not take" in the past — it likely will now.

Needs `npm run build` + rsync to appear on WordPress (see commands in CLAUDE.md). Note: this fix only prevents *future* staleness — Lauren may still need to hard-refresh (or purge any server-side/CDN cache) once after this deploy to clear whatever was already cached under the old static version string.

---

## Phase 2: ✅ All 19 blocks ported to WordPress Gutenberg as of May 17, 2026.
## Nav: ✅ Wired to WordPress native menus as of June 5, 2026.
## CPT Layer: ✅ All post types, taxonomies, and admin UX complete as of June 8, 2026.

---

## Today (July 30, 2026, session 3) — Sitewide dark-background body-text bug: root cause + fix

**What happened:** Lauren reported two-column-alternating's body copy still rendering dark/illegible on a Deep Blue background row, despite CSS that looked correct on paper. Two rounds of Chrome DevTools screenshots (Styles panel, then Computed panel) weren't conclusive on their own — Lauren found the actual root cause herself in a third screenshot: a WordPress **global styles** rule, `:root :where(p) { color: #3C3A36; ... }` (visible in DevTools sourced from `global-styles-inline-css`), was winning over our own deep-blue override.

**Root cause:** CSS inheritance loses to direct matching, full stop, regardless of specificity. Several blocks set `color` on a wrapper element (`.section-body`, `.tca-body`, `.usn-body`, etc.) intending it to cascade down to the `<p>` tags nested inside it. That works fine *until* something else sets `color` directly on the `<p>` element itself — and WordPress auto-generates exactly that, sitewide, for every `<p>` on the page (`:root :where(p) { color: #3C3A36; ... }`). `:root` contributes one pseudo-class of specificity; `:where()` contributes **zero** — so this rule's total specificity is only (0,1,0), roughly one class selector. But because it targets the `<p>` element *directly*, it beats any ancestor-level rule targeting a wrapper div, no matter how many classes that ancestor selector has. The fix is always the same shape: target the nested `<p>` explicitly too, e.g. `.xxx-section--bg-deep-blue .section-body, .xxx-section--bg-deep-blue .section-body p { color: ...; }`.

Two blocks (`contact-form`, `newsletter-cta`) already had this fix from an earlier, unremembered session — proof the bug had been hit before but never propagated theme-wide. Audited every block for the same pattern and fixed **13 total instances across 12 blocks**:

- `two-column-alternating` — both `.tca-body` (per-row) and `.section-body` (intro)
- `field-photos` — `.section-body` (intro body added earlier this session)
- `video` — `.section-body`
- `stats-grid` — `.section-body`
- `two-column-overlay` — `.section-body`
- `feature-stat` — `.section-body`
- `un-goals-nav` — `.section-body` (intro; `.usn-body` per-item was already safe — see below)
- `two-column` — `.section-body`
- `un-goals-two-column` — `.section-body`
- `mid-page-cta` — `.section-body`
- `two-column-video` — **also had zero deep-blue text-color overrides at all** (a second, separate bug); added both the heading and body color rules from scratch, matching the pattern used in `two-column`/`two-column-overlay`
- `people-showcase` — `.section-body`

**Confirmed safe, no change needed:** `split-column-icons`, `six-column-icons`, `icon-columns`, `three-column-icons`, `product-grid`, `contact-form`, `newsletter-cta`, `cards`, `resource-downloads`, `segments`. These either apply their body-text class directly to the `<p>` tag itself (e.g. `<p class="usn-body">`, `<p class="crd-excerpt">`, `<p class="rsd-excerpt">`) or use a RichText field with `tagName="p"` and inline-only `allowedFormats` (segments' `.seg-panel-desc`), so there's no div-wrapper-to-nested-`<p>` inheritance gap to exploit.

**Build tooling gotcha found and fixed along the way:** the scoped/chunked build technique used to work around this sandbox's 45-second-per-tool-call limit was silently wiping the *entire* `build/` directory on every run. `@wordpress/scripts`'s base webpack config instantiates `CleanWebpackPlugin` directly in its `plugins` array — this is separate from webpack 5's native `output.clean` option, so setting `output: { clean: false }` in an override config does nothing to stop it. Fix: filter the plugin out of the `plugins` array for every chunk except the first (`base.plugins.filter((p) => p.constructor.name !== 'CleanWebpackPlugin')`). Verified all 40 blocks are complete after every build with a quick loop: `for b in $(ls src/blocks); do test -f "build/blocks/$b/index.js" || echo "MISSING: $b"; done`.

**Status:** All fixes rebuilt, verified compiled (grepped the built `style-index.css` for each new selector), and packaged into a fresh deployment zip: `cropx-theme-20260730-135338.zip`. This supersedes all earlier zips from today, which predate these fixes. **Action for Lauren:** upload this zip to the staging server (not the earlier one from this morning).

---

## ▶ Start here for next session

**Phase 2 is complete.** All 19 custom Gutenberg blocks are implemented, committed, and running on the local WP install. Several additional WordPress-only blocks have been built beyond the original 19 (see "WordPress-only Blocks" table).

**Nav is fully WP-native.** Three registered menus (`cropx-solutions`, `cropx-platform`, `cropx-utility`) with custom walkers. Login button remains a block attribute. Nav CSS loads globally via `inc/enqueue.php`.

**CPT layer is complete.** Five custom post types with full editorial UX (see CPT section below for details).

**Ten page pattern templates complete and deploy-ready.** Homepage, Enterprise, Service Provider, On-Farm, Products Hub, Blog Archive, About CropX, Hardware Product Page, and Contact — all in `wp-theme/cropx/patterns/`, registered in `inc/patterns.php`, all using staging-server URLs.

**Single templates are complete.** `single.php` (blog posts) and `single-cropx_publication.php` (case studies + white papers) are both polished and in the theme. No local URLs hardcoded.

**Block background color system overhauled (July 7–8, 2026).** All 20 blocks now default to "white" (or the appropriate deep-blue default for mid-page-cta and testimonial-single). Deep-blue option added to two-column, two-column-alternating, two-column-overlay, and hardware-lineup. Taupe option added to feature-stat. Gray option removed from people-showcase and video. Full details in the July 7–8 entry below.

**Native block content width fixed (July 9, 2026).** Paragraph, Heading, List, Image, Table, Columns, etc. placed directly on a Page now respect the same 72rem max-width and 2rem side padding as CropX custom block inner containers. Fix is in `styles/content.css` — no build needed, just rsync.

**Pending build + rsync.** All block source changes since the last build need `npm run build` + rsync before they appear in WordPress. This now includes: Gutenberg sidebar panel standardization (Task #171), shared IconPicker component, hero swoop editor-preview simplification, background color system overhaul, the five zoom-bug fixes from July 10, the contact-form body-text color fix from July 13, the newsletter-cta full overhaul from July 13 session 2 (5 source files: `newsletter-cta/block.json`, `newsletter-cta/render.php`, `newsletter-cta/style.css`, `newsletter-cta/edit.js`, `newsletter-cta/editor.css`), the **two-column-alternating inline bullet list** from July 17 session 2 (4 files: `block.json`, `edit.js`, `render.php`, `style.css`), the **InnerBlocks body support + bullet CSS fixes** from July 20 (6 source files: `feature-stat/edit.js`, `feature-stat/render.php`, `two-column-overlay/edit.js`, `two-column-overlay/render.php`, `two-column-video/edit.js`, `two-column-video/render.php`), the **people-showcase duplicate-person fix** from July 21 session 3 (`people-showcase/edit.js`), AND the **July 22 session 1 changes**: `src/admin/editor-panels.js` (separator cleanup) and `split-column-icons/edit.js` (no item cap). AND the **July 22 session 2 changes** (see that entry): `stats-grid/edit.js`, `hero/edit.js` (new secondary CTA attributes + panel), `segment-hero/edit.js` (new secondary CTA attributes + panel), `mid-page-cta/edit.js` (secondary CTA consolidation), `pre-footer-cta/edit.js` (arrow added to preview). AND the **July 23 changes**: `icon-columns/edit.js`, `icon-columns/render.php`, `icon-columns/style.css`, `icon-columns/block.json` (see below). AND the **July 24 changes**: `testimonials-carousel/style.css` (author-title color), `testimonial-single/style.css` (blue-section title color), `resource-downloads/view.js` (new — pdf.js thumbnails), `resource-downloads/block.json` (viewScript added), `resource-downloads/render.php` (data-pdf-url on placeholder), `resource-downloads/style.css` (canvas rule + link reset). The **July 25 changes** (`inc/cpts.php`, `archive-cropx_publication.php` comment only) are PHP-only — rsync only, no build. The **July 26 changes** need a build: `resource-downloads/render.php` (title no longer wrapped in `<a>`) and `resource-downloads/style.css` (removed the now-unused `.rsd-title a` rules) both need `npm run build` since `style.css` is webpack-bundled into `style-index.css`. The **icon-columns changes** (`icon-columns/block.json`, `icon-columns/edit.js`, `icon-columns/render.php`, `icon-columns/style.css`) and the **Quotes rename's JS piece** (`testimonials-carousel/edit.js`) also need a build. The **curved hero text-wrap fix** (July 26, evenly-balanced heading/subheadline wrapping) needs a build too: `hero-curved/style.css`, `hero-curved-standard/style.css`, `hero-blog/style.css`. The **CropX Pages pattern removal** (`inc/patterns.php`) is PHP-only — rsync only, no build. The **segments block anchor field** (`segments/block.json`) needs a build. The **stats-grid expansion** (up to 8 stats, per-stat number size, optional per-stat icons) needs a build too: `stats-grid/block.json`, `stats-grid/edit.js`, `stats-grid/style.css`, `stats-grid/editor.css` (`stats-grid/render.php` is PHP-only but bundling it into the same build+rsync pass is simplest). The **icon-columns multi-column layout change** needs a build too: `icon-columns/style.css`, `icon-columns/edit.js` (`icon-columns/render.php`'s change is comment-only but bundling it in is simplest). The **icon-columns JS count-balancing follow-up** (same day) needs a build too: new `icon-columns/view.js`, new `src/shared/columnDistribute.js`, `icon-columns/edit.js`, `icon-columns/style.css`, `icon-columns/block.json` (`icon-columns/render.php`'s `data-base-columns` addition is bundled into the same pass). The **stats-grid rename + sidebar move** needs a build too: `stats-grid/block.json`, `stats-grid/edit.js`, `stats-grid/editor.css`. `styles/pub-single.css` (`.pub-btn-link` color changed from CropX Blue to Deep Blue) and all of the Customer Stories / Customer Results work (`inc/cpts.php`, new `inc/customer-stories.php`, `functions.php`, new `page-results.php`, rewritten `taxonomy-cropx_content_type.php`, `inc/enqueue.php`, `single-cropx_publication.php`, `styles/pub-archive.css`, `assets/js/pub-archive.js`, `inc/admin-ui.php`, `inc/admin-help.php`, `inc/menus.php`) are all PHP/CSS/JS-outside-webpack — rsync only, no build needed for those. Remember to create the "Customer Results" Page in wp-admin (slug `results`) if it doesn't already exist on a given install, and drop a Shortcode block with `[cropx_customer_stories_grid]` into it. CSS-only changes (all `style.css` edits from both July 22 sessions — see that entry) only need rsync, not a full build. PHP-only changes (`block-shadow.php`, `hero/render.php`, `segment-hero/render.php`, `mid-page-cta/render.php`, `pre-footer-cta/render.php`) also need rsync only. `stats-grid/block.json` and `stats-grid/render.php` are rsync only. `home.php` and `single.php` changes are PHP-only — no build needed, just rsync. The **new `cropx/un-goals-nav` block** (all six files, brand-new) and its same-day follow-up revisions (block-level `itemEyebrowColor` rename/default change, drag-and-drop item reordering, CTA line-height/arrow-wrap fix, and the new intro eyebrow/body feature — `block.json`, `edit.js`, `render.php`, `style.css`) all need a build + rsync — see the July 29, 2026 entries below for full detail. Also from July 29: the new **Job Openings (Workable)** block (5 new files, plus `inc/cropx-settings.php`, `inc/workable-jobs-api.php`, `functions.php` — PHP-only, rsync only for those three), the **Cards block Blog Posts category filter** (`cards/block.json`, `edit.js`, `render.php`), and the **carousel auto-advance toggle + Photo Carousel sliver fix** (new `src/shared/autoAdvance.js`, plus `field-photos/`, `testimonials-carousel/`, `hardware-lineup/`, `logo-strip/` — all four need a build) all need a build + rsync.

**⚠️ When deploying to staging**: manually create the `segment-navigation` Synced Pattern in the staging WP admin (Appearance → Patterns → Add New, slug: `segment-navigation`). The pattern files silently skip the block until it exists in the database.

**Next tasks:** Product page template (#131), cookie consent banner implementation (#132), blog post import research (#134), regional contact page template. Decide whether to delete the now-retired `archive-cropx_publication.php` (kept for reference — ask Lauren before deleting anything from the connected repo folder).

**Today (July 26, 2026):** Renamed the `cropx_publication` CPT to "Customer Stories" everywhere in the admin UI (labels, meta boxes, help text) while keeping the internal post type slug and existing permalinks untouched. Removed White Paper as a content type (it now lives under Resources — Lauren added it there herself); replaced it with Video Testimonial. Rebuilt `/results/` from an automatic CPT archive into a real, block-editable "Customer Results" Page with a `[cropx_customer_stories_grid]` shortcode, so Lauren can add hero/testimonial/etc. blocks around the grid. Added a new admin-only "Story Tags" taxonomy for Customer Stories. Fixed two bugs Lauren caught by screenshot (zero page margins; filtered "Case Studies" pill view rendering the literal shortcode text). Also fixed two link/button styling issues: a "Share this story" button that had CropX Blue text, and the Resources block's card title, which was a link with no underline styling of its own — it's now plain text, not a link at all. Generalized the Press Room / Ag Insights & Research breadcrumb fix to a fully clickable 3-level trail. Built a dedicated single template for Video Testimonial customer stories so they no longer show duplicate share links or case-study-only fields. Renamed the `cropx_testimonial` CPT to "Quotes" in the admin UI (labels-only, same pattern as Customer Stories). Updated the `cropx/icon-columns` block: item cap raised to 36, column options narrowed to 4/5/6 (3 removed, 4 is now default), renamed to "4-6 Column Crop List with Icons" in the inserter, given new unified responsive breakpoints (3 columns at ≤820px, 2 at ≤600px, 1 at ≤320px), and — after Lauren caught a real-world layout bug via screenshot — rebuilt around a standard CSS Grid instead of the old column-major chunking, so items always flow cleanly into rows regardless of count. Capped the blog post ToC sidebar at h3 — h4 and deeper headings no longer appear. Updated the Resource Grid block: `.rsd-format-btn` font size, new left/center alignment controls for the intro content and the card grid, added 5/6-column intermediate breakpoints to Icon Columns (1240px/1020px), removed the resource cover image's click behavior (download buttons only), and fixed a Buttons-block underline bug that resurfaced on Customer Story and blog post pages. Emptied the "CropX Pages" pattern library in the inserter (all 10 starter-page patterns removed — safe for existing pages, source files left in place) and fixed uneven line wrapping on the three curved hero blocks' headings/subheadlines (`text-wrap: balance` instead of `pretty`, plus a missing `balance` added to hero-blog's headline). Added an HTML anchor field to the segments block, and expanded the Stats Grid block to support 2-8 stats (up to 8, was a fixed 4) with per-stat number sizing and optional per-stat icons, then renamed it to "Text with Stats Grid" and moved its Add/Remove controls into the sidebar. Rebuilt Icon Columns' responsive layout twice more the same day: first from CSS Grid to CSS multi-column (fixing a *height*-based unevenness Lauren spotted), then added a JS layer on top (`view.js` + a new shared `columnDistribute.js` helper) so the last column's *item count* is now guaranteed to never exceed any other column's — something pure CSS multi-column can't promise on its own. Full details in "Done since last update" below.

**Yesterday (July 25, 2026):** Custom permalink structure for the `cropx_publication` CPT — single publications now live at `/results/{content-type}/{post-name}/` instead of the flat `/results/{post-name}/`. Blog post permalinks also restructured (by hand, in Settings → Permalinks — no code) to nest under `/blog/` instead of the site root. See "Done since last update" below.

**Yesterday (July 24, 2026):** Four fixes + resource-downloads pdf.js client-side PDF thumbnails.

**July 23, 2026:** `cropx/icon-columns` block completely rewritten — column-major ordering via PHP `array_chunk()`, independent flex column stacks, configurable 3–6 columns. Editor canvas now previews column-major layout live.

**Yesterday (July 22, 2026) — session 2:** Stats-grid deep blue background option (all 4 files wired); secondary CTA restyled across hero, segment-hero, mid-page-cta, and pre-footer-cta (text+arrow, no outline/fill) — see that entry below.

**Today (July 22, 2026) — session 1:** Large multi-part cleanup: separator options trimmed to 2; feature-stat white background fixed; animated topography pattern added to all 14 remaining deep-blue blocks; hero/CTA text wrapping improvements; split-column-icons sticky removed + item cap lifted — see "Done since last update" below.

**Yesterday (July 21, 2026 — session 3):** people-showcase duplicate-person support; LinkedIn icon repositioned below role; mid-page-cta hardcoded grey borders removed.

**Today (July 21, 2026 — sessions 1 & 2):** resource-downloads block — deep-blue background card width fix, responsive breakpoints (952px/648px), cover image no-crop fix (`object-fit: contain`), ±12px cover wrap letterbox trim; feature-stat mobile stat card layout (card bottom-right, left accent border, upper-right rounded corner, 40px bleeds); all two-column-family blocks now at a single 768px breakpoint; two-column-alternating content width toggle; people-showcase renamed + 5/6-column layout + narrow-card radius — see "Done since last update" below.

**July 20, 2026:** Bullet color/style fixes across blocks; InnerBlocks body support added to feature-stat, two-column-overlay, two-column-video — see that session's entry below.

**July 17, 2026 (session 2):** Two-column-alternating inline bullet list feature — see that session's entry below.

**July 17, 2026 (session 1):** Split Header + Contact Columns block built; contact form opacity/paragraph improvements; global `p + p` spacing; Contact page pattern created and registered; six-column-icons and split-column-icons blocks built (prior sessions, uncommitted until now).

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

### Done since last update ✅ (July 30, 2026)

- **Photo Carousel: new optional intro body text below the heading** (`field-photos/block.json`, `edit.js`, `render.php`, `style.css`) — Lauren asked for the ability to add introductory body copy underneath the block's intro heading, matching the intro-body pattern already established in `un-goals-nav`. Added a new `introBody` string attribute (default empty), a `RichText` field in the canvas (`tagName="div"`, `className="section-body"`, `allowedFormats: ['core/bold','core/italic','core/link']`) rendered inside the existing `.fph-header` alongside the eyebrow/heading inputs, and the matching `render.php` output (`wp_kses_post()`-sanitized). Reused the sitewide `.section-body` class from `styles/shared.css` for base typography (font-size, gray-700 color, margin-top) rather than inventing new CSS — the only new rule needed was a Deep-Blue-background override (`.fph-section--bg-deep-blue .section-body { color: rgba(255,255,255,0.85); }`), following the same pattern already used for the eyebrow/heading. The header-visibility check in both `edit.js` and `render.php` was extended so the header now shows if the intro body has content even when eyebrow and heading are both toggled off.
  - Verified via a scoped webpack build (zero errors) and an adversarial review pass (checked shared-CSS class inheritance given `.fph-header` doesn't carry the `section-header` class other blocks use for width-constraint, `wp_kses_post()` compatibility with the RichText's allowed formats, editor/front-end visibility parity, and undefined-attribute safety) — no bugs found. One cosmetic, non-blocking note: `.fph-header`'s own `max-width: 40rem` constrains the body text to a wider measure (~640px) than the ~52ch convention other blocks get from the sitewide `.section-header .section-body` rule, since `.fph-header` was never given that class — left as-is since it's pre-existing design intent for this specific block, not a bug.
  - **Needs a build + rsync**: `field-photos/block.json`, `edit.js`, `render.php`, `style.css` are all webpack-bundled for this block.

- **Job Openings block now sources data through the installed Workable plugin instead of a direct API call** (`inc/workable-jobs-api.php`, `src/blocks/job-openings/render.php`, `src/blocks/job-openings/block.json`, `inc/cropx-settings.php`) — Lauren installed the third-party "Workable API" plugin (`jlvanhulst/Workable-for-Wordpress`, a mirror of `kanopi/wp-workable`) on staging, which has its own Settings → Workable API credentials page and its own `Workable_Api_Wrapper` class/HTTP handling/caching. She asked for the existing Job Openings block — built July 29 against our own direct `wp_remote_get()` call — to be rewired to pull through the plugin instead, keeping the exact same visual design.
  - **`cropx_get_workable_jobs()` rewritten** to read credentials from the plugin's `workable_api_options` option (`field_workable_subdomain`, `field_api_key`) instead of our own `cropx_workable_subdomain`/`cropx_workable_api_key`, and to call `$workable->get_jobs(['state' => 'published'])` on a `new Workable_Api_Wrapper(...)` instance instead of building our own request. Return shape (`{configured, error, jobs: [{title, department, location, url}, …]}`) is completely unchanged, so `render.php`'s consumption logic needed zero changes beyond copy updates.
  - **Failed-fetch transient self-heal**: the plugin caches even failed lookups for 2 hours in its own transient; added a `delete_transient()` call on our error path so a credentials fix takes effect on the very next page load rather than silently waiting out the cache. Confirmed via an adversarial review (which fetched the plugin's actual source from GitHub rather than guessing) that our transient key construction happens to match the plugin's exactly today, but flagged as fragile — added a code comment warning that if the `get_jobs()` params array ever grows beyond the single `state` key, the two key-building lines must be kept in sync by hand or the cache-clear will silently stop working.
  - **Copy updated** in `render.php`'s two admin-only notice strings and doc comment, and in `block.json`'s description, to point to "Settings → Workable API" instead of "Settings → CropX."
  - **Removed the now-redundant `cropx_workable_subdomain`/`cropx_workable_api_key` fields from Settings → CropX** (`inc/cropx-settings.php`) — the whole "Workable Job Board" settings section, its two `register_setting()` calls, `add_settings_field()` calls, and both renderer functions — since credentials now live exclusively on the plugin's own settings page and leaving the old fields in place would just confuse Lauren with two competing places to enter the same credential (only Mapbox settings remain on Settings → CropX now).
  - Verified via a scoped webpack build (confirmed the updated `block.json` description and `render.php` copy landed correctly in `build/blocks/job-openings/`) and an adversarial review pass that independently fetched the plugin's real source from GitHub to check the transient-key assumption, field-name assumptions (`title`, `department`, `location.city/country/location_str/telecommuting`, `application_url`/`url`/`shortlink`), the `Workable_Api_Wrapper` constructor's side-effect safety, and `cropx-settings.php`'s brace/paren balance after the removal — no bugs found.
  - **Needs a build + rsync**: `src/blocks/job-openings/block.json` is webpack-bundled (already scoped-built this session); `inc/workable-jobs-api.php` and `inc/cropx-settings.php` are PHP-only — rsync covers them, no build needed for those two.

### Done since last update ✅ (July 29, 2026)

- **New block: "Job Openings (Workable)"** (`cropx/job-openings`, five new files in `wp-theme/cropx/src/blocks/job-openings/`: `block.json`, `index.js`, `edit.js`, `render.php`, `style.css`, `editor.css`, plus `editor.css`) — Lauren asked how hard it'd be to pull live job listings from Workable (the ATS the old Elementor site uses via a `workable.com/assets/embed.js` script embed) onto the new site, styled to match CropX's own design instead of Workable's generic default look. She doesn't have a Workable API key yet, so this was built to be immediately useful for reviewing the *styling* now, and go live the moment she adds real credentials — no further code changes needed at that point.
  - **Two integration options exist**: Workable's own drop-in embed widget (what the old site uses — zero setup, but locked into Workable's own boxy default styling) vs. their REST API (`GET /spi/v3/jobs`, needs a read-only Bearer token, `r_jobs` scope, free on any plan). Went with the API approach since it lets job rows be styled with CropX's own tokens/typography instead of an embedded widget.
  - **Credentials live in Settings → CropX, not block attributes** (`inc/cropx-settings.php`, new "Workable Job Board" section: `cropx_workable_subdomain` + `cropx_workable_api_key`, following the exact pattern already established for the Dealer Finder block's Mapbox token). This matters because block attributes are stored in `post_content` and are readable by anyone who can view a page's raw content or the REST API — not an acceptable place for a bearer token. The API key is read via `get_option()` only inside server-side PHP and never touches JS, block attributes, or REST-exposed settings (`show_in_rest` intentionally omitted).
  - **New `inc/workable-jobs-api.php`**: `cropx_get_workable_jobs()` calls Workable's Jobs API server-side via `wp_remote_get()` with a `Bearer` auth header (their API is CORS-blocked from front-end JS, confirmed via their own docs), filters to `state === 'published'` jobs only, normalizes each into `{title, department, location, url}`, and caches the result in a 15-minute transient (keyed per subdomain) to stay well under Workable's 10-requests/10-seconds rate limit. Returns a `{configured, error, jobs}` shape so `render.php` can tell "not set up yet" apart from "set up but the API call failed" apart from "working, here's the real (possibly empty) list." `cropx_get_sample_workable_jobs()` returns five realistic placeholder listings for the not-yet-configured state.
  - **Three-state front-end behavior** (`render.php`): not configured → sample placeholder jobs render, with a small dashed-border "Preview data" notice shown **only to logged-in admins** (`current_user_can('manage_options')` — a regular site visitor never sees it, even in view-source); configured but the live fetch fails → same sample fallback, notice text says the connection failed instead; configured and working → real jobs, or the block's own configurable empty-state message if there are genuinely zero open roles.
  - **Styling**: a clean stacked list of clickable rows (`.cjo-job`), each a `--card-shadow` white card with `--radius-lg` (6px, not the asymmetric `--radius-card` signature shape — that's reserved for feature cards/photos, and looked wrong repeated a dozen times in a dense list) containing the job title, a "Department • Location" meta line, and a trailing arrow that shifts on hover — reusing the same arrow-hover motif established in `un-goals-nav`'s CTAs. Supports White/Taupe/Deep Blue backgrounds (Deep Blue gets the same animated topography pattern used across every other Deep Blue CropX section) and an optional heading via the shared `.section-header`/`.section-heading` classes.
  - **Editor canvas** shows the same 4 sample jobs (100% inline placeholder data — no live API calls from the browser, which wouldn't work anyway due to CORS) with a small dashed "Preview data" badge above the list and a sidebar note explaining that real jobs load server-side once Workable is connected.
  - Verified via a scoped webpack build (zero errors) and an adversarial review pass (checked for API-key leakage into JS/attributes/REST, WP_Error handling order around `wp_remote_get()`, malformed-JSON safety, PHP tag balance, and admin-only notice gating) — one minor defensive fix applied (guarding against Workable ever returning `location` as a non-array), otherwise clean.
  - **Next step for Lauren**: once the Workable API key is in hand, add it under Settings → CropX (Workable Subdomain + API Key) and the block switches from sample to live data automatically — no further code or rebuild needed for that step.
  - **Needs a build + rsync**: the 5 new block files are webpack-bundled; `inc/cropx-settings.php`, `inc/workable-jobs-api.php` (new), and `functions.php` are PHP-only (rsync covers them, no build needed for those three).

- **"3-Column Content Cards" block: category filter for Blog Posts in Auto mode** (`block.json`, `edit.js`, `render.php`, all in `wp-theme/cropx/src/blocks/cards/`) — Lauren noticed that in Auto (latest posts) mode with Post Type set to Blog Posts, there was no way to filter which posts show up, unlike Customer Stories mode which already had a content-type checkbox filter. Root cause: the existing content-type filter (`queryContentTypes` attribute, `cropx_content_type` taxonomy) was hardcoded to only appear/apply when `queryPostType === 'cropx_publication'` — there was no equivalent for Blog Posts at all, not a bug in existing code, just a feature that was never built for that post type. Added a new `queryCategories` attribute (array of category slugs, default empty) and a matching checkbox filter in the "Auto Query" sidebar panel, shown only when Post Type is Blog Posts, sitting alongside (not replacing) the existing content-type checkboxes. The category list is fetched live via `useSelect` + `select('core').getEntityRecords('taxonomy', 'category', ...)` — the first time this codebase has queried a *taxonomy's terms* via the block editor's REST data layer (every prior `useSelect`/`getEntityRecords` call in the theme fetches *posts*, not taxonomy terms) — only firing the fetch when Blog Posts is actually selected, to avoid an unnecessary REST call otherwise. `render.php` mirrors the existing `cropx_publication` + `cropx_content_type` tax_query block with an equivalent one for `post` + WordPress core's built-in `category` taxonomy (no CPT-style taxonomy registration needed, since `category` already exists on the `post` post type out of the box). Verified via a scoped webpack build (zero errors) and an adversarial review pass (checked for REST-fetch race conditions, array mutation bugs, JSX fragment balance, and mutually-exclusive tax_query branches) — no bugs found.
  - **Needs a build + rsync**: `block.json`, `edit.js`, `render.php` all changed and are webpack-bundled for this block.

- **Auto-advance on/off toggle added to all four carousel/marquee blocks, plus a Photo Carousel edge-sliver fix** — Lauren asked for an editor toggle to auto-advance the Photo Carousel (matching the pace of the site's other auto-advancing carousels), a fix for a rough "sliver" of the next photo peeking in at the right edge of the Photo Carousel track, and then asked for the same on/off toggle to be extended to every other carousel/marquee block. Confirmed by grep across the whole block library that there are exactly four such blocks — Photo Carousel, Testimonials Carousel, Hardware Lineup, and Logo Carousel — no others exist.
  - **New shared helper** `src/shared/autoAdvance.js` (`initSnapAutoAdvance()`) — a timer that advances a native scroll-snap carousel to its next slide every 5 seconds (chosen since the two pre-existing auto-advancing blocks use a continuous-scroll marquee mechanism with a pixels/second speed that doesn't translate into a comparable "seconds per slide" figure — 5s felt like a natural, unhurried default and matches the `resumeDelayMs` pattern already used elsewhere), wrapping back to the first slide after the last, and pausing on hover, on any recent manual interaction (matching Hardware Lineup's existing pause behavior), and while the carousel is scrolled out of view (via `IntersectionObserver`, so it doesn't silently rack up skipped advances off-screen and then jump several slides at once when scrolled back into view). Respects `prefers-reduced-motion`.
  - **Photo Carousel** (`field-photos/`): new `autoAdvance` attribute, default **off** (this block never auto-advanced before, so off preserves existing appearance) — toggle in the Section Settings panel. `view.js` wires the shared helper onto the block's existing index-tracking functions when enabled.
  - **Testimonials Carousel** (`testimonials-carousel/`): same pattern, new `autoAdvance` attribute, default **off** for the same reason.
  - **Hardware Lineup** (`hardware-lineup/`): this one already always auto-scrolled (continuous RAF-driven marquee), so its new `autoAdvance` attribute defaults to **on**, preserving current behavior until someone turns it off. The toggle just gates the existing `shouldAutoScroll()` check — manual arrow-click navigation is completely unaffected either way.
  - **Logo Carousel** (`logo-strip/`): also always auto-scrolled (pure CSS `@keyframes`, no JS at all), so its new `autoAdvance` attribute also defaults to **on**. Toggling it off adds a `.ls-marquee--static` class that sets `animation: none`.
  - **Photo Carousel edge-sliver fix** (`field-photos/style.css`): the horizontally-scrolling track's item widths were computed as `calc((100% - gaps) / count - 1px)` — subtracting a tiny 1px safety margin *after* dividing by the item count left too little cumulative slack across the whole row, and sub-pixel flex-layout rounding could fully consume it on certain viewport widths, letting a sliver of the next (out-of-view) photo peek through since the track scrolls rather than clips. Fixed by subtracting a larger 6px margin *once, before* dividing (`calc((100% - gaps - 6px) / count)`), at all three responsive breakpoints (desktop 3-up, tablet 2-up, mobile 1-up) — a small, consistent, and much more rounding-resistant cushion. **Note for Lauren: this was diagnosed and fixed from code analysis alone — the screenshot referenced in the request didn't actually attach, so please take a look on the live site and flag it if the sliver is still visible anywhere.**
  - Verified via a scoped webpack build (zero errors across all four blocks) and two adversarial review passes (parameter-name matching between the shared helper and each call site, data-attribute-to-dataset camelCase conversion, interop between auto-advance and existing manual arrow/dot navigation, CSS specificity and flex-basis math, zero/one-item edge cases) — no bugs found.
  - **Needs a build + rsync**: all four blocks' JS/CSS changes are webpack-bundled (`field-photos`, `testimonials-carousel`, `hardware-lineup`, `logo-strip`, plus the new `src/shared/autoAdvance.js`).

- **Fixed: Photo Carousel crashing in the editor when the Corner Radius panel is opened** (`field-photos/edit.js`, `src/admin/editor-panels.js`) — Lauren reported the block showing "This block has encountered an error and cannot be previewed" after opening Corner Radius, which also made the new Auto-advance toggle and every other sidebar control disappear for that block instance. Root cause: both the Photo Carousel's own Corner Radius panel and the sitewide "every `core/image` gets a Corner Radius panel" feature (`editor-panels.js`, from the July 27 session) used WordPress's `BorderRadiusControl` component from `@wordpress/block-editor`. That component's availability/stability has proven inconsistent across WP/Gutenberg core versions — if it resolves to `undefined` on a given site, rendering `<BorderRadiusControl>` throws the instant that JSX actually mounts. Since the panel had `initialOpen={false}`, the crash was invisible until Lauren expanded it — and because there's only one error boundary per block (not per panel), the crash took down the whole Edit tree, hiding every other control including Section Settings (where Auto-advance lives).
  - **Fix**: replaced `<BorderRadiusControl>` in both locations with a plain 4-corner grid of `TextControl` inputs (Top left / Top right / Bottom left / Bottom right, accepting any CSS length like `40px`, `1rem`, `50%`) — `TextControl` has no version-compatibility risk. Behavior is otherwise identical: the "Use CropX Photo Radius" preset button and "Reset to default" button both still work, and attribute names/storage are unchanged, so no existing customized-radius content is affected.
  - This means the reported "Auto-advance isn't appearing" was very likely this crash hiding the whole sidebar, not a missing feature — the toggle was there in the code the whole time (verified via a scoped webpack build and an adversarial review pass — clean).
  - **On the sliver/edge-cutoff fix**: the editor's Photo Carousel canvas is a static read-only thumbnail grid (`.fph-canvas-preview`), a completely different, non-scrolling layout from the front-end's actual horizontally-scrolling track (`.fph-track`) that the sliver fix targets — the editor canvas was never able to show that bug or its fix. Please check the **published/front-end page** to confirm the sliver is gone, not the block editor canvas.
  - **Needs a build + rsync**: `field-photos/edit.js` and `src/admin/editor-panels.js` are both webpack-bundled.

- **Photo Carousel edge-sliver fix, take three — actual root cause** (`field-photos/style.css`) — the earlier rounding-margin theory (flex-basis calc cushion) didn't fix it; Lauren sent an actual screenshot at ~1660px browser width that showed the real cause: once the browser window is wider than the site's max content width (`var(--max-w)`, 72rem), `.fph-viewport`'s own `max-width` kicks in and centers the carousel with white space on either side — at that point the existing 2rem (32px) edge inset (sized for the shadow-bleed trick) left a partially-visible photo sitting flush against that centered column's boundary with no breathing room, reading as a jarring hard cutoff. Lauren proposed the fix directly: shrink the edge inset to a slim 10px once the window reaches that width. Added `@media (min-width: 72rem) { .fph-viewport { padding-left/right: 10px } .fph-track { margin-left/right: -10px; padding-left/right: 10px; scroll-padding-inline: 10px } }` — both the viewport and the track's matching negative-margin/padding pair had to shrink together, since the track's shadow-bleed trick only cancels out correctly when its negative margin exactly matches the viewport's own padding value. Verified via a scoped webpack build (zero errors).
  - **Needs a build + rsync**: `field-photos/style.css` only (webpack-bundled).

- **New block: "UN Sustainability Goals Nav"** (`cropx/un-goals-nav`, six new files in `wp-theme/cropx/src/blocks/un-goals-nav/`: `block.json`, `index.js`, `edit.js`, `render.php`, `style.css`, `editor.css`) — built from a mockup showing a centered page heading ("Supporting the United Nations Sustainable Development Goals") above a 3-column grid of items, each with a square badge/icon image on the left and eyebrow+heading+CTA links on the right. Several design decisions were confirmed with Lauren via clarifying questions before building:
  - **Data model**: a single `items` array attribute (unlike stats-grid's fixed named slots), up to 12 items, each an object with `imageId`/`imageUrl`/`imageAlt` (uploaded image, not a picked icon slug), `showEyebrow`/`eyebrow`/`eyebrowColor` (per-item toggle + color choice of Neutral Gray/CropX Blue/Deep Blue — deliberately editor-selectable per item, per Lauren's request), `heading`, optional `body`, and up to 3 `ctas` (`{label, url}` each).
  - **Grid**: fixed 3-column desktop layout (not configurable), collapsing to 1 column at 900px — deliberately mirrors `cropx/three-column-icons`'s exact breakpoint behavior, including reusing that block's shared `.section-header`/`.section-heading`/`.section-eyebrow` global classes so the block heading gets the same clamp()-based fluid sizing for free.
  - **Image framing**: reuses `cropx/un-goals-two-column`'s icon box treatment exactly (4px border-radius, no accent tint, no white filter, `object-fit: cover`), but sized via a new block-level `iconSize` attribute (RangeControl, 48–140px, default 72px) applied uniformly to every item via a `--usn-icon-size` CSS custom property — Lauren confirmed one-size-for-all over a per-item control.
  - **Type scale**: item headings use the `--fs-h3` token (1.375rem) rather than a one-off value Lauren originally asked for; body text and CTA links are a literal 1.1rem (no matching token exists, and the size was a deliberate custom choice, so left un-tokenized).
  - **Heading alignment**: new `headingAlign` attribute (left/center toggle, defaults to center), implemented by reusing the shared `.section-header--left` modifier plus a `.usn-header`-specific margin toggle so the heading's own box — not just its text-align — shifts between centered and flush-left.
  - **Deep Blue contrast fix**: matching the existing pattern in the Photo Carousel/field-photos block, when the section background is Deep Blue, a per-item eyebrow's inline color style is suppressed in both `render.php` and `edit.js`, with a `.usn-section--bg-deep-blue .usn-eyebrow` CSS override applying a readable light color instead — otherwise an item set to "Deep Blue" eyebrow color would render invisible text on a Deep Blue section.
  - **Inspector UI**: one collapsed `PanelBody` per item ("Item N — <heading>"), matching `stats-grid`'s per-item accordion pattern rather than `field-photos`' compact draggable-row pattern — chosen because each item here carries far more fields (image, eyebrow toggle+color, heading, body, up to 3 CTAs) than a compact row comfortably fits.
  - **Two bugs caught and fixed in an adversarial verification pass**: `render.php`'s `cropx_usn_eyebrow_var()` helper (maps the `eyebrowColor` attribute to a `--gray-700`-style CSS token name) was initially a bare top-level function, which would fatal-error with "Cannot redeclare function" if the block appeared twice on one page — now wrapped in `if ( ! function_exists( 'cropx_usn_eyebrow_var' ) ) : ... endif;`, matching the same guard already used in `cards/render.php`, `product-tabs/render.php`, `product-grid/render.php`, and `product-sections/render.php`. Also, the uploaded image's alt text was being passed through `esc_attr()` before `wp_get_attachment_image()`, which double-escapes since WP core already escapes the `alt` attribute itself — fixed by passing the raw alt text.
  - Verified via a scoped webpack build (webpack 5.106.2, zero errors). Registers automatically via `inc/blocks.php`'s glob over `build/blocks/*` — no manual wiring needed.
  - **Needs a build + rsync**: all six files are brand-new — `npm run build` + the standard rsync to the local WP install required before this block appears in the inserter.

- **"UN Sustainability Goals Nav" block: full feedback revision round, same day** (`block.json`, `edit.js`, `render.php`, `style.css` all in `wp-theme/cropx/src/blocks/un-goals-nav/`) — four rounds of follow-up changes Lauren requested after reviewing the initial build:
  - **Per-item eyebrow color moved to block-level, renamed `itemEyebrowColor`, default changed to Deep Blue.** The block originally let each of the 12 items pick its own eyebrow color independently; Lauren asked for a single setting that applies to every item at once, defaulting to Deep Blue instead of the original per-item default. The per-item `eyebrowColor` field was removed from each item's sidebar panel (replaced with a help note pointing up to Section Settings), and the attribute was promoted to block-level and renamed `itemEyebrowColor` (to disambiguate from the new intro eyebrow color added later the same day — see below) with its enum order changed so Deep Blue is now the default/first option. Propagated the rename through every call site in `edit.js` (destructure, the `SelectControl`, and the canvas eyebrow-color-preview helper) and `render.php` (the attribute read, its enum validation array, the PHP variable name, and the `$eyebrow_style`/`cropx_usn_eyebrow_var()` call site) — verified via an adversarial review pass that no stale bare `eyebrowColor` references were left behind anywhere.
  - **Drag-and-drop item reordering**, alongside the existing up/down buttons. Each item's sidebar `PanelBody` is now wrapped in a draggable `<div>` using native HTML5 drag-and-drop (matching `cropx/field-photos`'s established pattern exactly, including the small SVG grip-icon affordance and an `.is-dragging` CSS state with reduced opacity + dashed outline). `src/shared/reorder.js`'s `reorderByDrag()` helper handles both the new drag-drop moves and the existing up/down buttons (which previously used a since-removed `moveItem()` helper — now unused everywhere in this block).
  - **CTA line-height and arrow-wrap fix.** `.usn-cta` line-height reduced to 1.3 per Lauren's request. Separately, Lauren flagged via annotated screenshot that the trailing arrow icon was floating in its own vertically-centered slot next to a wrapped multi-line CTA label instead of sitting right after the label's actual last line. Root cause: `.usn-cta` was `display: inline-flex`, which lays out the (possibly-wrapped) text and the icon as two independent flex items. Fixed by switching the anchor to `display: block; width: fit-content;` (so its text content wraps as normal inline content) with the icon as an `inline-block` sibling, and inserting a non-breaking space between the label and the icon in the markup so the browser's line-wrap algorithm can never strand the icon alone on a new line.
  - **New: optional intro eyebrow + intro body around the block's own heading.** Lauren asked for an eyebrow above the block's intro heading and body text below it (the heading itself already existed). Added four new block-level attributes — `introEyebrow`, `showIntroEyebrow` (default on), `introEyebrowColor` (CropX Blue / Deep Blue / White, default CropX Blue — matching the block-level eyebrow pattern already used by `three-column-icons`/`stats-grid`/`un-goals-two-column`), and `introBody` — edited via canvas `RichText` fields (not sidebar text fields, since that's this codebase's established convention for a block's own header content vs. the per-item repeater fields, which do use sidebar controls) reusing the shared global `.section-eyebrow`/`.section-heading`/`.section-body` classes already loaded by `shared.css`, so no new CSS classes or responsive sizing rules were needed for typography. A new "Intro Header" sidebar panel holds the show/hide toggle and color picker. Unlike the per-item eyebrow color, the intro eyebrow needs no Deep-Blue-background suppression logic, since it already offers an explicit "White" option. The intro body has no color picker at all (it's plain optional copy) — a new `.usn-section--bg-deep-blue .section-body` CSS rule auto-flips it to a light, legible color on a Deep Blue background, matching the same pattern already used by `stats-grid`'s intro body. `render.php`'s `$has_header` check was extended to also consider the intro eyebrow and body (previously it only checked the heading).
  - Verified via a scoped webpack build (webpack 5.106.2, zero errors) and a full adversarial verification pass (checked for stale attribute-name references, enum-list cross-contamination, double-escaping, and Deep-Blue suppression scoping) — no bugs found.
  - **Needs a build + rsync**: `block.json`, `edit.js`, `render.php`, `style.css` all changed and are webpack-bundled for this block.

- **"UN Sustainability Goals Nav": CTA arrow-orphaning fix, take two** (`render.php`, `edit.js`, `style.css`) — the earlier same-day fix (nbsp between the CTA's last word and the arrow) turned out not to be enough on its own: Lauren caught via screenshot that the arrow could still land alone on its own line when a CTA label wrapped (e.g. "Increase Supply Chain Transparency" on one line, a lone arrow on the next). Root cause: the arrow `<svg>` is `display: inline-block`, and every browser gives an inline-block element an implicit line-break opportunity on either side of it, regardless of whether the adjacent space is breaking or non-breaking — a non-breaking space only suppresses breaking *at whitespace characters*, not this separate atomic-inline-box boundary. Fixed properly by splitting each CTA label into "everything but the last word" and "the last word," then wrapping "last word + nbsp + arrow" together in a new `<span class="usn-cta-tail">` with `white-space: nowrap` in CSS — this makes the pair a genuinely unbreakable unit, so if it doesn't fit at the end of a line, the whole pair (word + arrow) drops to the next line together, exactly as requested. `render.php` does the split with `strrpos( $cta_label, ' ' )`; `edit.js` gained a matching `splitCtaLabel()` helper so the canvas preview stays in sync with the front end. Verified via a scoped webpack build (zero errors) and an adversarial review pass — clean, though the review flagged one small pre-existing (not introduced by this fix) edge case worth a follow-up sometime: a CTA label that's only whitespace survives the `!empty()` visibility filter in both `render.php` and `edit.js` before being `trim()`'d down to an empty tail, which would render a bare arrow with no label text. Not fixed here since it wasn't part of what Lauren asked for this round.
  - **Needs a build + rsync**: same three files, webpack-bundled for this block.

- **Customer Story single template: duplicate share-buttons sidebar removed** (`single-cropx_publication.php`, `styles/pub-single.css`) — Lauren flagged via screenshot that published case-study pages were showing two identical sets of social share buttons (LinkedIn/X/copy-link). Root cause: when a Customer Story has none of its "Case Study Details" meta fields filled in (`cs_company`, `cs_region`, `cs_challenge`, `cs_solution`, `cs_scale` — i.e. `$has_cs_details` is false), the right-hand sticky sidebar fell back to rendering its own second `<aside class="pub-share-sidebar">` share block (LinkedIn/X/copy-link, copy button id `pub-copy-link`) — redundant with the inline share row that already renders below every article body regardless of meta fields. Fix: removed the entire `else` branch that built that duplicate sidebar (~30 lines) and replaced it with a bare `endif`, so when `$has_cs_details` is false the right sidebar no longer renders at all. Added a `pub-body-layout--no-sidebar` modifier class (alongside the existing `pub-body-layout--cs` modifier) to the body layout wrapper so the grid collapses to a single column when there's no sidebar content, with a matching `.pub-body-layout--no-sidebar { grid-template-columns: 1fr; }` rule added to `pub-single.css` next to the existing `.pub-body-layout--video` precedent. Also updated two stale doc comments (the top-of-file page-structure comment and the inline comment above the sidebar `if`) to describe the new "no sidebar at all" behavior. Verified no orphans: `pub-copy-link` (the removed sidebar's copy button id) has no remaining references anywhere in the theme; `assets/js/blog-single.js` only wires up the unrelated, still-present `bsingle-copy-link` (inline share row) and `pub-copy-link-inline` (a different pre-existing Download CTA box button) ids. PHP + CSS only — rsync only, no build required.

### Done since last update ✅ (July 28, 2026)

- **Core Table block: two sitewide CSS bug fixes** (`wp-theme/cropx/styles/content.css`, `styles/single.css`, `styles/pub-single.css` — CSS-only, rsync only, no build required):
  - **Mid-word text wrapping fixed.** Table cells were letting WordPress core's default `word-break: break-word` split ordinary short words mid-character on the published page (e.g. "Component" rendering as "Compone" / "nt"). `single.css`'s `td` rule already had a partial fix (`word-break: normal; overflow-wrap: break-word;`), but its sibling `th` rule — and `pub-single.css`'s `th` rule, and `content.css`'s `th`/`td` rules entirely — never got the same treatment. Since a column's width is driven by the widest requirement across *all* cells in that column (header included), one unfixed `th` was enough to undermine a fix already sitting on the `td`. Applied the same rule uniformly across all three files — `word-break: normal;` on both, `overflow-wrap: normal` on `th` / `overflow-wrap: break-word` on `td` — plus a new `min-width: 14ch;` floor on both. The `14ch` floor is what actually keeps ordinary words from wrapping: with `table-layout: auto` already in place, columns size to content, so a normal-length word (well under 14 characters) always has enough guaranteed column width to sit on one line; only a genuinely long unbreakable token (a long URL, say) that still doesn't fit even after the column grows falls back to a forced break. Using `ch` units keeps this viewport-independent (no breakpoint math), and each `.wp-block-table` wrapper's existing `overflow-x: auto` remains the escape valve for tight mobile widths.
  - **Missing blue accent band + stray divider line in the editor fixed.** Every table on the published page already got a Deep Blue 3px bottom accent (`border-bottom: 3px solid var(--deep-blue)` on the `.bsingle-article-content .wp-block-table` / `.pub-content .wp-block-table` wrapper in `single.css`/`pub-single.css`), but it was invisible inside the block editor because those two stylesheets are front-end-only (conditionally enqueued via `is_singular('post')` / `is_singular('cropx_publication')` in `inc/enqueue.php`) and never reach the editor iframe. The editor only ever loads `content.css` (enqueued via `enqueue_block_assets`, which fires in both contexts), and `content.css`'s generic `.wp-block-table` rule never had the accent band at all — so editors placing a table saw it just stop abruptly, plus WordPress core's own default `<thead>` bottom border showing through as a stray unstyled divider line (already cleared front-end-only in `single.css`/`pub-single.css`, but never in `content.css`). Fix: added the same `border-bottom: 3px solid var(--deep-blue);` to the generic `.wp-block-table` rule in `content.css`, plus `.wp-block-table thead { border-bottom: none; }` to clear core's default. Because `content.css` is the one stylesheet loaded everywhere, the band and the cleared divider now show correctly in the editor for every post type (Pages, Posts, Customer Stories).
  - **Note for Lauren**: this last fix is also a scope change worth a quick look — plain Pages previously had no bottom accent band on their tables at all (only Posts/Customer Stories did, via their own more specific stylesheets); they now get the same blue band on the front end too, as a side effect of the fix living in the universally-loaded `content.css`. Wasn't explicitly requested for that content type — flagging in case it's not wanted there.
  - **Follow-up fix, same day: blue accent band no longer overshoots the table edge in the editor.** Lauren spotted that the 3px Deep Blue bottom band — originally a `border-bottom` on the outer `.wp-block-table` wrapper (`content.css`) / `.bsingle-article-content .wp-block-table` wrapper (`single.css`) / `.pub-content .wp-block-table` wrapper (`pub-single.css`) — was visibly extending past the right edge of the table grid in the block editor. Root cause: these tables use `table-layout: auto`, which sizes the table to fit its content, while the wrapper is `width: 100%` and can render wider than the table itself, so a border on the wrapper doesn't reliably line up with the table's real edge. Fix: moved the band off the wrapper and onto the table's own last row instead — `tbody tr:last-child td { border-bottom: 3px solid var(--deep-blue); }` — in all three files, since a cell border is always exactly as wide as the table. Also added the matching override for the `.is-style-stripes` variant in all three files, since that variant already zeroes out `border-bottom` on every cell and needed its own explicit last-row rule to keep the band. The wrapper's own plain 1px gray border (`single.css`/`pub-single.css` only — `content.css` has no wrapper border) was left untouched; only the blue accent moved. CSS-only, no build step.

### Done since last update ✅ (July 27, 2026)

- **New block: "UN Goals - 2 Column Text & Photo"** (`cropx/un-goals-two-column`, six new files in `wp-theme/cropx/src/blocks/un-goals-two-column/`: `block.json`, `index.js`, `edit.js`, `render.php`, `style.css`, `editor.css`) — built as a near-duplicate of the existing `cropx/two-column` ("2 Columns with Text & Photo") block, same two-column layout (text column with eyebrow/heading/body/CTA on one side, a photo or transparent product PNG on the other, with all the same Photo Positioning controls: visual type, position, float, mobile stack, focal point/zoom/aspect ratio for photos). The one structural difference: instead of the standard icon system (a picked icon slug from the shared `IconPicker`, rendered white via CSS filter on a segment-accent-colored square), this block swaps in a custom image upload — meant for things like official UN Sustainable Development Goal badge icons, which are already full-color square graphics and shouldn't be tinted or boxed in an accent color.
  - New attributes `iconImageId`/`iconImageUrl`/`iconImageAlt` mirror the existing `photoId`/`photoUrl`/`photoAlt` pattern already used for the block's main visual, and are populated via the standard `MediaUpload`/`MediaUploadCheck` components (same as any other image attribute in this codebase — no new upload UI pattern introduced).
  - The uploaded image renders inside a 76×76px box (`.ugp-icon-box`) with 4px border-radius — same radius as the standard icon box, just scaled up from 56px to 76px — with `object-fit: cover` filling the box edge-to-edge. No accent-colored background fill and no `brightness(0) invert(1)` white filter, since the image is meant to display in its own original colors. `segmentAccent` is kept as an attribute (still drives the CTA and other segment-aware accents, same as `two-column`) but explicitly does not tint the icon image.
  - The editor sidebar (in Section Settings, in the slot where the icon toggle lives on `two-column`) has a small live thumbnail preview plus Select/Replace/Remove buttons. The toggle is still labeled "Show icon image" and the attribute is still named `showIcon`, kept consistent with the `two-column` pattern even though it now gates an uploaded image rather than a picked icon slug.
  - All CSS classes use a new `ugp-` prefix, mirroring `two-column`'s `tcv-` prefix 1:1 for every class (`.ugp-section`, `.ugp-grid`, `.ugp-content`, `.ugp-visual-col`, `.ugp-photo-wrap`, `.ugp-cta`, `.ugp-link`, segment accent classes `.ugp-segment-*`, etc.) rather than reusing `tcv-` — deliberately, so this block's CSS can never leak into or collide with `cropx/two-column` if both ever appear on the same page.
  - Block registration is automatic: `inc/blocks.php` globs `build/blocks/*` and calls `register_block_type()` on every folder, so no `functions.php`/`theme-setup.php` wiring was needed. The block sits in the existing `cropx-text-visual` category (already registered in `inc/theme-setup.php`), same as `cropx/two-column`.
  - **Needs a build**: all six files are brand-new, not modifications — `block.json`, `index.js`, `edit.js`, `render.php`, `style.css`, `editor.css`, all under `wp-theme/cropx/src/blocks/un-goals-two-column/`. `npm run build` + rsync required before this block appears in the WordPress inserter.

- **"UN Goals - 2 Column Text & Photo" block: CTA can now open a video lightbox instead of linking out** (`wp-theme/cropx/src/blocks/un-goals-two-column/` — `block.json`, `edit.js`, `render.php` modified, `view.js` new, `style.css` modified) — same-day follow-up to the block's initial build above. The CTA now has an optional video mode, reusing the lightbox pattern already duplicated in `cropx/video` and `cropx/two-column-video`. Since this was the third block to need it, the lightbox logic was extracted into shared modules rather than duplicated a third time:
  - **New shared files**: `wp-theme/cropx/src/shared/videoLightbox.js` (exports `getEmbedUrl(url)`, `openLightbox(src, isMedia, title)`, `closeLightbox()`, and a new generalized `initLightboxTriggers(selector)` that wires click + keyboard activation on any element with a `data-video-url` or `data-media-src` attribute — a faithful extraction of what `cropx/video` and `cropx/two-column-video` already had duplicated) and `wp-theme/cropx/src/shared/video-lightbox.css` (the `.cropx-vid-lightbox*` overlay styles, extracted from `cropx/video`'s stylesheet). `cropx/video` and `cropx/two-column-video` were deliberately left untouched — still using their own duplicated copies — to avoid risking regressions in two already-working blocks; only the new UN Goals code consumes the shared module. Noted in passing, not fixed: `cropx/two-column-video` never had its own copy of this CSS and silently relies on `cropx/video` also being on the page — a pre-existing gap, left alone in this pass.
  - **`block.json`**: added `ctaOpensVideo` (boolean), `ctaVideoSource` (enum `url`/`media`), `ctaVideoUrl`, `ctaVideoMediaId`, `ctaVideoMediaSrc` attributes, plus `"viewScript": "file:./view.js"`.
  - **`edit.js`**: CTA panel gained a "CTA opens a video" toggle; when on, a source picker appears (YouTube/Vimeo URL field, or a MediaUpload button with Replace/Remove for an uploaded file). The link-style CTA's canvas preview swaps the arrow icon for a play-triangle icon when video mode is on (button-style CTA preview only changes its label).
  - **`render.php`**: resolves the actual video src — `wp_get_attachment_url()` from the stored media ID first, falling back to the stored URL string — and when video mode is on, renders the CTA as a `<button type="button">` carrying `data-video-url` or `data-media-src` instead of an `<a href>` (there's nowhere to navigate to). Link-style video CTAs get the play-triangle icon in place of the arrow.
  - **`view.js`** (new): imports `initLightboxTriggers` from the shared module and wires it to `.ugp-section .ugp-cta--video, .ugp-section .ugp-link--video`.
  - **`style.css`**: added the `@import` for the shared lightbox CSS, plus a `.ugp-cta--video, .ugp-link--video` rule that strips native `<button>` chrome (border, font, cursor, appearance) while leaving color/background alone since those already come from the base `.ugp-cta`/`.ugp-link` rules.
  - Verified via a scoped webpack build (temporary entry pointing only at this block's `index.js` and `view.js`, using the project's existing `@wordpress/scripts` config) — compiled with zero errors, confirming the CSS `@import`, the new MediaUpload/ToggleControl/SelectControl JSX, and the shared-module import path all resolve. A second-pass adversarial review checked attribute-name consistency across all three files, PHP if/endif balance in `render.php`, CSS class/selector matching between `view.js` and `render.php`, and confirmed `cropx/video` and `cropx/two-column-video` were not touched — no issues found.
  - **Needs a build**: the 2 new shared files (`src/shared/videoLightbox.js`, `src/shared/video-lightbox.css`) plus the 4 changed files under `src/blocks/un-goals-two-column/` (`block.json`, `edit.js`, `render.php` modified; `view.js` new; `style.css` modified). Only a scoped verification build has been run so far (block-only entry, not the full theme) — a full `npm run build` across all blocks + rsync to the local WP install is still pending before this goes live.

- **"Text with Stats Grid" block: added a left-border toggle** (`block.json`, `edit.js`, `render.php`, `style.css`, all in `wp-theme/cropx/src/blocks/stats-grid/`) — new `showBorder` boolean attribute (default `true`, so no existing page changes visually until Lauren opts out), placed right after the existing `showIcons` attribute in `block.json`. This lets Lauren turn off the segment-accent-colored left border on each stat card for pages where it's not wanted. `render.php` parses `$show_border = (bool)($attributes['showBorder'] ?? true)` and conditionally appends `sg-stat-card--no-border` to the `.sg-stat-card` div's class when off. `edit.js` destructures `showBorder` from attributes and adds a matching `ToggleControl` ("Show left border") in the Section Settings panel, directly below "Show icons" (help text: "The segment-accent-colored border on each stat card. When off, the card's left padding is removed too."), applying the same conditional class to the canvas preview's `.sg-stat-card` div so the editor stays in sync with the front end. `style.css`'s new `.sg-stat-card--no-border` rule sits right after the base `.sg-stat-card` rule and removes both `border-left` and the card's left padding — the padding only existed to clear the border, so leaving it in place with the border gone would have left a phantom indent on the card content. **Needs a build**: all four files (`block.json`, `render.php`, `style.css`, `edit.js`) are webpack-bundled for this block.

### Done since last update ✅ (July 26, 2026)

- **`cropx/icon-columns`: added a JS layer for a hard item-count guarantee on the last column** (new `view.js`, new `src/shared/columnDistribute.js`, `edit.js`, `style.css`, `render.php`, `block.json`) — a same-day follow-up to the CSS multi-column change just below. Lauren asked whether the last (rightmost) column could be guaranteed to never have *more items* than any other column, since CSS multi-column's `column-fill: balance` only balances by rendered *height*, not item count, and can't offer a hard guarantee either way. After confirming a real guarantee requires JS regardless of whether it's height- or count-based, and that a count-based version would be simpler/more reliable (no waiting on rendered heights, just arithmetic), built:
  - **`src/shared/columnDistribute.js`** (new): `getEffectiveColumnCount(baseCount, width)` mirrors the exact breakpoint cascade already in `style.css` (1240/1020/820/600/320px), and `distributeIntoColumns(items, colCount)` splits a flat, ordered list into column-major groups using a **remainder-first** rule — any leftover items after dividing evenly go to the *earliest* columns, so the last column always gets the plain `floor(N / cols)` count, never more than any other column. Imported by both `view.js` and `edit.js` so front end and editor can never drift out of sync with each other.
  - **`view.js`** (new front-end script, registered via `block.json`'s new `viewScript`): on load and on debounced resize, reads the block's base column count from a new `data-base-columns` attribute on `.ici-columns` (added in `render.php`), computes the effective column count for the current width, and regroups the flat `.ici-item` list into real `.ici-col` wrapper divs — always re-grouping from each container's *original* flat item order (captured once up front) rather than from whatever the DOM currently looks like, so repeated resizes never drift. Adds an `.ici-columns--js` class once done.
  - **`style.css`**: added `.ici-columns.ici-columns--js` (switches the container from multi-column to a plain flex row) and `.ici-col` (a flex column, functionally the pre-Grid-rewrite `.ici-col` design, but this time correctly synced to the *actual* current viewport instead of a static PHP-side chunk — which is what caused the *original* ragged-layout bug in the first place, a mismatch between server-rendered grouping and CSS-only responsive rewrapping). The CSS multi-column rules from the change below are kept as-is and now serve as the no-JS fallback.
  - **`edit.js`**: added a `ResizeObserver` on the canvas's `.ici-columns` element (measuring the actual editor iframe width, not the outer browser window) and mirrors the exact same `columnDistribute.js` grouping to render real `.ici-col` divs in the canvas — so the editor preview always matches what `view.js` produces on the front end. `updateItem`/move/drag-and-drop calls are unaffected since each item's original flat index is threaded through the grouping unchanged.
  - **`render.php`**: added `data-base-columns` to `.ici-columns`; otherwise unchanged (still renders the flat item list — grouping is entirely `view.js`'s job now). `block.json` description updated (it had gone stale after the earlier Grid rewrite and still described the original independent-flex-column design — fixed now too).
  - **Needs a build**: `view.js`, `edit.js`, `style.css`, and `block.json` are all webpack-bundled for this block; `src/shared/columnDistribute.js` gets pulled in automatically by both.

- **`cropx/icon-columns`: CSS Grid → real CSS multi-column layout** (`style.css`, plus doc-comment/help-text updates in `render.php` and `edit.js`) — Lauren noticed the "Disease Control" page (and likely others using this block) still looked like it "broke oddly" as the viewport narrowed, even after the earlier Grid rewrite fixed the original ragged-bottom-edge bug. She correctly diagnosed the new cause herself: CSS Grid's row-major auto-flow sizes each *row* by its tallest item, and since which items share a row changes at every breakpoint (as the column count steps down), the vertical rhythm shifted unpredictably width to width — a different bug from the original one, but with a similar "uneven" symptom. She asked for a column-major layout again, but this time with each item's height driven purely by its own content rather than by whatever else happens to land in the same row. The right tool for that combination (column-major flow + per-item content height + no reintroduced ragged-count bug) is a real CSS multi-column container (`column-count`), not CSS Grid and not the original manual `array_chunk()` approach:
  - `.ici-columns` changed from `display: grid` to a plain multi-column container (`column-fill: balance`, the browser default, stated explicitly). `.ici-cols-4/5/6` changed from `grid-template-columns: repeat(n, 1fr)` to `column-count: 4/5/6` — same column-gap values as before.
  - The browser now fills the first column top-to-bottom, then the next, and so on (true column-major, matching what this block originally did before the Grid rewrite) — but instead of manually chunking items by raw index count (the old bug), the browser auto-balances *total rendered height* across columns via `column-fill: balance`, so an uneven item count no longer produces a visibly ragged bottom edge the way manual chunking did.
  - `.ici-item` gained `break-inside: avoid` (+ `-webkit-column-break-inside: avoid` for older Safari) so a single item's icon/heading/body/CTA can't get split across a column break.
  - Multi-column has no "row-gap" concept, so the vertical spacing between stacked items within the same column now comes from `margin-bottom` on `.ici-item` instead — set to the same values the old `row-gap` used (2.5rem for 4-col, 2rem for 5-/6-col, stepping to 1.5rem at ≤600px and 2rem at ≤320px, matching the previous schedule exactly).
  - All the same breakpoints (1240px/1020px/820px/600px/320px) carried over unchanged, just swapping `grid-template-columns` for `column-count` at each step — no other layout logic needed. `edit.js` needed no JS changes at all (it already just applies the same `ici-cols-N` class the front end uses), only its "Columns" help text and one code comment were updated to describe column-major flow instead of row-major.
  - **Needs a build**: `style.css` and `edit.js` are both webpack-bundled for this block.

- **"2x2 Stats Grid" block: up to 8 stats (2-8), always 2 columns, plus per-stat number size** (`block.json`, `edit.js`, `render.php`, `style.css`, `editor.css`) — per Lauren's request:
  - **Up to 8 stats, as few as 2**: this block started as a hardcoded "2×2" — four fixed attributes (`stat1Number`/`stat1Descriptor` ... `stat4Number`/`stat4Descriptor`), no concept of a variable count. Rather than convert those into an array (which per the block-development gotcha about schema changes would have reset every existing page's stat content back to defaults the moment the block updated), extended the same fixed-slot pattern out to 8 slots (`stat5`–`stat8`, new, default blank) and added a new `statCount` attribute (default `4`, clamped 2–8 in both `edit.js` and `render.php`) that controls how many of the 8 slots actually render. This means every page already using this block keeps its exact 4 stats, completely unaffected. In the editor, a "− Remove stat / + Add stat" control pair sits below the stat grid (bounded at 2 and 8, matching the block's own min/max); shrinking the count doesn't clear a slot's stored content, so growing back re-reveals it. `render.php` also drops any slot that's completely empty as a safety net, same filtering convention used in `icon-columns`, so a blank trailing slot never renders as an empty bordered box.
  - **2-column layout unaffected at any count**: `.sg-cards` was already a plain 2-column CSS Grid (`grid-template-columns: 1fr 1fr`) with native row-major auto-flow — no changes needed there. 2 stats fills one row, 3 wraps a lone third item to its own row's first column, 8 fills four full rows, etc., automatically.
  - **Per-stat number size**: new `stat1FontSize`–`stat8FontSize` attributes (enum `3rem`/`4rem`/`5rem`/`6rem`, default `5rem` — matching the block's one fixed size before this change), editable via a new "Stat Number Sizes" sidebar panel that only lists controls for the stats currently visible. `.sg-stat-number`'s old hardcoded `font-size: clamp(3rem, 7vw, 5rem)` became a `--sg-num-max`-driven formula (`clamp(calc(var(--sg-num-max, 5) * 0.6rem), calc(var(--sg-num-max, 5) * 1.4vw), calc(var(--sg-num-max, 5) * 1rem))`) — the exact same min/vw/max ratio the block already used for its one size, just generalized to whichever of the four max values a given stat is set to, so every option scales proportionally across viewport widths the same way `5rem` (→ `7vw` / `3rem`) always did. `render.php` and `edit.js` both set `--sg-num-max` inline per stat (as a unitless multiplier, e.g. `5`), so the editor canvas and front end always match.
  - **Needs a build**: `block.json`, `edit.js`, `style.css`, and `editor.css` are all webpack-bundled for this block.
  - **Follow-up, same session — optional per-stat icon**: added a "Show icons" toggle (default off, so no existing page changes visually until Lauren opts in) plus `stat1Icon`–`stat8Icon` attributes, so each stat can carry its own icon from the same 118-icon library used by `icon-columns` and the other icon-based blocks (imported the shared `src/shared/IconPicker.js` component rather than duplicating picker UI). The icon renders inside `.sg-stat-card`, above the number, as a standard icon box (`.sg-stat-icon` — 56px square, `var(--radius-md)`, white icon via `filter: brightness(0) invert(1)`). Its background is `var(--accent)` — the exact same CSS variable already driving each card's 6px left border — so the icon box automatically matches whichever segment accent color is selected in Section Settings, with no separate color control needed. A new "Stat Icons" sidebar panel (only shown when icons are on) lists an `IconPicker` per currently-visible stat, mirroring the "Stat Number Sizes" panel added just above it. `render.php` validates each stat's icon slug against the same allowlist icon-columns uses. **Needs a build**: `block.json`, `edit.js`, `style.css` all changed again.
  - **Follow-up, same session — renamed + Add/Remove moved to sidebar**: renamed the block from "2x2 Stats Grid" to "Text with Stats Grid" in the inserter (`block.json` title + description, since "2x2" was stale the moment the count became variable) and moved the "− Remove stat / + Add stat" controls out of the canvas and into the top of the "Section Settings" sidebar panel, per Lauren's request — the canvas now shows only the stat cards themselves, no editor-chrome buttons mixed in. `editor.css`'s `.sg-stat-count-controls` rule simplified accordingly (no longer needs to span `.sg-inner`'s two grid columns since it's not living in that grid anymore). **Needs a build**: `block.json`, `edit.js`, `editor.css`.
  - **Follow-up #2, same session — per-stat panels, matching Icon Columns' "Item N" pattern**: Lauren asked for each stat to get its own collapsible sidebar panel bundling BOTH its number-size control and its icon picker together (previously these lived in two separate "Stat Number Sizes" / "Stat Icons" panels, each listing all stats at once), with "Add stat" appearing below the last stat's panel — pointing to `icon-columns`' existing "Item N" panels as the reference pattern. Replaced both flat panels with one `PanelBody` per visible stat (titled "Stat N"), each containing the number-size `SelectControl`, the icon `IconPicker` (still gated on the global "Show icons" toggle in Section Settings, same as before), and a "Remove stat" button — enabled only on the *last* stat's panel (disabled on every other one, and at the 2-stat minimum), since stats are fixed, contiguous slots rather than a freely-reorderable list, so removing a middle one isn't a supported operation. The "− Remove stat / + Add stat" row that had just been moved to the top of Section Settings in the previous follow-up is gone entirely now — "+ Add stat" lives in one place, as a single button below the last stat panel (exactly where `icon-columns`' "+ Add item" button sits), and removal lives inside each stat's own panel instead of a separate global control. `editor.css`'s now-fully-unused `.sg-stat-count-controls` / `.sg-stat-count-label` rules were deleted. **Needs a build**: `edit.js`, `editor.css`.

- **"3-Column Segment Navigation" block: HTML anchor field added** (`segments/block.json`): added `"anchor": true` to the block's `supports` object so Lauren can set a jump-link ID in the editor's Advanced sidebar panel. No other file needed a change — this block already uses `useBlockProps()` in `edit.js` and `get_block_wrapper_attributes()` in `render.php`, both of which automatically wire up the anchor field/attribute once the support flag is on (WordPress core handles the "HTML anchor" UI and the `id="..."` output natively). **Needs a build**: `block.json` changes require `npm run build` to regenerate the compiled block metadata.

- **"CropX Pages" pattern library emptied** (`inc/patterns.php`): removed all 10 `register_block_pattern()` calls (Homepage, About, Products Hub, Hardware/Software Product, Contact, Blog Archive, and the three Segment patterns) per Lauren's request, so the inserter no longer offers these starter-page patterns. This is safe for every page already built from one of them: all 10 were standard (unsynced) patterns, meaning WordPress copies the pattern's content into the page's own stored block content the moment it's inserted — after that, the page has no live link back to the pattern registration, so removing the registration cannot alter, unlink, or break any existing page. Left in place: the `register_block_pattern_category('cropx-pages', ...)` call (harmless — WordPress hides a category with no patterns from the inserter automatically) and every physical `/patterns/*.php` source file (not deleted, just no longer wired up — happy to delete them too if you don't think you'll need them for reference, but wanted to check first before removing anything from the connected folder). The one actual Synced Pattern in the theme, `segment-navigation`, is a completely separate mechanism (database-stored, referenced live) and is untouched by this change. PHP-only — rsync only, no build required.

- **Curved hero blocks: even line-length wrapping on headings and subheadlines** (`hero-curved/style.css`, `hero-curved-standard/style.css`, `hero-blog/style.css`) — the three "— Curved Edge" hero blocks (Segment Hero, Standard Hero, Blog Hero) already balanced their headlines via `text-wrap: balance` in two of the three blocks, but every block's subheadline/intro-text line was set to `text-wrap: pretty`, which only prevents a single orphaned word on the last line — it does not even out line lengths across the rest of the block, which is what Lauren asked for. Changed all three subheadline rules (`.hc-subheadline`, `.shc-subheadline` ×2) from `pretty` to `balance`, and added the missing `text-wrap: balance` to `hero-blog`'s headline (`.hbl-block .shc-headline`), which had no `text-wrap` property at all — the only true gap found among the three blocks. `balance` is safe here since hero subheadlines only ever run 1–3 lines (the property's practical browser limit is well above that). **Needs a build**: all three are `style.css` files, webpack-bundled.

- **`cropx/icon-columns` block: switched from column-major chunking to a real CSS Grid** (`edit.js`, `render.php`, `style.css`) — Lauren caught this via screenshot on a live "Disease Control" page with ~30 short items in the 6-column layout: because the old design distributed items into independent flex column *stacks* (each column its own height, computed by chunking the flat item list with `array_chunk()`), an item count that didn't divide evenly across columns produced a ragged, uneven bottom edge — exactly the "breaks in weird ways" she flagged. That behavior was originally a deliberate design choice (documented as "a tall item in one column never shifts items in another"), which makes sense for a handful of long-form feature blurbs, but actively works against a grid of many short, uniform items like a disease/crop-model directory. Fixed by rewriting the block around a standard CSS Grid instead:
  - `render.php`: removed the entire `array_chunk()` distribution block (and the `$col_groups`/`$num_cols` variables that only existed to support it) — items now render in a single flat loop, in the order they're stored, directly as grid children.
  - `edit.js`: removed `getColRow()`, the `editorCols` chunking computation, and the "Col X, Row Y" per-item panel labels (now just "Item N", since position is no longer a fixed column/row pairing — it's wherever the row wraps naturally at the current viewport). The canvas preview now maps `columns` directly instead of mirroring PHP's chunking logic in parallel JS.
  - `style.css`: `.ici-columns` is now `display: grid` with `grid-template-columns: repeat(4/5/6, 1fr)` instead of a flex row of independent `.ici-col` stacks; `align-items: start` keeps each item at its own natural height without stretching to match a taller neighbor in the same row. This is what actually fixes the bug — CSS Grid's native row-major auto-flow wraps items into new rows automatically, and each row's height is set only by its own tallest item, so any item count (however unevenly it divides by the column count) flows cleanly with no ragged edges. The `.ici-col` class and all of its per-breakpoint `flex-basis: calc(...)` rules are gone entirely — the responsive breakpoints (1240px/1020px/820px/600px/320px, unchanged from the values already established) now just re-declare `grid-template-columns` with a plain `repeat(n, 1fr)`, which is both simpler and removes an entire category of by-hand percentage math.
  - **Needs a build**: `edit.js` and `style.css` are both webpack-bundled for this block.

- **Buttons block underline bug, found and fixed** (`styles/pub-single.css`, `styles/single.css`): Lauren flagged (via screenshot) that a core WordPress Buttons block still showed an underline on hover, even though buttons were already fixed sitewide in `content.css` months ago. Root cause: `.pub-content a` (Customer Story article body) and `.bsingle-article-content a` (blog post article body) are both unscoped, generic descendant selectors with higher specificity than `.wp-block-button__link`'s `text-decoration: none` in `content.css` — `.pub-content a` is `(0,1,1)` vs. `.wp-block-button__link`'s `(0,1,0)`, so it always won regardless of stylesheet load order. That's what put a dim, alpha-transparent underline on any button placed inside a Customer Story or blog post's body content, one that read as "becoming more opaque on hover" because the rule's `:hover` state swaps in a fully-opaque color. Fixed by excluding `.wp-block-button__link` from both selectors (`:not(.wp-block-button__link)`). Checked the rest of the theme for the same pattern (any generic, unscoped `<container> a` rule) — these were the only two; `content.css`'s own link rules (`.wp-block-paragraph a`, `.wp-block-list a`, `.wp-block-file a:first-child`) are all properly scoped and don't affect buttons. PHP + CSS only — rsync only, no build required.

- **`cropx/resource-downloads` ("Resource Grid") block: cover image no longer clickable** (`render.php`, `style.css`): per Lauren's request, removed the `<a>` that wrapped each card's cover image/thumbnail — the format buttons (US Letter / A4 / Download PDF) in the card body are now the only way to download a resource. Also removed the now-dead `.rsd-cover-link` CSS (including its hover zoom effect, since a non-interactive element shouldn't carry a hover affordance) and the now-unused `$primary_url` PHP variable that only existed to build that link's `href`. The editor preview (`edit.js`) never had this link to begin with, so no editor-side change was needed. PHP + CSS only — rsync only, no build required.

- **`cropx/resource-downloads` ("Resource Grid") block updated** (Lauren's request — button font size + new alignment controls):
  - **`.rsd-format-btn` font size** (`style.css`): changed from `0.8125rem` to `0.875rem`.
  - **New "Intro content alignment" control** (`block.json`, `edit.js`, `render.php`, `style.css`): new `introAlign` attribute (`'left' | 'center'`, default `'center'` — matches the block's existing look, so no visual change on any page already using it). Controls the eyebrow/heading/body block above the grid. New `.rsd-header--left` CSS modifier sets `text-align: left` and pins the block to the left edge (`margin-left: 0`) instead of centering it within its own max-width.
  - **New "Grid cards alignment" control** (same four files): new `gridAlign` attribute (`'left' | 'center'`, default `'center'`). This only has a visible effect when there are fewer resources selected than the column count — a full row always fills the grid edge-to-edge regardless. Previously this centering was automatic and non-optional (`count < columns` always triggered `.rsd-grid--centered`); now it's editor-controlled: `.rsd-grid--centered` is only added when `gridAlign` is `'center'` AND the row is partial. Choosing `'left'` lets a partial row sit in CSS Grid's default left-aligned auto-placement instead — no new CSS was needed for that state, since it's just the browser's native grid behavior.
  - **Needs a build**: `edit.js`, `block.json`, and `style.css` are all webpack-bundled for this block.

- **Blog post ToC now caps at h3** (`assets/js/blog-single.js`, `styles/single.css`): the sidebar table of contents on standard blog posts (`single.php`) previously scanned for h2/h3 as top-level entries and h4 as a nested sub-item. Per Lauren's request, headings below h3 (h4, h5, h6) should never appear in the ToC — so `buildToC()` now scans only `h1, h2, h3` (added h1 to the range too, per her "H1 to H3" wording, though blog posts rarely have one in the body since the post title itself is the page's h1) and lists them as a single flat, un-nested list. Removed the now-dead h4 sub-item branch from the JS and the corresponding `.bsingle-toc-sub` / `.bsingle-toc-sub-item` / `.bsingle-toc-sub-link` CSS rules, since that markup can no longer be generated. PHP + CSS/JS — plain files, not webpack-bundled — so rsync only, no build required.

- **`cropx/icon-columns` block updated** (Lauren's request — item cap, column options, rename, breakpoints):
  - **Item limit raised 18 → 36** (`edit.js`): the "+ Add item" cap and its "Maximum N items reached" label.
  - **Column count options narrowed to 4/5/6** (`block.json`, `edit.js`, `render.php`): 3 columns removed as an option entirely; 4 columns is now the default (was 3). `block.json`'s `columnCount` enum, `edit.js`'s `COLUMN_COUNT_OPTIONS` list and numeric fallback, and `render.php`'s enum validation + default all updated together. No existing patterns reference this block, so nothing needed re-inserting.
  - **Block renamed in the inserter**: `block.json` `title` changed from "Icon Columns (Column-Major)" to "4-6 Column Crop List with Icons" (and the description's "3–6" updated to "4–6"). This is a display-label-only change — the internal block name (`cropx/icon-columns`) is untouched, so this carries no migration risk for any pages that already use it.
  - **New unified responsive breakpoints** (`style.css`): previously each column count (3/4/5/6) had its own bespoke wrap pattern at different breakpoints. Since 3 columns is no longer possible and 4/5/6 should all behave the same way, replaced all of that with one shared set: wraps to 3 physical columns per row at ≤820px, 2 per row at ≤600px, and only fully stacks to 1 column at ≤320px (Lauren's explicit spec — a much narrower stack-to-mobile point than this block previously used). The old `.ici-cols-3` rules were deleted outright.
  - **Additional step-down breakpoints for 5- and 6-column layouts** (`style.css`, same session, follow-up request): a 6-column layout now also breaks to 5 physical columns at ≤1240px, then to 4 at ≤1020px; a 5-column layout breaks straight to 4 at ≤1020px. Both feed into the existing ≤820px/600px/320px steps below them. These new rules had to be placed *before* the ≤820px block in the stylesheet (not after) — CSS media queries using `max-width` all stay "true" below their own threshold, so with same-specificity selectors the rule that appears later in the source wins; the ≤820/600/320px rules need to keep overriding these new ones at narrower widths, so the new, wider-only rules had to go earlier in the file.
  - **Needs a build**: `edit.js` and `style.css` are both webpack-bundled — `npm run build` + rsync required (see command above). `block.json` and `render.php` are plain PHP/JSON, rsync only, but bundling them all in one build+rsync pass is simplest.

- **`cropx_testimonial` CPT renamed to "Quotes" in the admin UI** (same labels-only pattern as the Publications → Customer Stories rename — internal `post_type` slug, meta field names, and the block-editor panel plugin key all untouched):
  - `inc/cpts.php`: `register_post_type('cropx_testimonial', ...)` labels updated throughout (`name`, `singular_name`, `add_new_item`, `edit_item`, `not_found`, `not_found_in_trash`, `all_items`, `menu_name` all now say "Quote(s)" instead of "Testimonial(s)"), plus the top-of-file doc-comment summary line.
  - `inc/admin-help.php`: the `cropx_testimonial` help-panel block's title and opening body sentence now say "Quotes" instead of "Testimonials"; also updated the cross-reference inside the Customer Stories help block (`<em>Quotes</em> post type` instead of `<em>Testimonials</em> post type`).
  - `inc/admin-ui.php`: cosmetic admin-menu-order comment updated (`// Quotes`).
  - `src/blocks/testimonials-carousel/edit.js`: the empty-state message when no Quote posts exist yet now reads "The Quotes post type will be available once it is set up." **Needs a build** — this is a webpack-bundled JS source file, unlike the rest of this rename which is PHP-only.
  - Deliberately left unchanged: the "Video Testimonial" taxonomy term on Customer Stories (`cropx_content_type` — a completely different concept from this CPT), the `testimonials-carousel` / `testimonial-single` block names and their generic UI copy (block titles, "Pick testimonials," "+ Add testimonial," etc. — these describe the blocks' own content, not the CPT), and internal-only identifiers (`cropx-testimonial-avatar` image size, `editor-panels.js`'s `TestimonialPanels()`/panel keys) since none of those are user-facing CPT labels.

- **`cropx_publication` CPT renamed to "Customer Stories" in the admin UI** (`inc/cpts.php`): all labels (`name`, `singular_name`, `add_new_item`, `edit_item`, `all_items`, `menu_name`, etc.), meta box titles, and help-panel copy updated. The internal `post_type` slug (`cropx_publication`) and all existing single-post permalinks are untouched — this was a labels-only rename, no data migration, no broken links.
- **White Paper removed as a Customer Stories content type; replaced with Video Testimonial** (`inc/cpts.php`, `inc/customer-stories.php`, `single-cropx_publication.php`): White Papers now live under the `cropx_resource` CPT (Lauren added the term herself; `cropx_resource_type` seeded with a `white-paper` term to match). Every place that branched on content type — badge colors, download CTA copy, related-posts headings, plural label maps — updated from Case Study/White Paper to Case Study/Video Testimonial.
- **`/results/` rebuilt as a real, block-editable Page** ("Customer Results," slug `results`) instead of an automatic CPT archive:
  - `register_post_type('cropx_publication', ...)`: `has_archive` changed from the literal `'results'` to `false`, freeing up the `results` slug for a normal WP Page (WordPress auto-suffixes a Page slug that collides with a CPT's `has_archive` value, so archive had to be turned off for the Page to occupy `/results/` directly). Single-story permalinks are unaffected — they use a separate custom rewrite rule, independent of `has_archive`.
  - New `inc/customer-stories.php`: houses the shared grid-rendering logic and a new `[cropx_customer_stories_grid]` shortcode. The shortcode auto-detects `is_tax('cropx_content_type')` and filters/highlights itself to the active content type, so the same implementation powers both the unfiltered `/results/` view and the filtered `/content-type/{term}/` views. Also home to `cropx_get_customer_stories_url()` — a stable URL helper, since `get_post_type_archive_link()` always returns `false` now that `has_archive` is off.
  - New `page-results.php`: the template WordPress auto-selects for the Page slugged `results`. Renders nav, then `the_content()` (so Lauren can build the page with any blocks she likes, including a Shortcode block with `[cropx_customer_stories_grid]`), then the newsletter + pre-footer CTAs.
  - `taxonomy-cropx_content_type.php` rewritten to render the *same* "Customer Results" Page content for `/content-type/{term}/` views (falls back to just the grid if the Page doesn't exist yet), so a filtered view looks identical to the main page apart from the active filter pill.
  - Reusing the shortcode this way meant no new CSS/JS was needed — the existing `pub-archive.css` / `pub-archive.js` design (cards, filter pills, Show More) carried over unchanged.
- **Bug: zero page margins on `/results/`** — the grid shortcode rendered its `<section>` with no wrapping container, so it had none of the `max-width`/side-padding that the old template used to supply. Fixed by wrapping the shortcode's output in `.pa-body` / `.wrap` (in `inc/customer-stories.php`), and updated the stale CSS body-class background selector in `pub-archive.css` (`body.post-type-archive-cropx_publication` → `body.page-template-page-results-php`, since that old body class can never appear now that `has_archive` is off).
- **Bug: filtered "Case Studies" pill showed literal `[cropx_customer_stories_grid]` text** — `taxonomy-cropx_content_type.php` was rendering the Page's content with `do_blocks()`, which only processes Gutenberg blocks and does not run `do_shortcode()` (a separate filter that's normally chained in as part of the full `the_content` pipeline). Fixed by swapping in the Page's `$post`, calling `setup_postdata()` + `the_content()` (which runs the complete filter chain), then restoring the original `$post`.
- **New "Story Tags" taxonomy** (`cropx_story_tag`, `inc/cpts.php`): free-tagging taxonomy scoped to Customer Stories only, deliberately separate from WordPress's built-in Tags (so blog `/tag/...` archives never mix in customer-story tags). Registered `public => false` + `show_in_rest => true` — fully usable in the block editor and REST API, but with no public archive routes yet. Comment in the code spells out exactly what to flip (`public` → `true`, add a rewrite slug, flush permalinks, build a `taxonomy-cropx_story_tag.php` or extend the shortcode) if/when Lauren wants it public.
- **Link/button styling exceptions** (per Lauren's request — buttons should never carry CropX Blue text or an underline, and the Resources card title shouldn't be a link at all):
  - `styles/pub-single.css`: `.pub-btn-link` (the "Share this story" `<button>` next to the primary Download button on a Customer Story page) had `color: var(--cropx-blue)` — a real button rendering in link-blue. Changed to Deep Blue / Deep Blue Dark on hover, matching `.pub-btn-primary`'s family so the pair reads as primary/secondary buttons rather than button + link. (The sitewide `.cta-link` / arrow-link pattern used elsewhere — e.g. "Learn more →" — is intentionally CropX Blue per the design system's Text Link CTA spec and was left alone; this fix targeted the one real `<button>` element that had drifted into that color.)
  - `src/blocks/resource-downloads/render.php` + `style.css`: the resource name (`.rsd-title`) was wrapped in an `<a>` with no class, so it inherited the sitewide bare-link style (CropX Blue, underline) that a scoped block override then had to fight back down to plain deep-blue/no-underline. Simpler fix: removed the `<a>` entirely — the title is now plain text, not a link at all, since the cover image and download/format buttons are already the card's only clickable actions. Removed the now-dead `.rsd-title a` / `.rsd-title a:hover` / `.rsd-card--blue .rsd-title a` rules and the link-reset-exception comment block that existed only to patch around that anchor.
  - **Needs a build**: `resource-downloads/style.css` is webpack-bundled, so `npm run build` + rsync is required before this shows up in WordPress (the sandbox couldn't complete a full theme build in time — see "Pending build + rsync" note above for the exact command).
- **Zip exports**: theme zipped and delivered to Lauren multiple times this session, each time excluding `node_modules`, `.git`, and other non-essential files to keep the download small.
- **Archive-group breadcrumb fix, generalized to any registered group** (`single.php`, `inc/insights-archive.php`): blog posts in "Press Room" (or its children — Company News, Press Releases, Product Updates & Releases) or in "Ag Insights & Research" (Ag Insights / Research) were showing the generic `Resources > Blog > [title]` breadcrumb like every other post — wrong, since these posts live on their own curated hub page (`/press-room/`, `/insights/`), not the general blog. Rather than one-off it per group (started that way for Press Room, then generalized once the same request came in for Ag Insights & Research), `single.php` now resolves the breadcrumb from whatever group a post's category belongs to via `cropx_get_term_archive_group_slug()` (already existed in `inc/insights-archive.php` for `category.php`'s use) — so any future group added to `cropx_get_archive_group_registry()` gets the correct breadcrumb automatically, no template changes needed:
  - `single.php`: walks the post's own categories (same order used for the tag badges above the title) and finds the first one that resolves to a registered group. Breadcrumb is a full 3-level trail, all matching the site's normal breadcrumb convention (every level but the last is a link): `[Group heading]` (links to the group's hub page) `>` `[that specific category]` (links to the category archive via `get_category_link()`) `>` `[post title]` (current, non-linked) — e.g. `Press Room > Company News > CropX Launches Strato 1` or `Ag Insights & Research > Ag Insights > [title]`. Went through two rounds first (2-level with the category as the non-linked "current" crumb, then adding "About" as a first crumb before removing it) before landing on this — Lauren's final spec was that both non-final crumbs should be clickable, same as the rest of the site's breadcrumbs. Falls back to the original `Resources > Blog > [title]` trail for every other post.
  - New `cropx_get_archive_group_url( $group_slug )` in `inc/insights-archive.php`: extracted from `cropx_build_archive_group_pills()` (which now calls it too) so a group's hub-page URL can be reused for the breadcrumb link without duplicating the lookup logic.
  - PHP-only — rsync only, no build required.

- **Dedicated Video Testimonial single template** (new `single-cropx_publication-video-testimonial.php`, `inc/customer-stories.php`, `styles/pub-single.css`): video testimonial customer stories were using the same `single-cropx_publication.php` template as case studies, which caused duplicate share links (a right-hand sticky "Share" sidebar plus the "Share this story" row below the article) and exposed case-study-only fields (Download URL, Key Findings, and the "At a Glance" Scale/Challenge/Solution Deployed fields) that don't apply to a video. Since WordPress's template hierarchy has no native "single-{post_type}-{term}.php" pattern, fixed with:
  - New `single-cropx_publication-video-testimonial.php`: a trimmed copy of the case study template — same breadcrumb, header, hero image, article content, bottom share row, related-stories section, and pre-footer CTA, but with the right-hand share sidebar, Key Findings card, Download CTA, and case-study "At a Glance" fields (cs_scale/cs_challenge/cs_solution, plus cs_company/cs_region) removed entirely rather than just hidden.
  - New `template_include` filter in `inc/customer-stories.php`: swaps in the video-testimonial template whenever `is_singular('cropx_publication')` and the post has the `video-testimonial` term; everything else (case studies, untagged customer stories) keeps using the default template. No changes needed to `inc/enqueue.php` — its `pub-single.css`/`blog-single.js` enqueue condition is keyed on `is_singular('cropx_publication')` (post type), not the template file, so both templates share the same assets automatically.
  - `styles/pub-single.css`: new `.pub-body-layout--video` modifier (`grid-template-columns: 1fr`) so the article column fills the space where the sidebar used to be instead of leaving an empty reserved column.
  - Also corrected a stale doc-comment in `single-cropx_publication.php` that had the article/sidebar columns backwards (said sidebar was on the left).
  - PHP + CSS only — rsync only, no build required.

### Done since last update ✅ (July 25, 2026)

- **`cropx_publication` — custom permalink structure** (`inc/cpts.php`, `archive-cropx_publication.php` comment): Single publication URLs changed from the flat `/results/{post-name}/` to `/results/{content-type-slug}/{post-name}/` (e.g. `/results/case-study/my-annual-report/`, `/results/white-paper/soil-health-guide/`). Same conceptual approach as WordPress core's built-in `%category%/%postname%` structure for blog posts, but implemented by hand since core only auto-resolves that placeholder pattern for the built-in `post` type — a CPT paired with a custom taxonomy needs manual wiring:
  - **`register_post_type('cropx_publication', ...)`**: `has_archive` changed from `true` to the literal string `'results'` (previously it inherited the `rewrite.slug` value, which is no longer a usable literal). `rewrite.slug` changed from `'results'` to `'results/%cropx_content_type%'` — a placeholder token embedded in the slug string, resolved per-post at permalink-generation time.
  - **`cropx_publication_permalink()`** (new, hooked to `post_type_link`): Replaces the `%cropx_content_type%` placeholder in the generated permalink with the publication's actual `cropx_content_type` term slug. Falls back to the literal slug `publication` if a post has no term yet (e.g. a brand-new draft) — this fallback is also a valid rewrite pattern so the link never 404s.
  - **`cropx_publication_rewrite_rules()`** (new, hooked to `init` at priority 20): Adds an explicit rewrite rule so incoming requests to `/results/case-study/my-post/` route back to the right `cropx_publication` post. Pulls the live list of `cropx_content_type` term slugs via `get_terms()` (rather than hardcoding `case-study`/`white-paper`) so a future third content type is picked up automatically the next time permalinks are flushed — no code change needed, just a Settings → Permalinks → Save Changes.
  - **Design rationale** (discussed with Lauren before implementing): nesting single-post URLs under `/results/` — rather than a flatter `/{content-type}/{post-name}/` — matches the site's actual information architecture (Results & Research archive → content type → post) and avoids permanently reserving content-type slugs like `case-study` at the site root, where they could someday collide with an unrelated Page slug.
  - **Untouched**: the CPT archive (`/results/`) and the `cropx_content_type` taxonomy archive (`/content-type/{term}/`) — only single publication permalinks changed.
  - **PHP-only — rsync only, no build required.** Requires a permalink flush after deploying (Settings → Permalinks → Save Changes, no need to touch the structure dropdown) — old `/results/{post-name}/` URLs will 404 once the new rule is active, since no redirect was set up for the old pattern.

- **Blog post permalinks nested under `/blog/`** (no code — Settings → Permalinks in wp-admin): Structure changed from `/%category%/%postname%/` to `/blog/%category%/%postname%/`, for the same reasoning as the publications change above — matches the Blog archive's actual URL (`/blog/`) and stops category slugs from being reserved at the site root. This uses WordPress's native custom-structure field, so no theme code was involved. Mentioned here for the historical record since it was decided in the same conversation as the publications change.

### Done since last update ✅ (July 24, 2026)

- **Cookie consent banner — body padding fix** (`assets/js/cookie-consent.js`): The `position: fixed` banner and preferences panel were overlapping the footer on short viewports and on mobile. Fixed by measuring whichever panel is currently visible and applying its height as `padding-bottom` on `<body>` — this pushes the footer content above the banner. A `ResizeObserver` on both elements keeps the padding accurate as the viewport resizes (e.g. banner text wrapping on narrow screens). `requestAnimationFrame` debounce prevents layout thrash. When both panels are hidden (user dismissed), padding is removed. `show()` calls `updateBodyPad()` after adding the visible class; `hide()`'s `transitionend` listener calls it after the `hidden` attribute is set. JS-only change, no build needed — rsync only.

- **Testimonials carousel — attribution text color** (`src/blocks/testimonials-carousel/style.css`): `.testimonial-author .author-title` color changed from `var(--gray-500)` to `var(--deep-blue)`. The `--gray-500` value was too light for legibility against the white card background. **Requires `npm run build` + rsync.**

- **Testimonial single — heading color on blue variant** (`src/blocks/testimonial-single/style.css`): `.ts-section--blue .ts-title` color changed from `rgba(255, 255, 255, 0.7)` to `var(--white)`. The 70% opacity made the section heading read as muted on the deep-blue background — should be full white to match the rest of the reversed text. **Requires `npm run build` + rsync.**

- **Resource grid — sitewide link style override** (`src/blocks/resource-downloads/style.css`): The sitewide `a:not([class])` rule in `shared.css` (specificity 0,1,1) was overriding the block's own `.rsd-title a` rule (also 0,1,1 — load order breaks the tie in shared.css's favor), turning resource card title links CropX Blue with an underline. Fixed by adding a scoped override at the top of the block stylesheet: `.wp-block-cropx-resource-downloads a:not([class])` (specificity 0,2,1) resets `color: inherit`, `text-decoration: none`, `font-weight: inherit`, `text-decoration-color: unset`. **Requires `npm run build` + rsync.**

- **Resource grid — pdf.js client-side PDF thumbnails** (4 files):
  WordPress cannot auto-generate PDF thumbnails on the staging EC2 server because the ImageMagick `policy.xml` blocks PDF→image conversion at the OS level. `queryFormats("PDF")` returns true, but actual conversion is blocked. This is interim — after site launch, SSH access will be available to fix the policy. Until then, `view.js` renders thumbnails client-side.

  - **`src/blocks/resource-downloads/view.js`** (new file): IIFE following the `testimonials-carousel/view.js` pattern. Queries all `.rsd-cover-placeholder[data-pdf-url]` elements on the page — exits immediately if none are found (zero overhead when all cards have server-generated thumbnails). Loads pdf.js 3.11.174 from cdnjs as a singleton (one `<script>` tag regardless of how many cards are on the page; reuses `window.pdfjsLib` if already present from a prior block instance). Uses `IntersectionObserver` with `rootMargin: '300px 0px'` to trigger rendering 300px before each placeholder enters the viewport. For each card: `pdfjsLib.getDocument()` with `disableAutoFetch: true` (HTTP range requests — only the first page's data is fetched), `getPage(1)`, render at 400px width, replace the placeholder `<div>` with the `<canvas>` via `replaceWith()`. Fails silently — placeholder icon stays if CDN is unreachable or PDF is corrupt. Post-launch: once ImageMagick policy is updated on the server, WordPress auto-generates thumbnails at upload time, no placeholder carries `data-pdf-url`, and this script exits in under a millisecond.
  - **`src/blocks/resource-downloads/block.json`**: Added `"viewScript": "file:./view.js"` so WordPress enqueues the IIFE on front-end page loads.
  - **`src/blocks/resource-downloads/render.php`**: Added `$pdf_thumb_url = $url_letter ?: ( $url_a4 ?: $download_url )` (never uses `$permalink` — avoids pdf.js trying to load a WP page as a PDF). Added `data-pdf-url="..."` attribute to the `.rsd-cover-placeholder` div, but only when `$pdf_thumb_url` is non-empty.
  - **`src/blocks/resource-downloads/style.css`**: Added `canvas.rsd-cover--canvas { height: auto; }` — overrides the `height: 100%` from `.rsd-cover` that would stretch the canvas vertically. Width stays `100%`; the cover-wrap's `overflow: hidden` clips non-3:4 PDFs. Canvas also inherits `.rsd-cover`'s hover zoom transform via `.rsd-cover-link:hover .rsd-cover`.
  - **All four files require `npm run build` + rsync.**

### Done since last update ✅ (July 23, 2026)

- **`cropx/icon-columns` — complete rewrite** (column-major ordering, independent flex columns). All four core source files changed; `index.js` and `editor.css` were already correct and left untouched. **All four files require build.**

  - **`block.json`**: Title updated to `"Icon Columns (Column-Major)"`, description updated to document the column-major distribution behavior. No attribute schema changes — all existing attributes preserved.

  - **`edit.js`** — complete rewrite:
    - Added `getColRow(idx, total, numCols)` helper: mirrors PHP `array_chunk()` exactly. Computes which display column and row each flat item index lands in using `chunkSize = Math.ceil(total / numCols)`.
    - Added `editorCols` computation: groups flat `columns` array into column-major sub-arrays (each paired with `globalIdx` for RichText `onChange` callbacks). Mirrors PHP chunk logic exactly so canvas matches front-end.
    - Canvas rewritten: `ici-columns` → N `ici-col` divs (one per computed column), each containing its column's items in column-major order. RichText `onChange` maps back to flat attribute via `globalIdx`.
    - Sidebar per-item panels labeled `Col N, Item N` using `getColRow()`. Badge shows `C1 R1`, `C1 R2`, `C2 R1` etc.
    - Max items raised from 12 → 18. Functions renamed `updateItem`/`removeItem`/`addItem`/`dropItem`.

  - **`render.php`** — complete rewrite:
    - Column-major distribution via `array_chunk($all_items, ceil(count($all_items) / $num_cols))`. Produces N independent column groups (fewer if not enough items — graceful degradation).
    - Empty item filter applied before chunking: items with both empty heading and empty body are stripped.
    - HTML structure: `.ici-columns.ici-cols-{N}` → N `.ici-col` divs → `.ici-item` divs (icon → h3 → p → optional CTA link).
    - Icon allowlist sanitization retained. `$allowed_body` renamed → `$allowed_body_tags` to avoid variable collision.

  - **`style.css`** — complete rewrite:
    - `.ici-columns`: `display: flex; flex-direction: row; align-items: flex-start` — the `flex-start` is the key property that makes column heights independent.
    - `.ici-col`: `flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2.5rem` — each column is a self-contained independent stack.
    - Gap overrides per column count: 3→3rem, 4→2.5rem, 5→2rem, 6→1.75rem.
    - Deep Blue variant: same `drift-pattern.svg` + 60s `@keyframes ici-drift` animation as other deep-blue blocks.
    - Responsive: 3-col stacks at 900px; 4-col wraps 2×2 at 900px → stacks at 600px; 5/6-col wraps 3-col at 1100px → 2-col at 700px → stacks at 480px.

  **Implementation note — column-major rationale:** CSS Grid `repeat(N, 1fr)` with a flat `foreach` gives row-major ordering and coupled row heights. The new approach uses PHP `array_chunk()` + independent flex column containers, giving true column-major ordering with no row-height coupling. A tall item in column 1 cannot affect items in column 2.

### Done since last update ✅ (July 22, 2026 — session 2)

- **`stats-grid` — Deep Blue background option** (4 files):
  - `block.json`: Added `"deep-blue"` to `bgColor` enum → `["taupe", "white", "deep-blue"]`.
  - `render.php`: Validation array updated to include `'deep-blue'`.
  - `edit.js`: Added `{ label: __('Deep Blue','cropx'), value: 'deep-blue' }` to the Background `SelectControl`. **Requires build.**
  - `style.css`: Full text color override set under `.sg-section--bg-deep-blue`: eyebrow `rgba(255,255,255,0.7) !important` (the `!important` is needed because eyebrow color is set via inline PHP `style=""` which wins on specificity), heading/body/CTA in white/white-alpha tones, stat number/descriptor in white/white-alpha. Stat button flipped to white background + deep-blue text. **Rsync only.**

- **Secondary CTA restyled — text + arrow, no outline or fill** (14 files across 4 blocks). The secondary CTA across all hero and CTA blocks is now a bare inline-flex link: white/blue label text with an 18×18 right-pointing arrow SVG, gap-on-hover animation, no border or background.

  - **`hero` block** (`hero/block.json`, `hero/edit.js`, `hero/render.php`, `hero/style.css`):
    - Three new attributes added to `block.json`: `secondaryLabel` (string, default `""`), `secondaryUrl` (string, default `"#"`), `showSecondaryCta` (boolean, default `false`).
    - `render.php`: Primary CTA wrapped in new `.hero-actions` div; `.hero-sec-cta` secondary added conditionally (SVG inline in PHP). **Rsync only.**
    - `edit.js`: Added `ToggleControl` ("Show secondary CTA") + conditional `TextControl` pair in the CTA `PanelBody`; preview wraps both CTAs in `.hero-actions`. **Requires build.**
    - `style.css`: Moved `align-self: flex-start` from `.hero-cta` to new `.hero-actions` flex wrapper. Added `.hero-sec-cta` (text-only, white, `--fs-btn-hero`, gap + opacity hover, translateX arrow). **Rsync only.**

  - **`segment-hero` block** (same 4 files, same pattern):
    - Same three attributes added.
    - `render.php`: Primary CTA wrapped in `.sgh-actions`; `.sgh-sec-cta` secondary added. **Rsync only.**
    - `edit.js`: Same ToggleControl + TextControls; preview uses `.sgh-actions` wrapper. **Requires build.**
    - `style.css`: Fade-up animation moved from `.sgh-cta` to `.sgh-actions` (so both CTAs animate in together). Added `.sgh-sec-cta` and hover/svg rules. `prefers-reduced-motion` guard updated to include `.sgh-actions`. **Rsync only.**

  - **`mid-page-cta` block** (`mid-page-cta/edit.js`, `mid-page-cta/render.php`, `mid-page-cta/style.css`):
    - `render.php`: `$secondary_class` is now always `'mcta-btn-secondary'` — the old ghost/link `secondaryStyle` attribute is still read for backward compat but no longer affects which class is applied. Arrow SVG updated to 18×18. **Rsync only.**
    - `edit.js`: `secondaryClass` variable and `SelectControl` for secondary style removed. Preview uses `className="mcta-btn-secondary"` with `ARROW_SVG` always shown. **Requires build.**
    - `style.css`: Replaced `.mcta-btn--ghost` and `.mcta-btn-link` with unified `.mcta-btn-secondary` (inline-flex, no border, `var(--deep-blue)` color). Dark bg override: `color: rgba(255,255,255,0.9)`. Gap + opacity hover with translateX arrow. **Rsync only.**

  - **`pre-footer-cta` block** (`pre-footer-cta/edit.js`, `pre-footer-cta/render.php`, `pre-footer-cta/style.css`):
    - `render.php`: 18×18 arrow SVG added inside `.btn-ghost` link. **Rsync only.**
    - `edit.js`: Arrow SVG added to secondary preview span. **Requires build.**
    - `style.css`: `.btn-ghost` stripped of all border/padding styles and rewritten as text+arrow: `display: inline-flex; align-items: center; gap: 0.5rem; background: none; border: none; color: var(--white); padding: 0`. Gap + opacity hover with translateX arrow. **Rsync only.**

  **Implementation note — Tab indentation:** These JS files use actual tab characters (`\t`). If future edits to these files fail with the Edit tool's string matcher, use a Python bash script with explicit `\t` escape sequences in `content.replace()` calls.

### Done since last update ✅ (July 22, 2026 — session 1)

- **Separator options trimmed to 2** — removed "1px line" and "Shadow + line"; only "Drop shadow (default)" and "None" remain. Three-file change:
  - `src/admin/editor-panels.js`: `SelectControl` options array reduced to `[{shadow}, {none}]`. **Requires build.**
  - `inc/block-shadow.php`: `enum` updated to `['shadow', 'none']`; `render_block` filter remaps any stored `'line'` or `'both'` values to `'shadow'`. **Rsync only.**
  - `styles/tokens.css`: CSS rules for `data-separator="line"` and `data-separator="both"` removed. **Rsync only.**

- **`feature-stat` — white background fix** (`feature-stat/style.css`): Base `.fstat-section` rule changed from `background: transparent` → `background: var(--white)`. The deep-blue variant (`.fstat-section--deep-blue`) was already correct. **Rsync only.**

- **`hero-curved-standard` — max-width + text-wrap** (`hero-curved-standard/style.css`): Heading max-width set to `22ch` (wide: `26ch` when both overlay images hidden), subheading max-width set to `68ch` (wide: `76ch`). `text-wrap: balance` added to `.shc-headline`. **Rsync only.**

- **`hero-curved` — text-wrap** (`hero-curved/style.css`): `text-wrap: balance` added to `.hc-headline`. **Rsync only.**

- **`mid-page-cta` — subheading max-width + text-wrap** (`mid-page-cta/style.css`): `.cropx-mid-cta .section-body` max-width changed `44ch → 52ch`; `text-wrap: balance` added. **Rsync only.**

- **`pre-footer-cta` — subheading max-width + text-wrap** (`pre-footer-cta/style.css`): `.pf .pf-sub` max-width changed `44ch → 52ch`; `text-wrap: balance` added. **Rsync only.**

- **`split-column-icons` — remove sticky + no item cap** (two files):
  - `style.css`: `position: sticky; top: 6rem;` removed from `.spi-header`. **Rsync only.**
  - `edit.js`: `if (columns.length >= 6) return;` guard removed from `addColumn()`; `disabled` prop and label ternary removed from the Add button. **Requires build.**

- **Animated topography pattern — 14 blocks** — `::before` drift pattern added to every deep-blue background variant across the theme. All changes are CSS-only — **rsync only, no build required** for any of these.
  - *Class already existed, pattern added:* `three-column-icons` (`.tci-section--blue`), `hardware-lineup` (`.hwf-section--bg-deep-blue`), `two-column-alternating` (`.tca-section--bg-deep-blue`), `two-column` (`.tcv-section--bg-deep-blue`), `two-column-overlay` (`.tco-section--bg-deep-blue`), `feature-stat` (`.fstat-section--deep-blue` — position + overflow added to the rule, pattern appended).
  - *`pre-footer-cta`*: pattern at `z-index: 2` (above photo overlay at z:1, below content at z:3); `.pf-inner` bumped from z:2 → z:3. `.pf` already had position/overflow.
  - *`dealer-finder`*: `position: relative; overflow: hidden` added to `.df-block.df-scheme-dark`; `.df-layout` set to `z-index: 1`.
  - *Deep-blue CSS class was missing entirely — full rule added:* `logo-strip` (`.logo-strip--bg-deep-blue`), `faq-accordion` (`.faq-section--bg-deep-blue`), `stats-grid` (`.sg-section--bg-deep-blue`), `testimonials-carousel` (`.testimonials-section--deep-blue`), `product-grid` (`.pg-block--bg-deep-blue`), `two-column-video` (`.tcvid-section--bg-deep-blue`).
  - Pattern spec: `url('../../../assets/decorative/drift-pattern.svg')`, `background-size: 480px 480px`, `filter: invert(1) opacity(0.07)`, 60s linear infinite animation, `prefers-reduced-motion` suppression, each keyframe uniquely namespaced (prefix = block CSS namespace).

### Done since last update ✅ (July 21, 2026 — session 3)

- **`people-showcase` — allow same person added twice** (`people-showcase/edit.js`):
  - Removed the `if ( selectedPostIds.includes( postId ) ) return;` dedup guard in `addMemberById`.
  - Removed the `alreadyAdded` flag and all its side effects (disabled state, "Added" badge, grayed-out CSS class) from the search result buttons. Each search result is now always clickable regardless of how many times that person already appears in the list.
  - Each added member has its own auto-increment `id: Date.now()` key, so duplicate postIds are tracked independently in the sidebar and render independently on the front end.
  - **Requires `npm run build` + rsync** (edit.js changed).

- **`people-showcase` — LinkedIn icon repositioned** (`people-showcase/render.php`, `people-showcase/style.css`):
  - `render.php`: Moved the LinkedIn `<a>` anchor out of `.team-name-row` (where it was to the right of the name) to after `<p class="team-role">` so it appears below the person's job title. `.team-name-row` wrapper div removed (now just a plain `<p class="team-name">`).
  - `style.css`: Removed the `.team-name-row` flex layout rule (display: flex, justify-content: space-between, gap, margin-bottom). Added `margin-top: 0.6rem` to `.team-linkedin-icon` for breathing room below the role.
  - **Rsync only** (no JS changes).

- **`mid-page-cta` — remove hardcoded grey borders** (`mid-page-cta/style.css`):
  - Removed `border-top: 1px solid var(--gray-200)` and `border-bottom: 1px solid var(--gray-200)` from both `.mcta--taupe` and `.mcta--white` variants. These were always-on regardless of any block setting, with no attribute or control to disable them.
  - **Rsync only** (no JS changes).

### Done since last update ✅ (July 21, 2026 — session 2)

- **`two-column-alternating` — content width toggle** (4 files):
  - `block.json`: Added `contentWidth` attribute — `"string"`, default `"narrow"`, enum `["narrow", "wide"]`.
  - `edit.js`: Destructured `contentWidth = 'narrow'`; added `ToggleControl` ("Wide content width") in Section Settings between Background and Show intro toggle; canvas `.tca-inner` div gets `tca-inner--wide` class when toggled on.
  - `render.php`: Reads `$content_width = $attributes['contentWidth'] ?? 'narrow'`; applies `tca-inner--wide` modifier to `$inner_class`.
  - `style.css`: Added `.tca-inner--wide { max-width: var(--max-w); }` after the base `.tca-inner` rule. CSS-only change for this file (rsync only), but the edit.js change requires a build.
  - Default (narrow): `65rem / 1040px`. Wide: `72rem / 1152px` (matches the standard `--max-w` used by most other blocks).

- **`people-showcase` — renamed + columns selector** (4 files):
  - `block.json`: Title changed from `"4-Column People Showcase"` → `"People Showcase"`. Added `columns` attribute — `"string"`, default `"4"`, enum `["4", "5", "6"]`.
  - `edit.js`: Destructured `columns = '4'`; added `SelectControl` ("Columns" — 4/5/6) at the bottom of Section Settings panel; canvas `.people-grid` div gets `people-grid--cols-N` modifier class when N ≠ 4.
  - `render.php`: Reads `$columns = $attributes['columns'] ?? '4'`; applies `people-grid--cols-N` modifier class to the grid div.
  - `style.css`:
    - Base 5/6-col grid rules: `.people-grid--cols-5 { grid-template-columns: repeat(5, 1fr) }`, `.people-grid--cols-6 { repeat(6, 1fr) }`.
    - Responsive breakpoints: 6-col → 4-col at 1350px; 5-col → 4-col at 1200px; both → 3-col at 1100px; both → 2-col at 768px; both → 1-col at 480px.
    - **Narrow-card radius**: When column count makes cards < ~240px wide, the 40px outer corners reduce to 26px. Formula: `threshold viewport = 268N + 36`. 6-col cards are always < 240px (threshold is 1644px but layout drops to 4-col at 1350px) → unconditional rule on `.people-grid--cols-6 .team-card`. 5-col cards are < 240px from 1201px–1375px viewport → `@media (min-width: 1201px) and (max-width: 1375px)`. 4-col window is only 8px (1100px–1108px) — not worth a rule.
  - **Requires `npm run build` + rsync** (edit.js changed).

### Done since last update ✅ (July 21, 2026 — session 1)

- **feature-stat breakpoint consolidation**: The block previously used two separate media queries (`900px` for layout collapse and `600px` for padding-only). Merged into a single `@media (max-width: 768px)` block to match the rest of the two-column family. CSS-only change (`feature-stat/style.css`), no build required — rsync only.

- **All two-column-family blocks standardized to 768px**: Verified that `two-column`, `two-column-overlay`, `two-column-video`, `two-column-alternating`, and `feature-stat` all use `768px` as their responsive breakpoint. `two-column-alternating` retains an additional `600px` padding-only rule — intentionally left alone.

- **feature-stat mobile stat card layout** (`feature-stat/style.css`): At `≤768px`, the stat card is now always anchored bottom-right regardless of which side the photo is on at desktop. Specific rules:
  - `.fstat-photo-wrap`: `left: 0 !important; right: 2.5rem !important` — photo insets 40px from the right so the stat card can bleed 40px past the photo's right edge
  - `.fstat-card`: `right: 0 !important; left: auto !important; bottom: 2.5rem; max-width: 75%; border-radius: 0 32px 0 0 !important; border-left: 6px solid var(--accent) !important; border-right: none !important` — card bleeds past the photo's right edge, upper-right corner rounded only, accent border on left edge always, photo bottom extends 40px below card bottom

- **resource-downloads deep-blue background card width fix** (`resource-downloads/style.css`): Cards were narrower on the deep-blue variant because `overflow: hidden` on `.rsd-section--bg-deep-blue` was clipping the grid (cards are wider than the section at desktop because of the grid gap math). Fixed by removing `overflow: hidden` from the section and adding `z-index: -1` to the `.rsd-section--bg-deep-blue::before` topographic pattern pseudo-element instead — pattern still stays behind content without needing a clipping context on the section wrapper.

- **resource-downloads responsive breakpoints** (`resource-downloads/style.css`): Cards should never be narrower than 280px. Two-breakpoint system:
  - `952px`: 4-col and 3-col collapse to 2-col. Centered-grid variants re-locked to `flex: 0 0 calc(50% - 0.75rem)`.
  - `648px`: all layouts (4-col, 3-col, 2-col) collapse to 1-col. Centered variants go `flex: 0 0 100%`. **Specificity gotcha fixed**: the 952px centered rule has (0,3,0) specificity which beat the 648px centered override at (0,2,0). Fixed by prefixing the 648px centered rules with `.rsd-inner` to reach (0,3,0). A build step is required (not rsync-only) because webpack compiles the CSS.

- **resource-downloads cover image — no-crop fix** (`resource-downloads/style.css`):
  - Changed `object-fit: cover` → `object-fit: contain` on `.rsd-cover` so the full document thumbnail is always visible without cropping or distortion. `object-position` changed from `top center` to `center`.
  - Removed the `aspect-ratio: 3/2` landscape override from the `648px` media query — the base `aspect-ratio: 3/4` (portrait, matches document proportions) now applies at all breakpoints.
  - The `background-color: var(--gray-100)` on `.rsd-cover-wrap` fills any letterbox space around images that don't match 3:4 exactly.

- **resource-downloads cover letterbox trim** (`resource-downloads/style.css`): With `object-fit: contain`, a grey strip was visible at both the top and bottom of the cover wrap (the `--gray-100` background showing through the letterbox). Fixed by:
  - Adding `overflow: hidden` to `.rsd-cover-link` (so it clips its child's negative margins)
  - Adding `margin-top: -12px; margin-bottom: -12px` to `.rsd-cover-wrap` — pulls the card body up and clips the top edge, hiding the grey strips. Crops 12px of image at top and bottom — user-approved trade-off.

- **two-column-alternating bullet list font-size fix** (`two-column-alternating/style.css`): Changed `.tca-body ul li, .tca-body ol li { font-size }` from `1.0625rem` → `1rem` to match the block's body paragraph text size. The `::before` pseudo-element (bullet dot) was not changed. CSS-only — rsync only, no build required.

### Done since last update ✅ (July 20, 2026)

- **Bullet color fix — `two-column-alternating`** (`two-column-alternating/style.css`): Changed `.tca-body ul li::before { background }` from `var(--cropx-blue)` to `var(--deep-blue)`. The deep-blue-section override (`.tca-section--bg-deep-blue .tca-body ul li::before`) keeps `var(--cropx-blue)` for contrast — that was intentional and correct.

- **Double-bullet fix + bullet style unification** (`styles/shared.css`):
  - **Root cause of double bullets**: `content.css` (loaded after `shared.css` by `enqueue_block_assets`) contains `ul.wp-block-list { list-style-type: disc }` at specificity 0,1,1. `shared.css` had `list-style: none` at the same specificity — so `content.css` won by load order, re-enabling the native disc bullet. With both the native `::marker` disc AND the custom `::before` circle rendering, blocks showed `•• item` double bullets.
  - **Fix**: Added `.section-body ul.wp-block-list { list-style: none; padding-left: 1.375rem; }` and `.section-body ul.wp-block-list li::marker { content: none; }` to `shared.css` — specificity 0,2,1 beats `content.css`'s 0,1,1 unconditionally.
  - **Bullet style unification**: Also updated `.section-body ul li::before` in `shared.css` from the old unicode `\2022` bullet (with `color` property) to match `two-column-alternating`'s circle approach: `content: ''`, `width: 6px`, `height: 6px`, `border-radius: 50%`, `background: var(--list-marker-color)`. This is a CSS-only change — just rsync, no build needed.

- **InnerBlocks body support added to 3 blocks** — `feature-stat`, `two-column-overlay`, `two-column-video`:
  - **Pattern**: For each block, replaced the `<RichText tagName="div" className="section-body">` body with `<div className="section-body"><InnerBlocks allowedBlocks={['core/paragraph','core/list','core/heading']} template={[['core/paragraph',{placeholder:'Body text…'}]]} templateLock={false}/></div>` in `edit.js`. Updated `render.php` to check `$content` (serialized inner blocks) first, falling back to the legacy `$body` attribute — this preserves content on any existing block instances.
  - **Files changed**: `feature-stat/edit.js`, `feature-stat/render.php`, `two-column-overlay/edit.js`, `two-column-overlay/render.php`, `two-column-video/edit.js`, `two-column-video/render.php`. All six require `npm run build` + rsync.
  - **Migration note**: Re-insert any existing block instances of these three blocks after the build — WordPress doesn't auto-migrate stored body text to InnerBlocks format. Existing instances fall back to the legacy `$body` attribute gracefully.

- **Breakpoint audit** — `two-column`, `two-column-overlay`, and `two-column-video` were already at `768px`. Standardized `feature-stat` to match: merged its two-query `900px` (layout) + `600px` (padding) approach into a single `768px` block. All five two-column-family blocks are now at `768px`. See July 21 entry for the feature-stat mobile card layout work that followed.

### Done since last update ✅ (July 17, 2026 — session 2)

- **`cropx/two-column-alternating` — inline bullet list + paragraph support** (4 files modified):
  - `block.json`: Added `"bulletList": ""` string attribute to each default row object (replaces the old `bulletItems: []` array approach that was explored and abandoned). `addRow()` also initializes `bulletList: ''`.
  - `edit.js`: Added `<RichText tagName="ul" multiline="li" className="tca-items" ...>` inline in the canvas immediately after the row body `<div>` RichText. `allowedFormats={['core/bold', 'core/italic']}` — no link in bullet items. Sidebar bulletItems controls removed entirely. Body RichText already used `tagName="div"` + `wp_kses_post()` so paragraph recognition was already handled.
  - `render.php`: Added `$bullet_list = trim( wp_kses( $row['bulletList'] ?? '', ['li'=>[], 'strong'=>[], 'em'=>[], 'br'=>[]] ) )` and conditional `<ul class="tca-items">` output after `$body`.
  - `style.css`: Added `.tca-items` styles (custom bullets with `var(--cropx-blue)` dots, 6×6px, `padding-left: 1.25rem`, `position: relative` on `li`, plus deep-blue-background overrides).
  - **Architectural note**: The block stores rows as a JSON array attribute — InnerBlocks cannot be used per-row (that's how the `two-column` block achieves list support). The `RichText tagName="ul" multiline="li"` approach stores the full `<li>…</li><li>…</li>` HTML string as a single attribute, wrapped in `<ul>` at render time. Enter key adds new `<li>` items inline in the canvas — exactly the contextual inline-toolbar experience the user wanted.
  - **⚠️ Build + rsync needed** — source edits complete but not yet compiled.
  - **⚠️ User reported "weird issues"** on first test — needs investigation next session. Possible causes: old block instances stored with `bulletItems` schema (deleted attribute name conflict), editor validation warnings, or CSS specificity issue in the editor preview. Re-insert the block fresh to rule out stale stored attributes.

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

16. **A "scoped single-block build" (overriding webpack's `entry` to just one block, to build faster) silently wipes every OTHER block's compiled JS/CSS from `build/`, unless you explicitly strip `CleanWebpackPlugin` out of the plugins array.** `@wordpress/scripts`'s base webpack config (`require('@wordpress/scripts/config/webpack.config')`) instantiates `CleanWebpackPlugin` directly in its `plugins` array — this is a *separate* mechanism from webpack 5's native `output.clean` option, and setting `output: { clean: false }` in an override config does **nothing** to stop it; the plugin instance still wipes the entire `output.path` on every run regardless. This went undetected for a while because `block.json` and `render.php` still get copied fresh into every block's `build/` folder every time (via `CopyPlugin`, which globs across all of `src/blocks/*` regardless of `entry`) — so a wiped block still *looks* present in `build/`, it's just missing `style-index.css` and `index.js`, meaning that block silently loses ALL its front-end + editor styling and JS behavior until it's rebuilt again. Discovered when Lauren reported a CSS fix (two-column-alternating's deep-blue body text color) "still broken" on staging after she'd genuinely rebuilt and re-uploaded — the fix was correct in source the whole time, but an earlier scoped single-block build (done to work around a 45-second command-execution limit) had wiped that block's compiled CSS from the shared `build/` folder, and a later zip was made from that half-wiped state before Lauren's own full rebuild overwrote it back to correct — timing made it look like the source fix wasn't real. **If you ever need to rebuild just one or a few blocks** (not a full `npm run build`), filter the plugin out explicitly:
    ```js
    const base = require('@wordpress/scripts/config/webpack.config');
    const plugins = base.plugins.filter((p) => p.constructor.name !== 'CleanWebpackPlugin');
    module.exports = { ...base, entry: { /* just the blocks you're touching */ }, plugins };
    ```
    Do a real `rm -rf build && npm run build` (full, un-scoped) periodically regardless — Lauren's own local builds are always full builds and are unaffected by this, but any zip built from this sandboxed repo copy using a scoped-entry technique must use the filtered-plugins config above, or verify with `for b in $(ls src/blocks); do test -f "build/blocks/$b/index.js" || echo "MISSING: $b"; done` before zipping.

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
