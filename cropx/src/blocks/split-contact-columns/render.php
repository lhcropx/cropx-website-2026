<?php
/**
 * Split Header + Contact Columns block — front-end render.
 *
 * Layout: two-zone grid.
 *   Left  (1/3) — eyebrow, h2, body paragraph
 *   Right (2/3) — 3-column contact grid (location name, phone, address)
 *
 * Address values are stored as plain text with newlines; nl2br() converts
 * those to <br> tags so each line of a multi-line address renders correctly
 * without the p+p spacing that would apply to separate <p> elements.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow       = $attributes['eyebrow']           ?? '';
$heading       = $attributes['heading']            ?? '';
$body          = $attributes['body']               ?? '';
$bg_variant    = $attributes['backgroundVariant']  ?? 'blue';
$eyebrow_color = $attributes['eyebrowColor']       ?? 'cropx-blue';
$show_eyebrow  = (bool) ( $attributes['showEyebrow'] ?? true );
$show_body     = (bool) ( $attributes['showBody']    ?? true );

$columns = $attributes['columns'] ?? array();

// Validate enum.
if ( ! in_array( $bg_variant, array( 'taupe', 'white', 'blue' ), true ) ) {
	$bg_variant = 'blue';
}

$section_class = 'scc-section scc-section--' . $bg_variant;
$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="scc-inner">

		<!-- Left: section header -->
		<div class="scc-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
			<?php if ( $show_body && $body ) : ?>
				<p class="section-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Right: contact grid -->
		<div class="scc-grid">
			<?php foreach ( $columns as $col ) :
				$col_head = $col['heading'] ?? '';
				$phone    = $col['phone']   ?? '';
				$address  = $col['address'] ?? '';

				// Skip entirely empty items.
				if ( '' === $col_head && '' === $phone && '' === $address ) {
					continue;
				}
			?>
			<div class="scc-item">

				<?php if ( $col_head ) : ?>
					<h3 class="scc-item-heading"><?php echo wp_kses( $col_head, $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $phone ) : ?>
					<div class="scc-contact-row">
						<h4 class="scc-label"><?php esc_html_e( 'Phone:', 'cropx' ); ?></h4>
						<p class="scc-phone"><?php echo esc_html( $phone ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $address ) : ?>
					<div class="scc-contact-row">
						<h4 class="scc-label"><?php esc_html_e( 'Address:', 'cropx' ); ?></h4>
						<p class="scc-address"><?php echo nl2br( esc_html( $address ) ); ?></p>
					</div>
				<?php endif; ?>

			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
