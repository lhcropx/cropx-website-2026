# CropX Website — Translation Readiness Audit (LATAM Spanish / `/es/`)

Prepared to scope what stands between the current site and a `cropx.com/es/` Spanish version. The core issue: a content-duplication plugin (Polylang, WPML) can only translate text that lives in the WordPress database — a Page's content, a synced pattern, a menu item. Anything hardcoded directly into a PHP template or a JS file is invisible to it and will show up in English on every language version, forever, until someone fixes it at the code level.

No multilingual plugin is installed yet, and none of the hardcoded strings below are even wrapped in WordPress's own translation functions (`__()`) — they're raw text baked into templates.

## TL;DR

- **Good news:** `page-results.php` and `page.php` are built the right way — real Page content via `the_content()`, nothing hardcoded. Nav menu items and (mostly) the footer are also standard, translatable WordPress content.
- **The real scope:** two root-level pages (404, Thank You) are 100% hardcoded PHP with no translatable content at all. Every archive/category/tag/single template layers hardcoded chrome (headings, "Read more," "Show More," breadcrumbs, share labels) around otherwise-real content. Three JS files hardcode UI strings that can't be swapped per language at all right now. And two files contain hard blockers — content a translation plugin fundamentally cannot reach, no matter how it's configured.
- **11 synced patterns** (Nav CTAs, pre-footer CTAs, demo forms) are shared across many pages — each needs its own per-language version, and someone has to remember to keep them in sync by hand.

---

## Tier 1 — Fully hardcoded pages (no translatable content exists at all)

| Page | What's hardcoded |
|---|---|
| **404.php** | Heading "404 Error," subheading ("Looks like this page doesn't exist..."), CTA "Contact Us," both card-grid headings ("Latest Results & Research," "Recent Ag Industry Insights"). |
| **page-thank-you.php** | Fallback hero heading "Thank You" + subheading, and — unconditionally, not just as a fallback — both card-grid headings. |

**Fix required:** rebuild these as real WordPress Pages using block-editor content (like `page-results.php` does), or wrap every string in `__()` and maintain a `.po`/`.mo` translation file. The first option is less work and matches the rest of the site's pattern.

## Tier 2 — Partially hardcoded pages + the shared functions behind them

Several pages render *real* Page content for the hero, but the surrounding chrome — grid headings, filter labels, empty states, "Read more" / "Show More" — comes from a small number of shared PHP functions. This is good news structurally: fixing the function fixes every page that uses it.

- **`inc/insights-archive.php`** is the single source of the "Ag Insights" / "News" headings and intro copy seen on `page-insights.php`, `page-news.php`, `category.php`, and `tag.php` — plus the shared grid renderer's "No articles found," "Filter by topic," "Filter by category," "Read more," "Show More." Fix this one file and four+ pages inherit it.
- **`inc/customer-stories.php`** does the same job for the Results & Research grid: "No customer stories found," "Filter by topic," "Filter by type," "Read more," "Show More," "All."
- **`inc/helpers.php`**'s `cropx_reading_time()` hardcodes "%d min read," shown on every blog post and case study.
- **`inc/parts/related-posts.php`** hardcodes "Keep Reading," "More from CropX," "View all posts," "Read more" — appears at the bottom of every blog post.
- **`single.php`** (blog posts) hardcodes breadcrumb labels, "Share this article," "In this article," and several accessibility labels (reading progress, share buttons, table of contents).
- **`single-cropx_publication.php`** / **`single-cropx_publication-video-testimonial.php`** hardcode breadcrumbs, download CTA copy ("Download the Full Case Study," "Get the complete methodology..."), field labels (Company/Region/Scale/Challenge/Solution), and related-content section headings/CTAs.
- **`category.php`** / **`tag.php`** fall back to hardcoded "Articles" / "Browse CropX articles on this topic" when a category or tag isn't owned by a registered group.
- **`archive.php`** (date archives) and the retired **`archive-cropx_publication.php`** also carry their own hardcoded chrome, lower priority since they're not primary landing pages.

**Fix required:** these are shorter, more contained fixes than Tier 1 — mostly a matter of deciding how the translation plugin should reach these strings (gettext `.po`/`.mo` files work fine here since these are genuinely UI chrome, not page-specific marketing copy).

## Tier 3 — Hardcoded JavaScript UI strings

`assets/js/blog-archive.js`, `pub-archive.js`, and `ag-archive.js` all hardcode "Loading…," "Show More," and "Read more" directly as JS string literals — not pulled from any PHP-localized data, so they render in English regardless of site language. `blog-single.js` hardcodes "Copied!" for the copy-link button.

**Fix required:** route these through `wp_localize_script()` so PHP can hand the JS file the correct-language string. Small, mechanical fix once flagged — this is the kind of thing that's easy to miss without an audit like this.

## Tier 4 — Synced patterns needing per-language versions

These live in the database (Appearance → Patterns) and are reused across many pages by slug lookup:

`results-cat-pg-demo-form-cta`, `demo-contact-form`, `press-room-pre-footer-cta`, `post-submit-thank-you-hero`, `post-submit-thank-you-pre-footer-cta`, `404-error-contact-form-cta`, `insights-pre-footer-cta`, `results-pre-footer-cta`, `insights-cat-pg-demo-form-cta`, `news-cat-pg-demo-form-cta`, `segment-navigation`

That's **11 patterns**. Each needs a Spanish counterpart, and — this is the important part for your "will it stay in sync" question — the theme currently has no locale-aware lookup mechanism, so wiring up a Spanish version per pattern is itself a small piece of engineering, not just a content task.

## Tier 5 — Hard blockers (not fixable by any translation plugin)

Two places return content a duplicate-and-translate plugin has literally no visibility into, because it's server-side logic, not stored content:

- **`inc/helpers.php`**'s `cropx_zoho_country_list()` — a flat PHP array of 250+ hardcoded English country names populating the Country dropdown on every Zoho-backed contact form.
- **`inc/contact-form-api.php`** — the REST endpoint's validation and success messages ("First name is required," "Thank you — we'll be in touch shortly," etc.), returned as JSON to JS on form submit.

**Fix required:** these need custom locale-detection logic in the PHP itself (e.g., check the current Polylang/WPML language and branch to a Spanish array/string set) — genuinely outside what any WordPress translation plugin manages automatically.

## Nav / Footer — mostly fine

Nav menu *items* (Platform, Solutions, Knowledge Hub, About, Contact dropdowns) are real WordPress Nav Menus and translate normally — Polylang/WPML both support per-language menus assigned to the same theme location. The footer is a single clean block reference (`do_blocks('<!-- wp:cropx/footer /-->')`), which is the right pattern.

The nav *chrome* around those menus is hardcoded, though: top-level trigger labels ("Platform," "Solutions," "Knowledge Hub," "About," "Contact," "Log in"), the "Products" mega-menu heading and "View all products" CTA (explicitly not a menu item, per the code's own comment), and a handful of accessibility labels.

---

## What this means for your three questions

**Should you pilot it now?** Yes — this audit is the argument for doing it now rather than later. A pilot on a page like a segment landing page (built cleanly, no hardcoded chrome around it) will work fine today. Trying to translate the whole site right now would immediately surface all five tiers above at once, which is a much bigger distraction than doing it page by page.

**Will English changes stay in sync?** For real Page content, Polylang/WPML flag translations as "needs updating" when English changes — manual re-edit, but at least flagged. For everything in Tiers 1–3 and 5, there's no flagging mechanism at all right now, since it's not stored as translatable content — a change there is invisible to the translation system until someone remembers to go looking.

**How much copy-pasting?** Depends entirely on which tier a given page falls into. Pages like `page-results.php` (Tier "none") will be close to zero manual copy-paste beyond the initial translation. Tiers 1–3 require actual code changes before translation is even possible — that's a one-time engineering cost, not ongoing copy-paste, but it has to happen before those pages can be translated at all.
