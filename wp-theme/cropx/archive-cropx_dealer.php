<?php
/**
 * Dealers archive — archive-cropx_dealer.php
 *
 * Public-facing page at /dealers/. has_archive was flipped true for
 * cropx_dealer in Aug 2026 specifically to route here (see the comment on
 * that flag in inc/cpts.php for the full history — it was false because a
 * fancier /dealers/ Page with the interactive dealer-finder map block was
 * once planned for that URL; that Page was never built, so there was nothing
 * left to collide with).
 *
 * Deliberately basic per Lauren's brief (Aug 2026): "very simple, using the
 * most basic pages we have — like the Terms & Conditions page — as a
 * reference point." A plain list of dealers — no map, no filtering UI (that's
 * what the dealer-finder block is for elsewhere) — just name, region, address,
 * phone, and a website link, each linking through to its own simple detail
 * page (single-cropx_dealer.php).
 *
 * Only dealers with dealer_active meta != '0' are listed, matching the
 * dealer-finder block's own filtering — a dealer an editor has hidden from
 * the interactive map shouldn't reappear in this plain list either. That
 * filtering is done via a pre_get_posts filter in inc/cpts.php, NOT in this
 * file — see that filter's doc comment for exactly why it has to live there
 * (short version: template files are included after the main query has
 * already run, so a pre_get_posts callback registered here would never
 * actually apply to this page's own listing — an actual bug in an earlier
 * version of this file, fixed Aug 2026 after Lauren reported inactive
 * dealers still showing up here).
 *
 * Assets:
 *   styles/simple-cpt.css — enqueued on this + the other 5 "simple CPT"
 *                            templates, see inc/enqueue.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );
?>

<section class="section-padded">
	<div class="section-inner">

		<div class="pg-header section-header section-header--left">
			<span class="section-eyebrow"><?php esc_html_e( 'Dealers', 'cropx' ); ?></span>
			<h1 class="section-heading"><?php esc_html_e( 'Dealers', 'cropx' ); ?></h1>
			<p class="section-body"><?php esc_html_e( 'CropX authorized dealers and distributors.', 'cropx' ); ?></p>
		</div>

		<?php if ( ! have_posts() ) : ?>

			<div class="scpt-empty">
				<p><?php esc_html_e( 'No dealers found.', 'cropx' ); ?></p>
			</div>

		<?php else : ?>

			<div class="scpt-grid">
				<?php
				while ( have_posts() ) :
					the_post();

					$dealer_id      = get_the_ID();
					$dealer_region  = get_post_meta( $dealer_id, 'dealer_region', true );
					$dealer_address = get_post_meta( $dealer_id, 'dealer_address', true );
					$dealer_phone   = get_post_meta( $dealer_id, 'dealer_phone', true );
					$dealer_thumb   = get_the_post_thumbnail_url( null, 'medium' );
					?>
					<a href="<?php the_permalink(); ?>" class="scpt-card">

						<?php if ( $dealer_thumb ) : ?>
							<div class="scpt-card-img">
								<img src="<?php echo esc_url( $dealer_thumb ); ?>"
									alt="<?php echo esc_attr( get_the_title() ); ?>"
									loading="lazy" decoding="async">
							</div>
						<?php endif; ?>

						<div class="scpt-card-body">
							<?php if ( $dealer_region ) : ?>
								<span class="scpt-card-badge"><?php echo esc_html( $dealer_region ); ?></span>
							<?php endif; ?>

							<h2 class="scpt-card-title"><?php the_title(); ?></h2>

							<?php if ( $dealer_address ) : ?>
								<p class="scpt-card-excerpt"><?php echo esc_html( $dealer_address ); ?></p>
							<?php endif; ?>

							<?php if ( $dealer_phone ) : ?>
								<p class="scpt-card-meta"><?php echo esc_html( $dealer_phone ); ?></p>
							<?php endif; ?>
						</div>

					</a>
				<?php endwhile; ?>
			</div>

			<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
				<div class="scpt-pagination">
					<?php
					the_posts_pagination( array(
						'prev_text' => __( '← Previous', 'cropx' ),
						'next_text' => __( 'Next →', 'cropx' ),
					) );
					?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>

<?php
// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
$_dealers_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_dealers_pfc_ref
	? '<!-- wp:block {"ref":' . $_dealers_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
