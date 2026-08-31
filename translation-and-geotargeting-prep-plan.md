# Translation & Geo-Targeting Prep Plan

Companion to `translation-readiness-audit.md`. That doc identified *what's* hardcoded; this one answers *how to fix it* — specifically, which parts are a plugin's job versus something we need to custom-code, and in what order to tackle it.

## Bottom line up front

**No plugin can fix Tiers 1, 3, or 5 from the audit, regardless of which one you pick.** Polylang, WPML, TranslatePress — none of them can duplicate-and-translate content that was never stored as a database row in the first place. Hardcoded PHP template strings, hardcoded JS strings, and hardcoded PHP arrays/logic are all structurally invisible to a content-duplication plugin. That work has to happen in code before any plugin can help. The plugin *is* the right tool for everything else — real Page content, the shared UI chrome strings (once registered), synced patterns, menus, and hreflang/SEO plumbing.

**Recommended plugin: Polylang** (not WPML) for this project. Both support `/es/` subdirectory URLs without a subdomain, which is the hard requirement. Polylang's free tier already includes string translation, custom-post-type translation (needed for Synced Patterns), and menu translation — the three features this site specifically needs — where WPML gates comparable functionality behind paid tiers. WPML is worth reconsidering only if you want a heavier translator-management workflow (assigning jobs to translators, translation memory, etc.) — not needed for a single new language on a marketing site.

---

## Plugin vs. custom code, by concern

| Concern | Plugin or custom code | Why |
|---|---|---|
| `/es/` subdirectory routing, language switcher, admin UI | **Plugin** (Polylang) | Exactly what it's built for — don't reinvent URL routing and rewrite rules. |
| Translating real Page content (hero, two-column, etc. built in the block editor) | **Plugin** | Standard duplicate-and-translate workflow, including "needs updating" flags when English changes. |
| Menu translation | **Plugin** | Polylang supports per-language menus assigned to the same theme location out of the box. |
| hreflang tags, basic sitemap awareness | **Plugin** | Comes free once Pages are linked as translations of each other. Verify as part of the existing sitemap/canonical QA backlog item. |
| Block attribute translatability (which of each custom block's 15–40 attributes are text vs. settings) | **Plugin config**, block-by-block | Polylang has a settings screen for this, plus a filter (`pll_translate_block_attribute`) for anything the UI doesn't cover. One-time setup per block (~30 blocks), not per page. Not really "custom code" so much as configuration specific to our blocks that no plugin does automatically. |
| Synced Patterns (Nav CTAs, pre-footer CTAs, demo forms — 11 total) | **Plugin**, with one small code patch | Once `wp_block` is enabled as a translatable post type in Polylang, patterns get the same duplicate-and-translate treatment as Pages. The patch: `cropx_get_synced_block_ref()` currently always looks up one global English slug — it needs a few lines added so it resolves through Polylang's `pll_get_post()` to fetch the *current language's* version of a pattern, falling back to English if no translation exists yet (same safe-fallback convention already used everywhere in this theme). |
| Tier 1 — fully hardcoded pages (`404.php`, `page-thank-you.php`) | **Custom code, no plugin involved** | Has to be rebuilt as real WordPress Pages (like `page-results.php`) before there's any content for a plugin to translate. This is a template rewrite, not a plugin setting. |
| Tier 2 — hardcoded UI chrome (`insights-archive.php`, `customer-stories.php`, `related-posts.php`, `single.php` breadcrumbs/share labels, `cropx_reading_time()`) | **Hybrid** — light custom code, plugin-managed storage | Rather than hand-rolling a `.po`/`.mo` gettext system, wire these through Polylang's own String Translation feature: register each string once with `pll_register_string()`, then call `pll__()` where the string is output. The actual translated text is then managed in Polylang's own admin screen (Languages → Strings translation) — no separate system to maintain. |
| Tier 3 — hardcoded JS strings ("Loading…," "Show More," "Read more," "Copied!") | **Custom code** (wiring), plugin-managed text | JS can't call `pll__()` directly — needs `wp_localize_script()` to hand the already-translated string from PHP to JS. The translated text itself still lives in Polylang's string table from the Tier 2 work above. |
| Tier 5 — hard blockers (`cropx_zoho_country_list()`, `inc/contact-form-api.php` validation/success messages) | **Custom code**, plugin-managed text | Same pattern as Tier 2/3 — route through `pll_register_string()`/`pll__()` rather than inventing a separate translation mechanism, but the branching logic itself (which language is active, which array/message to use) has to be written by hand since this is server-side REST logic, not stored content. |
| Cloudflare | **Infrastructure, not a plugin or code** | DNS/CDN account configuration outside WordPress entirely. |
| `inc/geo.php` visitor-country helper | **Custom code**, not a plugin | Once Cloudflare is in front of the site, it hands every request a `CF-IPCountry` header for free. A ~10-line helper reading that header is simpler and more reliable than adding a dedicated GeoIP plugin (with its own database/lookups) for something already solved upstream. |
| Geo-based language-suggestion banner | **Custom code** | Needs to match CropX's own design system and integrate with Polylang's per-language URLs — a generic plugin's banner UI wouldn't fit. Small component: check `inc/geo.php` + current language, show/hide a themed banner. Suggest only — never auto-redirect (see note below). |
| Country-dropdown geo-aware default sort | **Custom code**, optional/low priority | Bespoke to our own `cropx_zoho_country_list()` — no plugin territory here. |
| Dealer Finder auto-centering by region | **Custom code**, optional/low priority | Bespoke to our own dealer-finder block/API — same reasoning. |
| Unit conversions (acres ↔ hectares) in copy | **Content work**, not code | A translator/reviewer decision, not something to automate. |

**One standing rule for the geo work:** suggest a language switch, never force-redirect based on IP. Auto-redirecting by geolocation is a known anti-pattern — it breaks for VPN users, annoys returning visitors who prefer English, and can prevent Google's crawler (which typically crawls from US IPs) from ever indexing the Spanish pages if it keeps getting bounced away from them.

---

## Recommended sequencing

**Phase A — Decisions & infrastructure**
1. Decide: one generic `/es/` LATAM-Spanish tree, or country-specific variants later? Affects scope but not the plugin choice.
2. Stand up Cloudflare in front of the site (existing backlog item).
3. Install Polylang, add Spanish, set URLs to subdirectory mode (`/es/`), no subdomain.
4. In Polylang settings, enable `wp_block` (Synced Patterns) as a translatable post type; decide whether Customer Stories/Quotes CPTs should be translatable too.

**Phase B — Custom code prerequisites** (nothing in Phase C/D can start on these pages until this is done)
5. Rebuild `404.php` as a real Page, matching `page-results.php`'s pattern.
6. Rebuild `page-thank-you.php` the same way.
7. Register all Tier 2 chrome strings via `pll_register_string()` / `pll__()` (insights-archive.php, customer-stories.php, related-posts.php, single.php, `cropx_reading_time()`).
8. Wire Tier 3 JS strings through `wp_localize_script()`.
9. Patch `cropx_get_synced_block_ref()` to resolve via `pll_get_post()`.
10. Convert the Zoho country list and contact-form-api.php messages to route through Polylang's string API.

**Phase C — Per-block configuration**
11. Go block-by-block (~30 custom blocks) marking text attributes as translatable, settings attributes as not. One-time cost.

**Phase D — Geo layer** (can run in parallel with B/C once Cloudflare is live)
12. Build `inc/geo.php`.
13. Build the language-suggestion banner.
14. (Optional, later) Geo-aware country dropdown default.
15. (Optional, later, low priority) Dealer Finder auto-centering.

**Phase E — Content & QA**
16. Translate every Page, all 11 Synced Patterns, and every registered string into LATAM Spanish — professional review recommended over machine translation alone.
17. Review copy for unit conversions (acres/hectares, °F/°C).
18. Verify hreflang tags render correctly (ties into the existing sitemap/canonical QA backlog item).
19. End-to-end test of the contact form and country dropdown in Spanish.

None of this touches the live production site — it all happens on staging first, per the existing workflow.
