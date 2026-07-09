<?php
/**
 * Dealer Finder block — front-end render.
 *
 * Outputs the split-panel HTML structure.
 *
 * Mapbox GL CSS + JS are enqueued in inc/dealer-finder-api.php via
 * wp_enqueue_scripts (which fires before wp_head) so the CSS lands in <head>.
 * Enqueueing from here (during the_content) would be too late — the CSS would
 * miss wp_head and the map canvas would have no dimensions.
 *
 * Per-block config (token, REST URL, zoom/center defaults) is injected as a
 * plain inline <script> tag inside the block HTML.  This executes before
 * view.js (which loads in the footer), so window.cropxDealerFinder is always
 * defined by the time view.js runs.
 *
 * Requires:
 *   - get_option('cropx_mapbox_token') set via Admin → Settings → CropX
 *   - inc/dealer-finder-api.php for the REST endpoint + asset enqueue
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$map_height    = isset( $attributes['mapHeight'] )   ? (int)   $attributes['mapHeight']   : 600;
$default_zoom  = isset( $attributes['defaultZoom'] ) ? (float) $attributes['defaultZoom'] : 4;
$default_lat   = isset( $attributes['defaultLat'] )  ? (float) $attributes['defaultLat']  : 38.5;
$default_lng   = isset( $attributes['defaultLng'] )  ? (float) $attributes['defaultLng']  : -96;
$heading       = isset( $attributes['heading'] )     ? $attributes['heading']              : __( 'Find a CropX Dealer', 'cropx' );
$subtext       = isset( $attributes['subtext'] )     ? $attributes['subtext']              : __( 'Enter your zip code to find authorized CropX dealers near you.', 'cropx' );
$color_scheme  = isset( $attributes['colorScheme'] ) && 'dark' === $attributes['colorScheme'] ? 'dark' : 'light';

$mapbox_token = get_option( 'cropx_mapbox_token', '' );

// Config object read by view.js. Output inline in the block HTML so it is
// guaranteed to be available before the footer script executes.
$dealer_finder_config = wp_json_encode( array(
	'token'       => $mapbox_token,
	'apiUrl'      => rest_url( 'cropx/v1/dealers' ),
	'defaultZoom' => $default_zoom,
	'defaultLat'  => $default_lat,
	'defaultLng'  => $default_lng,
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'df-block df-scheme-' . $color_scheme,
	'style' => '--df-map-height:' . $map_height . 'px',
) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php /* Config for view.js — must come before the footer script executes */ ?>
	<script>window.cropxDealerFinder = <?php echo $dealer_finder_config; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;</script>

	<?php if ( ! $mapbox_token ) : ?>
		<div class="df-token-notice">
			<p><?php echo wp_kses_post( __( '<strong>Dealer Finder:</strong> No Mapbox token configured. Go to <a href="/wp-admin/options-general.php?page=cropx-settings">Settings → CropX</a> to add your token.', 'cropx' ) ); ?></p>
		</div>
	<?php endif; ?>

	<div class="df-layout">

		<!-- ── Sidebar ── -->
		<aside class="df-sidebar" aria-label="<?php esc_attr_e( 'Dealer list', 'cropx' ); ?>">

			<div class="df-sidebar-header">
				<?php if ( $heading ) : ?>
					<h2 class="df-heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $subtext ) : ?>
					<p class="df-subtext"><?php echo esc_html( $subtext ); ?></p>
				<?php endif; ?>
			</div>

			<div class="df-search-wrap">
				<div class="df-search-row">
					<input
						type="text"
						class="df-zip-input"
						placeholder="<?php esc_attr_e( 'Enter zip code…', 'cropx' ); ?>"
						aria-label="<?php esc_attr_e( 'Zip code', 'cropx' ); ?>"
						maxlength="10"
						inputmode="numeric"
						autocomplete="off"
					>
					<button class="df-search-btn" type="button" aria-label="<?php esc_attr_e( 'Search', 'cropx' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
							<path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</button>
				</div>
				<div class="df-search-controls">
					<button class="df-location-btn" type="button">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
							<circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/>
							<circle cx="12" cy="12" r="2.5" fill="currentColor"/>
							<path d="M12 4v3M12 17v3M4 12h3M17 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
						<span class="df-location-label"><?php esc_html_e( 'Use current location', 'cropx' ); ?></span>
					</button>
					<button class="df-search-clear" type="button" hidden aria-label="<?php esc_attr_e( 'Clear search', 'cropx' ); ?>">
						<?php esc_html_e( '✕ Clear', 'cropx' ); ?>
					</button>
				</div>
			</div>

			<p class="df-results-meta" aria-live="polite"></p>

			<ul class="df-list" role="list">
				<li class="df-item df-item--loading" aria-label="<?php esc_attr_e( 'Loading dealers…', 'cropx' ); ?>">
					<span class="df-loading-spinner" aria-hidden="true"></span>
					<span><?php esc_html_e( 'Loading dealers…', 'cropx' ); ?></span>
				</li>
			</ul>

		</aside>

		<!-- ── Map ── -->
		<div class="df-map-wrap" aria-label="<?php esc_attr_e( 'Dealer location map', 'cropx' ); ?>">
			<div class="df-map" id="cropx-dealer-map"></div>
		</div>

	</div><!-- .df-layout -->
</div>
