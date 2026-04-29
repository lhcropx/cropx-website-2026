# Project Progress & Handoff

This file captures the full state of the CropX website rebuild as of **April 28, 2026** — all decisions made, blocks built, work in progress, and what's still ahead. Use this as a context primer for any future Claude session so we never lose progress.

---

## Quick Status

**Phase:** Block library in HTML/CSS (Phase 2 of 6)
**Approach:** Build standalone HTML/CSS blocks first, port into WordPress later
**Stack:** WordPress (Local) + GitHub + Claude Code
**Repo:** https://github.com/lhcropx/cropx-website-2026

### Done ✅
- Design token system (`tokens/tokens.css` + visual reference page)
- 8 core block files committed to `blocks/`
- Claude Code installed and authenticated locally
- `CLAUDE.md` project briefing

### In Progress 🔨
- `segment-hero.html` — segment landing page hero with nav badge + product PNG overlays
  - Last action: fixing phone PNG to overlap next section by ~30px (was overlapping too much)
  - File NOT yet committed — was living in Downloads/temp

### Up Next ⬜
- Finish segment-hero phone PNG fix → commit
- 4 more HTML blocks (hardware carousel, testimonials carousel, stats grid, FAQ accordion)
- 3 blocks built directly in WordPress (alternating two-column, two-column text+photo, two-column with PNG overlay)
- WordPress theme build (Phase 3)

---

## Block Library Status

### Committed blocks (8)

| Block | File | Notes |
|---|---|---|
| Hero | `blocks/hero.html` | Deep blue nav, photo bg with dark gradient overlay, accent-edge CTA, animated pattern. Hero photo: farmer with phone in flowering field. |
| Segments | `blocks/segments.html` | Three segments (Enterprise/Gold, Service Provider/Terra, In-Field/Green). Center-aligned, no dividers, mobile tab navigation. SVG arrow on hover, underline matches text width. |
| Logo strip | `blocks/logo-strip.html` | White bg, full color logos, marquee, edge fades, Deep Blue eyebrow. |
| Feature + stat card | `blocks/feature-stat.html` | 4 variants: Variant A/B (photo right/left) × white/Deep Blue backgrounds. Photo inset so stat card never crosses into text column. White stat card on Deep Blue sections. Lining figures, full-height special characters. |
| Cards | `blocks/cards.html` | White and Deep Blue card variants. Photo, title, "Read [x]" CTA all clickable. Tags wired to placeholder URLs (`/resources/case-studies` etc). |
| Pre-footer CTA | `blocks/pre-footer-cta.html` | Photographic background, all-combined overlay treatment (radial gradient + lighter opacity + full white subtext + text shadows). 25px accent stripe along top edge using `--accent`. |
| Nav (standard) | `blocks/nav.html` | White bar, full-color logo, click-to-open mega menu (Platform) + simple dropdown (Solutions). Underline pinned to bottom edge. Login button right-aligned. |
| Footer | `blocks/footer.html` | Variant C — 4 equal columns (brand left-aligned + 3 nav groups). Fully responsive: 4-col → 2-col at 900px → 1-col at 540px. |
| Nav (segment) | `blocks/nav-segment.html` | Three segment variants with colored badge between logo and links. Badge specs locked in below. |

### Segment nav badge — locked specs
- Padding: `1.25em` left/right, `0.75rem` bottom (matches L/R)
- `min-width` removed — sizes to content
- White text, weight 600, font-size `0.9375rem`
- Letter-spacing: `0.08em`
- Text-shadow: `0 1px 2px rgba(0,0,0,0.2)` (subtle)
- Bottom-aligned within badge with `0.75rem` margin beneath text
- Badge hangs below nav (~1.5rem overhang) using negative bottom margin + `overflow: visible` on nav and inner container
- Nav stays white on scroll (no blue transition)

---

## Design Decisions Locked In

### Color rules
- **NO accent-color text:** Never use New Leaf green, Muted Gold, or Terra cotta as text color anywhere on the site. These colors are for borders, underlines, icon box backgrounds, and accent stripes ONLY. Approved text colors: Deep Blue, white, gray scale, CropX Blue.
- Documented as a comment in `tokens.css` under `:root`.

### Greyscale
- Warm grays (Option A from token review). `--gray-700: #3C3A36`. Not the cooler Tailwind defaults.

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
- `--radius-card: 40px 2px 40px 2px` (corrected from rem)
- `--radius-photo: 60px 2px 60px 2px`
- `--radius-photo-mobile: 40px 2px 40px 2px`

### Typography additions
- `--fs-feature-heading`: weight 700, letter-spacing `-0.015em`
- `--fs-segment-name`: 2rem fixed, line-height 1.05

### Stat card
- Solid Deep Blue on white sections, solid white on Deep Blue sections.
- Always 6px left or right accent border using `--accent`.
- Number: lining figures, special characters (`%`, `+`, `$`) at full height (not superscript).
- Context text: weight 600, opaque white (or Deep Blue on white card).
- Metric text: weight 600, opaque white (or Deep Blue on white card).
- Photo is inset 2.5rem from outer column edge so the card sits in that lane without crossing into the text column.

### Animated background pattern
- Option A (slow horizontal drift, fades on the left) is the standard.

### Logo strip
- Logo height: 38px
- Default opacity: 0.85, hover: 1.0
- Marquee duration: 35s
- Edge fade width: 6rem

### Content tags (Option B locked)
- `tag--dark`: Deep Blue bg, white text — for light card backgrounds
- `tag--white`: White bg, Deep Blue text — for dark card backgrounds
- Tags are clickable — wired to placeholder URLs like `/resources/case-studies` with HTML comment documenting the WordPress integration intent.

### Pre-footer overlay treatment
- All four techniques combined: radial gradient (darker center, lighter edges) + 0.58 base opacity + full white subtext (1.0) + text shadows on heading and body.
- 25px top accent stripe using `--accent` (defaults to CropX Blue, set per segment).

### Footer (Variant C)
- 4 equal columns: brand (logo left-aligned + tagline + contact + social) + Platform + Solutions + Company
- Responsive: 4-col desktop → 2-col tablet (brand spans full width on top) → 1-col mobile
- Social: LinkedIn, X (Twitter), YouTube
- Legal bar: copyright + Privacy Policy, Terms of Use, Cookie Settings

---

## Segment Hero (In Progress)

The segment landing page hero combines:
- The standard `nav.html` foundation (white bar, full-color logo, click-to-open dropdowns)
- A segment-colored badge bolted between logo and nav links (specs above)
- Hero with left-side content (eyebrow + H1 with underline + subtext + accent-edge CTA)
- Right side: **hardware sensor PNG** (254px wide, fills hero height proportionally, anchored top-0, clipped at bottom edge of hero)
- Below hero: dark grey (#2C2C2C) "Hardware Lineup" section with **phone PNG** bleeding from hero down into this section

### Phone PNG overlap — work in progress
- **Goal:** phone PNG should overlap the dark grey section below the hero by **~30px only** (was previously overlapping much more)
- **Constraint:** aspect ratio must NEVER be distorted (`width: Xpx; height: auto !important`)
- Last action before context limit: I was about to inspect the phone bleed CSS to make this change but the response cut off

### Sensor PNG — locked specs
- `width: 254px !important; height: auto !important` (proportional, no distortion)
- `top: 0` anchored to top of container, bottom clips naturally via parent `overflow: hidden`
- Pushed to right edge of hero
- Hero has `overflow: hidden` on the hero element itself, but `.hero--A { overflow: visible }` so phone PNG can bleed below
- PNG positioning is relative to `.hero-wrap--A` (which wraps both hero + next section)

### Hero typography (matched to standard hero.html)
- Headline: `clamp(2.75rem, 6vw, 4.25rem)`, weight 600, line-height 1.0, max-width 16ch
- Underline: `<span class="hero-em">` with `text-decoration` + `text-underline-offset: 0.12em` (NOT border-bottom)
- Subheadline: 1.25rem, weight 400, line-height 1.35, full white with text-shadow
- Eyebrow: 0.75rem, weight 600, letter-spacing 0.12em, `rgba(255,255,255,0.85)`
- CTA button: `box-shadow: inset 0.5rem 0 0 var(--accent)`, weight 500, font-size 1.1875rem, padding 1rem 2.25rem

---

## Remaining Sections to Build

Based on landing page screenshots reviewed earlier:

### Build in HTML first (visually complex)
1. ~~Segment header with banner + product PNGs~~ — IN PROGRESS
2. **Hardware lineup carousel** — carousel interaction + product card layout
3. **Customer testimonials carousel** — avatar initials, dots, arrows
4. **Stats grid** — left column + 2x2 stat grid with accent borders, lining figures
5. **FAQ accordion** — +/− toggle, smooth open/close

### Build directly in WordPress
6. Alternating two-column layout (basically repeating feature-stat pattern)
7. Two-column text + photo (feature-stat without the stat card)
8. Two-column text + photo with PNG overlay

---

## Asset Library

### Photos available (in earlier conversation, may need re-uploading)
1. `hf_20260422...` — Farmer with phone in flowering field (current hero photo)
2. `iStock-1152871634` — Tulip field
3. `iStock-1179600353` — Leafy greens close-up
4. `iStock-1264967756` — Agronomist with tablet in orchard
5. `iStock-1365305727` — Early crop rows aerial
6. `iStock-1828370492` — Card1
7. `iStock-2160577695` — Food production
8. `iStock-2187703848` — Coffee plantation aerial
9. `iStock-2190962003` — Silos at dusk

### Icons (10)
alarm-clock, antenna, corn, field-sun, fields, language, nutrition, sensor-cloud, speed, valve-irrigation

### Logos
- CropX full color
- CropX white
- 11 customer logos: AB InBev, Dairy Holdings, General Mills, Rotoplas, NASA, NEC, HZPC, McCain, Nestlé, PepsiCo, Ritter Sport

### Product PNGs (uploaded in segment-hero session)
- Hardware sensor (had black background, removed for transparency)
- Phone with app screenshot (had black background, removed for transparency)

### Other
- `hero-pattern.svg` — animated background pattern (Option A drift)

---

## Workflow Reminders

### Saving work to GitHub
After updating any file in `~/Documents/github/cropx-website-2026/`:
```bash
cd ~/Documents/github/cropx-website-2026
git add .
git commit -m "Descriptive message"
git push
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
4. Then describe what you want to work on next

That gives Claude full context without needing the entire chat history re-pasted.
