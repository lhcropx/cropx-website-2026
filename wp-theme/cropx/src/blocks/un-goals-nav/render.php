<?php
/**
 * UN Sustainability Goals Nav — front-end render.
 *
 * A 3-column grid of up to 12 items, stored in the `items` array attribute,
 * with an optional intro eyebrow + heading + body above the grid.
 *
 * Intro header (block-level):
 *   showIntroEyebrow/introEyebrow/introEyebrowColor — optional eyebrow above
 *                               the heading. Color options include 'white'
 *                               for use on Deep Blue sections (unlike the
 *                               per-item eyebrow below, no suppression trick
 *                               is needed here).
 *   showHeading/heading/headingAlign — the block's own h2.
 *   introBody                — optional paragraph below the heading.
 *
 * Each item:
 *   imageId/imageUrl/imageAlt — uploaded square image, same framing as the
 *                               UN Goals - 2 Column Text & Photo block's icon
 *                               box (4px radius, no accent bg, object-fit
 *                               cover), sized uniformly by the block-level
 *                               iconSize attribute (--usn-icon-size).
 *   showEyebrow/eyebrow      — per-item eyebrow on/off + text. Color is a
 *                               single block-level itemEyebrowColor attribute
 *                               (default 'deep-blue') applied to every
 *                               item uniformly — 'gray' (--gray-700),
 *                               'cropx-blue', or 'deep-blue'.
 *   heading                  — h3, --fs-h3 (1.375rem).
 *   body                     — optional paragraph, 1.1rem.
 *   ctas                     — up to 3 { label, url } link items, 1.1rem.
 *
 * Items with no heading, no image, and no body are skipped entirely so
 * editors can leave unused slots without them rendering as blank cells.
 *
 * Responsive behavior (3 → 1 column at 900px, heading via the shared
 * .section-heading clamp()) intentionally mirrors cropx/three-column-icons.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$intro_eyebrow       = $attributes['introEyebrow']       ?? '';
$show_intro_eyebrow  = (bool) ( $attributes['showIntroEyebrow'] ?? true );
$intro_eyebrow_color = $attributes['introEyebrowColor']  ?? 'cropx-blue';
$heading              = $attributes['heading']      ?? '';
$show_heading         = (bool) ( $attributes['showHeading']  ?? true );
$heading_align        = $attributes['headingAlign'] ?? 'center';
$intro_body           = $attributes['introBody']    ?? '';
$bg_color             = $attributes['bgColor']      ?? 'white';
$icon_size            = (int) ( $attributes['iconSize'] ?? 72 );
$item_eyebrow_color   = $attributes['itemEyebrowColor']  ?? 'deep-blue';
$items                = $attributes['items']        ?? array();

if ( ! in_array( $bg_color, array( 'white', 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}
if ( ! in_array( $heading_align, array( 'center', 'left' ), true ) ) {
	$heading_align = 'center';
}
if ( $icon_size < 48 || $icon_size > 140 ) {
	$icon_size = 72;
}
if ( ! in_array( $item_eyebrow_color, array( 'deep-blue', 'cropx-blue', 'gray' ), true ) ) {
	$item_eyebrow_color = 'deep-blue';
}
if ( ! in_array( $intro_eyebrow_color, array( 'cropx-blue', 'deep-blue', 'white' ), true ) ) {
	$intro_eyebrow_color = 'cropx-blue';
}

// Maps an itemEyebrowColor attribute value to the actual token variable name —
// 'gray' isn't a token on its own, it means --gray-700. Wrapped in
// function_exists() because render.php runs once per block instance, and
// this file would fatal-error on a second instance on the same page
// otherwise (same pattern as cards/product-grid/product-tabs render.php).
if ( ! function_exists( 'cropx_usn_eyebrow_var' ) ) :
	function cropx_usn_eyebrow_var( $color ) {
		if ( ! $color || 'gray' === $color ) {
			return 'gray-700';
		}
		return $color;
	}
endif; // function_exists cropx_usn_eyebrow_var

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

$section_class = 'usn-section usn-section--bg-' . $bg_color;
$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => $section_class,
	'style' => '--usn-icon-size:' . $icon_size . 'px;',
) );

$header_class = 'usn-header section-header' . ( 'left' === $heading_align ? ' section-header--left' : '' );
$has_header   = ( $show_intro_eyebrow && $intro_eyebrow ) || ( $show_heading && $heading ) || $intro_body;

// On a Deep Blue section, suppress the inline eyebrow color so the CSS
// override (a light, readable tint) can apply without fighting inline
// specificity — otherwise "Deep Blue" eyebrow color (the block default)
// would be invisible on a Deep Blue section background. Computed once
// since itemEyebrowColor is a single block-level setting, not per item.
$eyebrow_style = ( 'deep-blue' !== $bg_color )
	? ' style="color: var(--' . esc_attr( cropx_usn_eyebrow_var( $item_eyebrow_color ) ) . ')"'
	: '';

// The intro eyebrow already offers an explicit 'white' option for Deep Blue
// sections, so — unlike the per-item eyebrow above — no suppression trick
// is needed here; just render the token color directly.
$intro_eyebrow_style = ' style="color: var(--' . esc_attr( $intro_eyebrow_color ) . ')"';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="usn-inner">

		<?php if ( $has_header ) : ?>
		<div class="<?php echo esc_attr( $header_class ); ?>">
			<?php if ( $show_intro_eyebrow && $intro_eyebrow ) : ?>
				<span class="section-eyebrow"<?php echo $intro_eyebrow_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( wp_strip_all_tags( $intro_eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $show_heading && $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
			<?php if ( $intro_body ) : ?>
				<div class="section-body"><?php echo wp_kses_post( $intro_body ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php
		$rendered_any = false;
		ob_start();
		foreach ( $items as $item ) :
			$image_id     = (int) ( $item['imageId'] ?? 0 );
			$image_url    = $item['imageUrl'] ?? '';
			$image_alt    = $item['imageAlt'] ?? '';
			$show_eyebrow = (bool) ( $item['showEyebrow'] ?? true );
			$eyebrow      = $item['eyebrow'] ?? '';
			$item_heading = $item['heading'] ?? '';
			$body         = $item['body'] ?? '';
			$ctas         = is_array( $item['ctas'] ?? null ) ? $item['ctas'] : array();

			// Skip a genuinely empty slot.
			if ( '' === $item_heading && '' === $body && ! $image_id && '' === $image_url ) {
				continue;
			}

			$visible_ctas = array_values( array_filter( $ctas, fn( $c ) => ! empty( $c['label'] ?? '' ) ) );

			// Resolve image markup — prefer attachment ID for auto srcset,
			// same pattern as cropx/un-goals-two-column's icon image.
			$image_markup = '';
			if ( $image_id ) {
				// wp_get_attachment_image() escapes its own 'attr' array
				// internally — passing an already-escaped alt here would
				// double-escape entities (e.g. "&" -> "&amp;amp;").
				$image_markup = wp_get_attachment_image( $image_id, 'thumbnail', false, array(
					'class'   => 'usn-image-img',
					'alt'     => $image_alt,
					'loading' => 'lazy',
				) );
			} elseif ( $image_url ) {
				$image_markup = '<img class="usn-image-img" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $image_alt ) . '" loading="lazy">';
			}

			$rendered_any = true;
			?>
			<div class="usn-item">
				<?php if ( $image_markup ) : ?>
				<div class="usn-image-wrap">
					<div class="usn-image-box" aria-hidden="true">
						<?php echo $image_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<?php endif; ?>
				<div class="usn-content">
					<?php if ( $show_eyebrow && $eyebrow ) : ?>
						<span class="usn-eyebrow"<?php echo $eyebrow_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $item_heading ) : ?>
						<h3 class="usn-item-heading"><?php echo wp_kses( $item_heading, $allowed_inline ); ?></h3>
					<?php endif; ?>
					<?php if ( $body ) : ?>
						<p class="usn-body"><?php echo wp_kses( $body, $allowed_inline ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $visible_ctas ) ) : ?>
						<div class="usn-ctas">
							<?php foreach ( $visible_ctas as $cta ) :
								$cta_label = trim( $cta['label'] ?? '' );
								$cta_url   = $cta['url']   ?? '#';

								// Split off the last word so it can be glued to the arrow inside
								// a white-space:nowrap span — an inline-block element (our <svg>)
								// always carries an implicit line-break opportunity on either
								// side of it in every browser, regardless of a non-breaking space
								// next to it, so nbsp alone isn't enough to stop the arrow from
								// landing on its own line. Wrapping "last word + arrow" in a
								// nowrap span forces the browser to treat them as one atomic
								// unit: if they don't fit at the end of the current line, the
								// whole pair drops to the next line together instead of the
								// arrow going it alone.
								$cta_last_space = strrpos( $cta_label, ' ' );
								if ( false !== $cta_last_space ) {
									$cta_lead = substr( $cta_label, 0, $cta_last_space );
									$cta_tail = substr( $cta_label, $cta_last_space + 1 );
								} else {
									$cta_lead = '';
									$cta_tail = $cta_label;
								}
							?>
								<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="usn-cta"><?php if ( '' !== $cta_lead ) : ?><?php echo esc_html( $cta_lead ); ?> <?php endif; ?><span class="usn-cta-tail"><?php echo esc_html( $cta_tail ); ?>&nbsp;<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		endforeach;
		$items_html = ob_get_clean();

		if ( $rendered_any ) :
			?>
			<div class="usn-grid">
				<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

	</div>
</section>
