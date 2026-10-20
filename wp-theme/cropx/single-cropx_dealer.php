<?php
/**
 * Dealer single — single-cropx_dealer.php
 *
 * Public-facing page at /dealers/{post-name}/. Deliberately basic per
 * Lauren's brief (Aug 2026): "very simple, using the most basic pages we
 * have — like the Terms & Conditions page — as a reference point." No
 * newsletter subscribe CTA — just name, photo (if any), and contact details,
 * plus the "Fallback Cat. Pg. Demo Form + CTA" synced pattern.
 *
 * cropx_dealer only supports 'title' and 'thumbnail' (no 'editor', no
 * 'excerpt' — see inc/cpts.php), so all the real content here comes from
 * post meta: dealer_region, dealer_address, dealer_phone, dealer_email,
 * dealer_website. This template intentionally does NOT check dealer_active —
 * a directly-linked single dealer page should still load even if that dealer
 * has been hidden from the interactive map / basic archive list; only the
 * archive listing (archive-cropx_dealer.php) filters by that flag.
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

while ( have_posts() ) :
	the_post();

	$dealer_id      = get_the_ID();
	$dealer_region  = get_post_meta( $dealer_id, 'dealer_region', true );
	$dealer_address = get_post_meta( $dealer_id, 'dealer_address', true );
	$dealer_phone   = get_post_meta( $dealer_id, 'dealer_phone', true );
	$dealer_email   = get_post_meta( $dealer_id, 'dealer_email', true );
	$dealer_website = get_post_meta( $dealer_id, 'dealer_website', true );
	$dealer_thumb   = get_the_post_thumbnail_url( null, 'large' );
	?>

	<article class="section-padded">
		<div class="section-inner">

			<div class="scpt-detail">

				<?php if ( $dealer_thumb ) : ?>
					<div class="scpt-detail-photo">
						<img src="<?php echo esc_url( $dealer_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="eager" decoding="async">
					</div>
				<?php endif; ?>

				<div class="scpt-detail-body">

					<?php if ( $dealer_region ) : ?>
						<span class="scpt-resource-type"><?php echo esc_html( $dealer_region ); ?></span>
					<?php endif; ?>

					<h1 class="section-heading"><?php the_title(); ?></h1>

					<ul class="scpt-detail-list">
						<?php if ( $dealer_address ) : ?>
							<li><strong><?php esc_html_e( 'Address', 'cropx' ); ?></strong> <?php echo esc_html( $dealer_address ); ?></li>
						<?php endif; ?>
						<?php if ( $dealer_phone ) : ?>
							<li><strong><?php esc_html_e( 'Phone', 'cropx' ); ?></strong> <a href="<?php echo esc_attr( 'tel:' . preg_replace( '/[^0-9+]/', '', $dealer_phone ) ); ?>"><?php echo esc_html( $dealer_phone ); ?></a></li>
						<?php endif; ?>
						<?php if ( $dealer_email ) : ?>
							<li><strong><?php esc_html_e( 'Email', 'cropx' ); ?></strong> <a href="<?php echo esc_attr( 'mailto:' . $dealer_email ); ?>"><?php echo esc_html( $dealer_email ); ?></a></li>
						<?php endif; ?>
						<?php if ( $dealer_website ) : ?>
							<li><strong><?php esc_html_e( 'Website', 'cropx' ); ?></strong> <a href="<?php echo esc_url( $dealer_website ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( preg_replace( '#^https?://(www\.)?#i', '', untrailingslashit( $dealer_website ) ) ); ?></a></li>
						<?php endif; ?>
					</ul>

					<p style="margin-top: var(--space-8);">
						<a class="cta-link" href="<?php echo esc_url( get_post_type_archive_link( 'cropx_dealer' ) ); ?>">
							<?php esc_html_e( '← Back to Dealers', 'cropx' ); ?>
						</a>
					</p>

				</div>
			</div>

		</div>
	</article>

<?php endwhile;

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
$_dealer_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_dealer_pfc_ref
	? '<!-- wp:block {"ref":' . $_dealer_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
