<?php
/**
 * Product Tabs block — front-end render.
 *
 * Outputs a two-panel tabbed product grid. Each card is a link that
 * smooth-scrolls to an anchor section further down the page.
 * Tab switching is handled by view.js (registered as viewScript).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$hardware_label = $attributes['hardwareLabel'] ?? 'Hardware';
$software_label = $attributes['softwareLabel'] ?? 'Software';
$hardware_items = $attributes['hardwareItems'] ?? [];
$software_items = $attributes['softwareItems'] ?? [];

// Unique ID so aria-controls wires up correctly when the block
// appears multiple times on the same page.
$block_id = wp_unique_id( 'ptabs-' );

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'ptabs-block' ] );

/**
 * Render the cards for one panel.
 *
 * @param array $items  Array of card attribute objects.
 * @return string       HTML string.
 */
function cropx_ptabs_render_cards( array $items ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$html = '';
	foreach ( $items as $item ) {
		$name    = esc_html( $item['name']     ?? '' );
		$tagline = esc_html( $item['tagline']  ?? '' );
		$anchor  = ltrim( $item['anchor'] ?? '', '#' );
		$img_url = esc_url( $item['imageUrl']  ?? '' );
		$img_alt = esc_attr( $item['imageAlt'] ?? $name );
		$href    = $anchor ? '#' . esc_attr( $anchor ) : '#';

		$html .= '<a class="ptabs-card" href="' . $href . '">';

		if ( $img_url ) {
			$html .= '<div class="ptabs-card-image">';
			$html .= '<img src="' . $img_url . '" alt="' . $img_alt . '" loading="lazy">';
			$html .= '</div>';
		}

		$html .= '<div class="ptabs-card-body">';
		$html .= '<p class="ptabs-card-name">' . $name;
		$html .= '<span class="ptabs-card-arrow" aria-hidden="true">→</span>';
		$html .= '</p>';

		if ( $tagline ) {
			$html .= '<p class="ptabs-card-tagline">' . $tagline . '</p>';
		}

		$html .= '</div>'; // .ptabs-card-body
		$html .= '</a>';   // .ptabs-card
	}

	return $html;
}
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="ptabs-tabs"
		role="tablist"
		aria-label="<?php echo esc_attr( $hardware_label . ' / ' . $software_label ); ?>"
	>
		<button
			class="ptabs-tab is-active"
			role="tab"
			aria-selected="true"
			aria-controls="<?php echo esc_attr( $block_id . '-hardware' ); ?>"
			data-tab="hardware"
		><?php echo esc_html( $hardware_label ); ?></button>

		<button
			class="ptabs-tab"
			role="tab"
			aria-selected="false"
			aria-controls="<?php echo esc_attr( $block_id . '-software' ); ?>"
			data-tab="software"
		><?php echo esc_html( $software_label ); ?></button>
	</div>

	<div class="ptabs-panels">
		<div
			class="ptabs-panel is-active"
			id="<?php echo esc_attr( $block_id . '-hardware' ); ?>"
			role="tabpanel"
			aria-label="<?php echo esc_attr( $hardware_label ); ?>"
		>
			<div class="ptabs-grid">
				<?php echo cropx_ptabs_render_cards( $hardware_items ); // phpcs:ignore ?>
			</div>
		</div>

		<div
			class="ptabs-panel"
			id="<?php echo esc_attr( $block_id . '-software' ); ?>"
			role="tabpanel"
			aria-label="<?php echo esc_attr( $software_label ); ?>"
			hidden
		>
			<div class="ptabs-grid">
				<?php echo cropx_ptabs_render_cards( $software_items ); // phpcs:ignore ?>
			</div>
		</div>
	</div>

</div>
