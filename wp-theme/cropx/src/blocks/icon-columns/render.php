<?php
/**
 * Icon Columns block — front-end render.
 *
 * A flexible icon feature grid supporting 3, 4, 5, or 6 columns.
 * Replaces and extends the separate three-column-icons and six-column-icons blocks.
 *
 * Attributes:
 *   columnCount       string  '3' | '4' | '5' | '6'
 *   eyebrow           string
 *   heading           string
 *   backgroundVariant string  'taupe' | 'white' | 'blue'
 *   segmentAccent     string  'general' | 'enterprise' | 'service-provider' | 'on-farm'
 *   columns           array   [{icon, heading, body, ctaLabel, ctaUrl}]
 *   eyebrowColor      string  'cropx-blue' | 'deep-blue' | 'white'
 *   showEyebrow       bool
 *   showHeading       bool
 *   showIcons         bool
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$column_count   = $attributes['columnCount']       ?? '3';
$eyebrow        = $attributes['eyebrow']           ?? '';
$heading        = $attributes['heading']            ?? '';
$bg_variant     = $attributes['backgroundVariant']  ?? 'white';
$segment_accent = $attributes['segmentAccent']      ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']       ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_heading   = (bool) ( $attributes['showHeading'] ?? true );
$show_icons     = (bool) ( $attributes['showIcons']   ?? true );

$columns = $attributes['columns'] ?? array();

// Validate enums.
if ( ! in_array( $column_count, array( '3', '4', '5', '6' ), true ) ) {
	$column_count = '3';
}
if ( ! in_array( $bg_variant, array( 'taupe', 'white', 'blue' ), true ) ) {
	$bg_variant = 'white';
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

$section_class = 'ici-section ici-section--' . $bg_variant;
if ( in_array( $bg_variant, array( 'white', 'taupe' ), true ) && 'general' !== $segment_accent ) {
	$section_class .= ' ici-segment-' . $segment_accent;
}

$_attrs = array( 'class' => $section_class );
if ( in_array( $bg_variant, array( 'taupe', 'white' ), true ) ) {
	$_attrs['data-section-bg'] = $bg_variant;
}
$wrapper_attrs = get_block_wrapper_attributes( $_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$has_header = ( $show_eyebrow && $eyebrow ) || ( $show_heading && $heading );
$grid_class = 'ici-grid ici-grid--cols-' . $column_count;
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="ici-inner">

		<?php if ( $has_header ) : ?>
		<div class="ici-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $show_heading && $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $grid_class ); ?>">
			<?php foreach ( $columns as $col ) :
				$icon      = $col['icon']     ?? 'fields';
				$col_head  = $col['heading']  ?? '';
				$col_body  = $col['body']     ?? '';
				$cta_label = $col['ctaLabel'] ?? '';
				$cta_url   = $col['ctaUrl']   ?? '#';

				// Skip items with no heading and no body.
				if ( '' === $col_head && '' === $col_body ) {
					continue;
				}

				// Sanitize icon slug.
				if ( ! in_array( $icon, $allowed_icons, true ) ) {
					$icon = 'fields';
				}
			?>
			<div class="ici-item">
				<?php if ( $show_icons ) : ?>
				<div class="ici-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
						alt=""
						width="24"
						height="24"
					>
				</div>
				<?php endif; ?>

				<?php if ( $col_head ) : ?>
					<h3 class="ici-item-heading"><?php echo wp_kses( $col_head, $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $col_body ) : ?>
					<p class="ici-body"><?php echo wp_kses( $col_body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="ici-cta">
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
