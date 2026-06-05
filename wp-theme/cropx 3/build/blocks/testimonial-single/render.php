<?php
/**
 * Testimonial (Single) block — front-end render.
 *
 * backgroundVariant "white" (default) — white bg, deep-blue text.
 * backgroundVariant "blue" — deep-blue bg with animated drift pattern ::before,
 *   white quote + name, rgba(255,255,255,0.7) for title/meta.
 *
 * Curly quote marks are added by CSS ::before / ::after on .ts-quote — the
 * stored quote attribute should NOT include quotation mark characters.
 *
 * Attribution meta line: authorTitle and authorCompany are joined with ", "
 * when both are set. If only one is set, that one is shown alone.
 *
 * Photo uses wp_get_attachment_image() for srcset + lazy-loading.
 * Accepts either a profile photo or a logo image — same slot, same CSS.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$background_variant = $attributes['backgroundVariant'] ?? 'white';
$quote              = $attributes['quote']             ?? '';
$author_name        = $attributes['authorName']        ?? '';
$author_title       = $attributes['authorTitle']       ?? '';
$author_company     = $attributes['authorCompany']     ?? '';
$photo_id           = (int) ( $attributes['photoId']   ?? 0 );
$photo_url          = $attributes['photoUrl']           ?? '';
$photo_alt          = $attributes['photoAlt']           ?? '';

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'ts-section ts-section--' . esc_attr( $background_variant ),
) );

// Resolve attachment URL at render time so media-library edits propagate.
if ( $photo_id ) {
	$src = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
	if ( $src ) { $photo_url = $src[0]; }
}

// Photo markup — prefer attachment ID for srcset; fall back to plain img.
$photo_markup = '';
if ( $photo_id ) {
	$photo_markup = wp_get_attachment_image( $photo_id, 'thumbnail', false, array(
		'alt'     => $photo_alt,
		'loading' => 'lazy',
	) );
} elseif ( $photo_url ) {
	$photo_markup = '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy">';
}

// Attribution meta line — join title and company with a comma when both present.
$meta_parts = array_filter( array( $author_title, $author_company ) );
$meta_line  = implode( ', ', $meta_parts );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="ts-inner">

		<?php if ( $quote ) : ?>
			<blockquote>
				<p class="ts-quote"><?php echo wp_kses( $quote, $allowed_inline ); ?></p>
			</blockquote>
		<?php endif; ?>

		<?php if ( $author_name || $photo_markup ) : ?>
			<div class="ts-author">
				<?php if ( $photo_markup ) : ?>
					<div class="ts-icon" aria-hidden="true">
						<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
				<div>
					<?php if ( $author_name ) : ?>
						<p class="ts-name"><?php echo esc_html( $author_name ); ?></p>
					<?php endif; ?>
					<?php if ( $meta_line ) : ?>
						<p class="ts-title"><?php echo esc_html( $meta_line ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
