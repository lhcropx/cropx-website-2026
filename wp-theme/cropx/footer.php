<?php
/**
 * Footer template — closes <main>, renders the site footer, closes <body>/<html>.
 *
 * Only the global site footer lives here. The pre-footer CTA is intentionally
 * NOT included — it has per-page content (heading, image, buttons) and should
 * be placed in each template or block pattern individually so it can be
 * customised per page.
 *
 * As of the Aug 2026 footer rebuild (task #305), the footer is a Synced
 * Pattern (Appearance → Patterns, slug "footer") built from native
 * core/group + core/heading + core/paragraph blocks, plus two small custom
 * blocks — cropx/footer-brand (logo/tagline/contact/social) and
 * cropx/menu-links (per-column links, pulled live from the same Nav Menus
 * the header uses). Lauren can now reorder columns, retitle headings, and
 * rearrange the brand block directly in the block editor — no code changes
 * needed for anything that used to require editing the old monolithic
 * cropx/footer block's ~15 hardcoded attributes.
 *
 * Looked up by slug (not a hardcoded numeric ID) via cropx_get_synced_block_ref()
 * so this stays correct across local/staging/production, same idiom used for
 * segment-navigation and the per-page pre-footer CTA patterns. Falls back to
 * the old cropx/footer block if the "footer" pattern hasn't been created yet
 * on this environment — see PROGRESS.md for the one-time setup steps.
 *
 * Wrapped in do_shortcode() because block content rendered via do_blocks()
 * outside the main loop doesn't get shortcodes auto-expanded — the footer's
 * copyright line uses [cropx_year] (see inc/helpers.php) instead of a
 * hardcoded/PHP-computed year.
 */
?>
	</main>
</div>

<?php
// ── Global site footer ───────────────────────────────────────────────────────
$_footer_pattern_ref = cropx_get_synced_block_ref( 'footer' );
if ( $_footer_pattern_ref ) {
	echo do_shortcode( do_blocks( '<!-- wp:block {"ref":' . $_footer_pattern_ref . '} /-->' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
	// One-time fallback until the "footer" Synced Pattern is created on this
	// environment (Appearance → Patterns → Add New → paste the markup from
	// the footer-rebuild patch notes, or see PROGRESS.md).
	echo do_blocks( '<!-- wp:cropx/footer /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>

<?php wp_footer(); ?>
</body>
</html>
