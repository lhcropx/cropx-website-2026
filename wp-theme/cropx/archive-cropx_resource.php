<?php
/**
 * Resources archive — archive-cropx_resource.php
 *
 * Public-facing page at /resources-archive/ (has_archive => true, rewrite
 * slug 'resources-archive' — see inc/cpts.php). Deliberately basic per Lauren's brief
 * (Aug 2026): "very simple, using the most basic pages we have — like the
 * Terms & Conditions page — as a reference point." No hero, no newsletter
 * subscribe section — just a plain title band, a simple card grid, and the
 * "Fallback Cat. Pg. Demo Form + CTA" synced pattern in place of a plain
 * pre-footer CTA block.
 *
 * Cards link straight to the single resource (single-cropx_resource.php),
 * which is where the actual download buttons live — this archive is a
 * browse/discovery view only.
 *
 * Card thumbnail framing (Aug 2026 feedback): "should be designed to look a
 * bit more like the cards in the 3-wide version of the resource grid block,
 * in terms of how the thumbnail image fits in its frame. That should be the
 * only similarity though." So rather than reimplement an equivalent, the
 * cover frame here directly reuses that block's own .rsd-cover-wrap/.rsd-cover
 * markup and CSS classes (its stylesheet is manually enqueued on this archive
 * too — see inc/enqueue.php) — a fixed 8.5:11 (US Letter) frame, cropped from
 * the bottom, never the top, where a document's title lives. Everything else
 * about this card (shape, radius, shadow, badge, button styling) is
 * .scpt-card's own and unrelated to that block, on purpose.
 *
 * Landscape documents (Aug 2026 follow-up): a document whose real page is
 * wider than it is tall would otherwise lose its left/right edges under
 * plain object-fit:cover in that fixed portrait frame — reported by Lauren
 * against "CropX System – Detailed Overview" and similar landscape
 * brochures. Ported the exact same fix already used inside the
 * resource-downloads block itself: a landscape cover renders as TWO stacked
 * images sharing one source — a full-bleed, blurred, slightly enlarged copy
 * behind (.rsd-cover--bg) so the frame never shows hard empty bars, and the
 * untouched, fully-visible page on top sized to fit by width
 * (.rsd-cover--fg, object-fit:contain). Portrait documents (the majority)
 * are unaffected — single image, plain object-fit:cover, exactly as before.
 * See resource-downloads/render.php's own doc comment for the fuller
 * explanation of this technique; the cover-resolution logic below
 * (download_attachment_id → featured image → skip) is copied from there
 * verbatim so the two places can never disagree about which image is "the"
 * cover for a given resource.
 *
 * Assets:
 *   styles/simple-cpt.css                              — shared "simple CPT" chrome.
 *   build/blocks/resource-downloads/style-index.css — .rsd-cover-wrap/.rsd-cover
 *     framing + landscape treatment, manually enqueued on this archive AND on
 *     single-cropx_resource.php — see inc/enqueue.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
// PHP-rendered (not a Gutenberg block), same as every other archive/single
// template in this theme — see category.php, tag.php, single.php.
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );
?>

<section class="section-padded">
	<div class="section-inner">

		<div class="pg-header section-header section-header--left">
			<span class="section-eyebrow"><?php esc_html_e( 'Resources', 'cropx' ); ?></span>
			<h1 class="section-heading"><?php esc_html_e( 'Resources', 'cropx' ); ?></h1>
			<p class="section-body"><?php esc_html_e( 'Brochures, datasheets, and reports on CropX hardware and platform capabilities.', 'cropx' ); ?></p>
		</div>

		<?php if ( ! have_posts() ) : ?>

			<div class="scpt-empty">
				<p><?php esc_html_e( 'No resources found.', 'cropx' ); ?></p>
			</div>

		<?php else : ?>

			<div class="scpt-grid">
				<?php
				while ( have_posts() ) :
					the_post();

					$res_id      = get_the_ID();
					$res_types   = get_the_terms( $res_id, 'cropx_resource_type' );
					$res_type    = ( $res_types && ! is_wp_error( $res_types ) ) ? $res_types[0] : null;
					$res_excerpt = cropx_get_card_excerpt( null, 20 );

					// ── Cover image — same resolution + landscape check as
					// resource-downloads/render.php (see this file's doc
					// comment above for why they're kept identical).
					$res_cover_src_id   = 0;
					$res_cover_src_data = false;

					$res_attachment_id = (int) get_post_meta( $res_id, 'download_attachment_id', true );
					if ( $res_attachment_id ) {
						$data = wp_get_attachment_image_src( $res_attachment_id, 'cropx-doc-cover' );
						if ( $data && ! empty( $data[1] ) && ! empty( $data[2] ) ) {
							$res_cover_src_id   = $res_attachment_id;
							$res_cover_src_data = $data;
						}
					}
					if ( ! $res_cover_src_data && has_post_thumbnail( $res_id ) ) {
						$thumb_id = get_post_thumbnail_id( $res_id );
						$data     = wp_get_attachment_image_src( $thumb_id, 'cropx-doc-cover' );
						if ( $data && ! empty( $data[1] ) && ! empty( $data[2] ) ) {
							$res_cover_src_id   = $thumb_id;
							$res_cover_src_data = $data;
						}
					}

					$res_is_landscape = $res_cover_src_data && $res_cover_src_data[1] > $res_cover_src_data[2];
					?>
					<a href="<?php the_permalink(); ?>" class="scpt-card">

						<?php if ( $res_cover_src_id ) : ?>
							<div class="rsd-cover-wrap<?php echo $res_is_landscape ? ' rsd-cover-wrap--landscape' : ''; ?>">
								<?php if ( $res_is_landscape ) : ?>
									<?php echo wp_get_attachment_image( $res_cover_src_id, 'cropx-doc-cover', false, array(
										'class'       => 'rsd-cover rsd-cover--bg',
										'loading'     => 'lazy',
										'alt'         => '',
										'aria-hidden' => 'true',
									) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
								<?php echo wp_get_attachment_image( $res_cover_src_id, 'cropx-doc-cover', false, array(
									'class'   => 'rsd-cover' . ( $res_is_landscape ? ' rsd-cover--fg' : '' ),
									'loading' => 'lazy',
									'alt'     => esc_attr( get_the_title() ),
								) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>

						<div class="scpt-card-body">
							<?php if ( $res_type ) : ?>
								<span class="scpt-card-badge"><?php echo esc_html( $res_type->name ); ?></span>
							<?php endif; ?>

							<h2 class="scpt-card-title"><?php the_title(); ?></h2>

							<?php if ( $res_excerpt ) : ?>
								<p class="scpt-card-excerpt"><?php echo esc_html( $res_excerpt ); ?></p>
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
// Fallback group's own pattern, per Lauren (Aug 2026) — falls back to the plain
// default block if the pattern hasn't been created yet on this environment, so
// nothing goes missing (same lookup-by-slug approach as category.php/tag.php).
$_resources_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_resources_pfc_ref
	? '<!-- wp:block {"ref":' . $_resources_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
