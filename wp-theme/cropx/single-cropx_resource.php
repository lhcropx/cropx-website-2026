<?php
/**
 * Resource single — single-cropx_resource.php
 *
 * Public-facing page at /resources-archive/{post-name}/. Deliberately basic
 * per Lauren's brief (Aug 2026): "very simple, using the most basic pages we
 * have — like the Terms & Conditions page — as a reference point." No
 * newsletter subscribe CTA — just title, resource type, thumbnail, excerpt,
 * download button(s), and the "Fallback Cat. Pg. Demo Form + CTA" synced
 * pattern.
 *
 * cropx_resource only supports 'title', 'excerpt', 'thumbnail' (no 'editor'
 * — see inc/cpts.php), so there's no post_content to render here. The
 * excerpt (cropx_get_card_excerpt()) is the resource's description; the
 * actual files live in the download_url_* post meta fields.
 *
 * Download buttons (Aug 2026 feedback — "should include buttons / drop-down
 * menus to download the resource, very similar to what we use in the
 * resource grid block"): this reuses the resource-downloads block's own
 * markup, CSS classes (.rsd-download-btn, .rsd-version-picker,
 * .rsd-version-select, .rsd-version-download), download icon, and even its
 * view.js (manually enqueued — see inc/enqueue.php) rather than
 * reimplementing an equivalent picker, so a visitor sees and gets the exact
 * same download experience here as on any page using that block. The
 * 4-branch fallback logic (2+ versions → select + button; exactly 1 → plain
 * button; legacy General URL field → plain button; nothing on file → no
 * download section at all, since linking to this same page would be
 * pointless) mirrors resource-downloads/render.php's logic exactly — see
 * that file's doc comment for the full reasoning.
 *
 * Thumbnail corners (Aug 2026 feedback): all 4 corners at a plain 2px
 * radius via .scpt-detail-photo--doc, NOT the CropX signature asymmetric
 * --radius-card used by Dealers/Team's detail photos — a document cover
 * reads better with a simple, uniform corner treatment than the brand's
 * usual lifestyle-photo card shape.
 *
 * Assets:
 *   styles/simple-cpt.css                       — shared "simple CPT" chrome.
 *   build/blocks/resource-downloads/style-index.css + view.js — reused
 *     download button/picker styling and behavior; manually enqueued on
 *     is_singular('cropx_resource') in inc/enqueue.php since this page
 *     doesn't use the actual Gutenberg block (no auto-enqueue trigger).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

// Download icon — copied from resource-downloads/render.php so the button
// looks identical wherever it appears.
$res_download_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
	. '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
	. '<polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
	. '<line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>'
	. '</svg>';

while ( have_posts() ) :
	the_post();

	$res_id       = get_the_ID();
	$res_types    = get_the_terms( $res_id, 'cropx_resource_type' );
	$res_type     = ( $res_types && ! is_wp_error( $res_types ) ) ? $res_types[0] : null;
	$res_thumb    = get_the_post_thumbnail_url( null, 'large' );
	$res_excerpt  = cropx_get_card_excerpt( null, 80 );

	// Same version resolution + fallback chain as resource-downloads/render.php.
	$res_download_url = get_post_meta( $res_id, 'download_url', true );
	$res_versions      = cropx_resource_get_available_versions( $res_id );
	$res_version_count = count( $res_versions );
	$res_default_version = $res_versions[0] ?? null;
	foreach ( $res_versions as $v ) {
		if ( 'en_a4' === $v['code'] ) {
			$res_default_version = $v;
			break;
		}
	}
	?>

	<article class="section-padded">
		<div class="section-inner">

			<div class="scpt-detail">

				<?php if ( $res_thumb ) : ?>
					<div class="scpt-detail-photo scpt-detail-photo--doc">
						<img src="<?php echo esc_url( $res_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="eager" decoding="async">
					</div>
				<?php endif; ?>

				<div class="scpt-detail-body">

					<?php if ( $res_type ) : ?>
						<span class="scpt-resource-type"><?php echo esc_html( $res_type->name ); ?></span>
					<?php endif; ?>

					<h1 class="section-heading"><?php the_title(); ?></h1>

					<?php if ( $res_excerpt ) : ?>
						<p class="section-body"><?php echo esc_html( $res_excerpt ); ?></p>
					<?php endif; ?>

					<?php if ( $res_version_count >= 2 ) : ?>
						<div class="rsd-version-picker" style="margin-top: var(--space-6); max-width: 22rem;">
							<select class="rsd-version-select" aria-label="<?php esc_attr_e( 'Choose language and format', 'cropx' ); ?>">
								<?php foreach ( $res_versions as $v ) : ?>
									<option value="<?php echo esc_url( $v['url'] ); ?>"
										data-code="<?php echo esc_attr( $v['code'] ); ?>"
										<?php selected( $v['code'], $res_default_version['code'] ?? '' ); ?>>
										<?php echo esc_html( $v['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<a href="<?php echo esc_url( $res_default_version['url'] ?? '' ); ?>"
							   class="rsd-download-btn rsd-version-download"
							   target="_blank" rel="noopener noreferrer">
								<?php echo $res_download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
							</a>
						</div>
					<?php elseif ( 1 === $res_version_count ) : ?>
						<a href="<?php echo esc_url( $res_default_version['url'] ); ?>"
						   class="rsd-download-btn"
						   style="margin-top: var(--space-6);"
						   target="_blank" rel="noopener noreferrer">
							<?php echo $res_download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
						</a>
					<?php elseif ( $res_download_url ) : ?>
						<a href="<?php echo esc_url( $res_download_url ); ?>"
						   class="rsd-download-btn"
						   style="margin-top: var(--space-6);"
						   target="_blank" rel="noopener noreferrer">
							<?php echo $res_download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
						</a>
					<?php endif; ?>

					<p style="margin-top: var(--space-8);">
						<a class="cta-link" href="<?php echo esc_url( get_post_type_archive_link( 'cropx_resource' ) ); ?>">
							<?php esc_html_e( '← Back to Resources', 'cropx' ); ?>
						</a>
					</p>

				</div>
			</div>

		</div>
	</article>

<?php endwhile;

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
$_resource_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_resource_pfc_ref
	? '<!-- wp:block {"ref":' . $_resource_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
