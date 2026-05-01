# Project Progress & Handoff

Full state of the CropX website rebuild as of **May 2, 2026**. Use this as a context primer for any new Claude session so we never lose progress.

---

## ▶ Start here for next session

**Next task: build the FAQ accordion** (`blocks/faq-accordion.html`).

After that, work down the "Remaining blocks" list below. Once all blocks are built, we move to Phase 3: WordPress theme build.

---

## Quick Status

**Phase:** Block library in HTML/CSS (Phase 2 of 6) — nearly complete
**Approach:** Build standalone HTML/CSS blocks first, port into WordPress later
**Stack:** WordPress (Local) + GitHub + Claude Code
**Repo:** https://github.com/lhcropx/cropx-website-2026

### Done ✅
- Design token system (`tokens/tokens.css` + visual reference page)
- 11 core block files committed to `blocks/`
- Claude Code installed and authenticated locally
- `CLAUDE.md` project briefing

### Remaining ⬜
- 5 HTML blocks left to build (FAQ accordion, hardware lineup carousel, single testimonial, three-column text-with-icons, plus 3 simpler two-column variants)
- WordPress theme build (Phase 3)
- Internal staging review (Phase 4)
- Production go-live (Phase 5)

---

## Block Library — Committed (11)

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

## Remaining Blocks to Build

Order of priority for tomorrow / future sessions:

### Build in HTML
1. **FAQ accordion** ← next ← `blocks/faq-accordion.html` — expandable Q/A list with +/− toggle, smooth open/close
2. **Hardware lineup carousel** — `blocks/hardware-carousel.html` — products with names + descriptions, similar pattern to testimonials carousel
3. **Single testimonial block** — `blocks/testimonial-single.html` — big-quote spotlight version (Variant A from earlier exploration), one large quote with attribution, no carousel
4. **Three-column text-with-icons** — `blocks/three-column-icons.html` — eyebrow + heading + 3 columns. Each column: icon box (Deep Blue square with white icon), sub-heading, body, "See how it works →" link. Reference screenshot: "One platform for every layer of your operation."
5. **Alternating two-column layout** — `blocks/two-column-alternating.html` — repeating feature-stat pattern without the stat card, alternating photo left/right between rows
6. **Two-column text + photo** — `blocks/two-column-text-photo.html` — single row of feature-stat without the stat card
7. **Two-column text + photo with PNG overlay** — `blocks/two-column-png.html` — same as above but with a product PNG floating over the photo

---

## Asset Library

Note: assets were uploaded in earlier conversations and may need re-uploading per session.

### Photos (9)
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
4. Then describe what you want to work on next — or just say "let's pick up where we left off" and reference the **▶ Start here for next session** banner at the top.

That gives Claude full context without needing the entire chat history re-pasted.
