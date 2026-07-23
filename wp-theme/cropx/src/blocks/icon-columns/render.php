<?php
/**
 * Icon Columns block — front-end render.
 *
 * Items are stored in a flat `columns` array attribute and distributed into
 * independent flex column containers using PHP array_chunk(). This gives
 * column-major ordering (top-to-bottom within each column, then left-to-right)
 * and ensures that a tall item in one column never shifts items in adjacent
 * columns — there is no row-height coupling.
 *
 * Column distribution formula (mirrors edit.js getColRow()):
 *   chunk_size = ceil( count($items) / $num_cols )
 *   $col_groups = array_chunk( $items, $chunk_size )
 *
 * Attributes:
 *   columnCount       string  '3' | '4' | '5' | '6'
 *   eyebrow           string
 *   heading           string
 *   backgroundVariant string  'taupe' | 'white' | 'blue'
 *   segmentAccent     string  'general' | 'enterprise' | 'service-provider' | 'on-farm'
 *   columns           array   [ { icon, heading, body, ctaLabel, ctaUrl } ]
 *   eyebrowColor      string  'cropx-blue' | 'deep-blue' | 'white'
 *   showEyebrow       bool
 *   showHeading       bool
 *   showIcons         bool
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$column_count   = $attributes['columnCount']       ?? '3';
$eyebrow        = $attributes['eyebrow']            ?? '';
$heading        = $attributes['heading']             ?? '';
$bg_variant     = $attributes['backgroundVariant']   ?? 'white';
$segment_accent = $attributes['segmentAccent']       ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']        ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_heading   = (bool) ( $attributes['showHeading']  ?? true );
$show_icons     = (bool) ( $attributes['showIcons']    ?? true );

$all_items = $attributes['columns'] ?? array();

// Validate enums.
if ( ! in_array( $column_count, array( '3', '4', '5', '6' ), true ) ) {
	$column_count = '3';
}
$num_cols = (int) $column_count;

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

// Filter out blank items (editors can leave slots empty).
$all_items = array_values(
	array_filter(
		$all_items,
		function ( $item ) {
			return '' !== trim( $item['heading'] ?? '' ) || '' !== trim( $item['body'] ?? '' );
		}
	)
);

// ── Column-major distribution ─────────────────────────────────────────────
// Chunk the flat items array into $num_cols independent groups. The chunk
// size is ceil(total / num_cols), so each group forms one display column.
// array_chunk() produces fewer chunks than $num_cols when there aren't
// enough items (e.g. 5 items across 4 columns → 2 chunks of 2 + 1 of 1).
// Those "missing" columns just don't render — graceful degradation.
if ( ! empty( $all_items ) ) {
	$chunk_size = (int) ceil( count( $all_items ) / $num_cols );
	$col_groups = array_chunk( $all_items, $chunk_size );
} else {
	$col_groups = array();
}

// ── Section wrapper ───────────────────────────────────────────────────────
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
$allowed_body_tags = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$has_header = ( $show_eyebrow && $eyebrow ) || ( $show_heading && $heading );
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

		<?php if ( ! empty( $col_groups ) ) : ?>
		<div class="ici-columns ici-cols-<?php echo esc_attr( $column_count ); ?>">

			<?php foreach ( $col_groups as $col_items ) : ?>
			<div class="ici-col">

				<?php foreach ( $col_items as $item ) :
					$icon      = $item['icon']     ?? 'fields';
					$item_head = $item['heading']   ?? '';
					$item_body = $item['body']       ?? '';
					$cta_label = $item['ctaLabel']  ?? '';
					$cta_url   = $item['ctaUrl']    ?? '#';

					// Sanitize icon slug against the allowlist.
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

					<?php if ( $item_head ) : ?>
						<h3 class="ici-item-heading"><?php echo wp_kses( $item_head, $allowed_inline ); ?></h3>
					<?php endif; ?>

					<?php if ( $item_body ) : ?>
						<p class="ici-body"><?php echo wp_kses( $item_body, $allowed_body_tags ); ?></p>
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
			<?php endforeach; ?>

		</div>
		<?php endif; ?>

	</div>
</section>
