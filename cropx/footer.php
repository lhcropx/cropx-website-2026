<?php
/**
 * Footer template — closes <main>, renders the site footer block, closes <body>/<html>.
 *
 * Only the global site footer lives here. The pre-footer CTA is intentionally
 * NOT included — it has per-page content (heading, image, buttons) and should
 * be placed in each template or block pattern individually so it can be
 * customised per page.
 */
?>
	</main>
</div>

<?php
// ── Global site footer ───────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/footer /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php wp_footer(); ?>
</body>
</html>
