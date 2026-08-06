<?php
/**
 * Three-column icons block — front-end render.
 *
 * Columns are stored in the `columns` array attribute. Each item has:
 *   icon, heading, body, ctaLabel, ctaUrl
 *
 * Editors can reorder columns in the sidebar; this file always iterates in
 * whatever order the attribute array is stored.
 *
 * Variants:
 *   backgroundVariant=white + segment accent → tinted icon boxes
 *   backgroundVariant=blue                  → white icon boxes always
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow        = $attributes['eyebrow']           ?? '';
$heading        = $attributes['heading']            ?? '';
$bg_variant     = $attributes['backgroundVariant']  ?? 'taupe';
$segment_accent = $attributes['segmentAccent']      ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']       ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_heading   = (bool) ( $attributes['showHeading'] ?? true );
$show_icons     = (bool) ( $attributes['showIcons']   ?? true );

$columns = $attributes['columns'] ?? array();

// Validate enums.
if ( ! in_array( $bg_variant, array( 'taupe', 'white', 'blue' ), true ) ) {
	$bg_variant = 'taupe';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = array(
	// Crops & Plants
	'apple', 'asparagus', 'banana', 'beetroot', 'bell-pepper',
	'broccoli', 'carrot', 'celery', 'coriander', 'corn',
	'endive', 'grape', 'grapefruit-citrus', 'leek', 'lemon-citrus',
	'lettuce', 'onion', 'pear', 'peas', 'potato',
	'pumpkin', 'rapeseed', 'soybean', 'sprout', 'strawberry',
	'sugarcane', 'sunflower', 'tomato', 'tulip', 'wheat',
	// Field & Soil
	'fields', 'fields-2', 'field-sun', 'semicircle-field', 'single-fields',
	'soil', 'soil-sensor-vertex', 'layers', '3d', 'spiral-taper',
	// Water & Irrigation
	'droplet', 'droplets-irrigation', 'no-droplet', 'rain-bucket', 'recharge',
	'irrigation-history', 'irrigation-planning', 'spray-irrigation',
	'valve-irrigation', 'leaching', 'effluent',
	// Sensors & Connectivity
	'sensor', 'sensor-cloud', 'sensor-network', 'antenna', 'satellite',
	'bluetooth', 'wireless-signal', 'smartphone', 'battery-charge',
	'transmitted-cloud', 'pending-cloud', 'cloud-offline',
	'partner-connection', 'partner-connection-2',
	// Agronomy & Field Ops
	'planting', 'harvesting', 'scouting', 'machines-tractor', 'sprayer',
	'fertilization', 'fertilizer-record', 'spraying-record',
	'bug-pest', 'disease', 'nutrition',
	// Weather & Environment
	'thermometer', 'thermometer-hot', 'thermometer-cold', 'thermometer-temperature',
	'wind-direction', 'frequency', 'mountain-snow',
	'EC-electrical-conductivity', 'ET-evapotranspiration', 'speed', 'speed-2',
	// Data & Analytics
	'chart', 'report', 'trending-up', 'trending-down', 'history',
	'group-data', 'measurement-units', 'ruler',
	// Operations & UI
	'alarm-clock', 'calendar', 'date-time', 'settings', 'sync',
	'user', 'people-group', 'contact', 'email', 'password',
	'location-pin', 'link', 'language', 'label-tags', 'note-thumbtack',
	'attachment', 'file', 'idea-tip', 'glasses', 'expand',
	'reorder', 'spark', 'morning-digest',
);

$section_class = 'tci-section tci-section--' . $bg_variant;
if ( in_array( $bg_variant, array( 'white', 'taupe' ), true ) && 'general' !== $segment_accent ) {
	$section_class .= ' tci-segment-' . $segment_accent;
}

$_tci_attrs = array( 'class' => $section_class );
if ( in_array( $bg_variant, array( 'taupe', 'white' ), true ) ) {
	$_tci_attrs['data-section-bg'] = $bg_variant;
}
$wrapper_attrs = get_block_wrapper_attributes( $_tci_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$has_header = ( $show_eyebrow && $eyebrow ) || ( $show_heading && $heading );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tci-inner">

		<?php if ( $has_header ) : ?>
		<div class="tci-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $show_heading && $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="tci-grid">
			<?php foreach ( $columns as $col ) :
				$icon      = $col['icon']     ?? 'fields';
				$col_head  = $col['heading']  ?? '';
				$col_body  = $col['body']     ?? '';
				$cta_label = $col['ctaLabel'] ?? '';
				$cta_url   = $col['ctaUrl']   ?? '#';

				// Skip items that have no heading and no body — allows editors to
				// leave any slot empty without it rendering as a blank grid cell.
				if ( '' === $col_head && '' === $col_body ) {
					continue;
				}

				// Sanitize icon slug against allowlist.
				if ( ! in_array( $icon, $allowed_icons, true ) ) {
					$icon = 'fields';
				}
			?>
			<div class="tci-item">
				<?php if ( $show_icons ) : ?>
				<div class="tci-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
						alt=""
						width="24"
						height="24"
					>
				</div>
				<?php endif; ?>

				<?php if ( $col_head ) : ?>
					<h3 class="tci-item-heading"><?php echo wp_kses( $col_head, $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $col_body ) : ?>
					<p class="tci-body"><?php echo wp_kses( $col_body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tci-cta">
						<?php echo esc_html( $cta_label ); ?>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
