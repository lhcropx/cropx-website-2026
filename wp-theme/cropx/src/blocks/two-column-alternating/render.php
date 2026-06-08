<?php
/**
 * Two-Column Alternating block — front-end render.
 *
 * Iterates over the rows array attribute. Rows without a photo are skipped
 * so the alternation counter only advances for rendered rows — a blank slot
 * in the middle won't break the left/right pattern.
 *
 * Photos use wp_get_attachment_image() for automatic srcset + lazy-loading.
 * Falls back to a plain <img> when only a URL (no attachment ID) is stored.
 *
 * Photo position alternates by rendered index: even = photo right (default),
 * odd = photo left (.tca-row--photo-left). Alternation driven by PHP counter
 * rather than CSS :nth-child so skipped rows don't break the pattern.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$show_intro      = (bool)   ( $attributes['showIntro']     ?? false );
$show_intro_cta  = (bool)   ( $attributes['showIntroCta']  ?? true  );
$intro_heading   =           $attributes['introHeading']   ?? '';
$intro_body      =           $attributes['introBody']      ?? '';
$intro_cta_label =           $attributes['introCtaLabel']  ?? '';
$intro_cta_url   =           $attributes['introCtaUrl']    ?? '#';
$rows            = (array)   ( $attributes['rows']         ?? [] );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'tca-section' ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$arrow_svg = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
           . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
           . '</svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tca-inner">

		<?php if ( $show_intro && ( $intro_heading || $intro_body || $intro_cta_label ) ) : ?>
			<div class="tca-intro">
				<?php if ( $intro_heading ) : ?>
					<h2 class="tca-intro-heading"><?php echo wp_kses( $intro_heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php if ( $intro_body ) : ?>
					<p class="tca-intro-body"><?php echo wp_kses( $intro_body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $show_intro_cta && $intro_cta_label ) : ?>
					<a href="<?php echo esc_url( cropx_url( $intro_cta_url ) ); ?>" class="tca-intro-cta">
						<?php echo esc_html( $intro_cta_label ); ?>
						<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="tca-rows">
			<?php
			$rendered = 0;
			foreach ( $rows as $row ) :
				$photo_id  = (int)    ( $row['photoId']  ?? 0 );
				$photo_url =           $row['photoUrl']  ?? '';
				$photo_alt =           $row['photoAlt']  ?? '';
				$heading   =           $row['heading']   ?? '';
				$body      =           $row['body']      ?? '';

				// Resolve attachment URL at render time so media-library edits propagate.
				if ( $photo_id ) {
					$src = wp_get_attachment_image_src( $photo_id, 'full' );
					if ( $src ) {
						$photo_url = $src[0];
					}
				}

				// Skip rows with no photo — an empty photo column breaks the layout.
				if ( ! $photo_url ) { continue; }

				$is_photo_left = ( $rendered % 2 === 1 );
				$row_class     = 'tca-row' . ( $is_photo_left ? ' tca-row--photo-left' : '' );
				$rendered++;

				// Photo markup — prefer attachment ID for srcset; fall back to plain img.
				if ( $photo_id ) {
					$photo_markup = wp_get_attachment_image( $photo_id, 'full', false, array(
						'class'   => 'tca-photo',
						'alt'     => $photo_alt,
						'loading' => 'lazy',
					) );
				} else {
					$photo_markup = '<img class="tca-photo" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy">';
				}
			?>
				<div class="<?php echo esc_attr( $row_class ); ?>">
					<div class="tca-content">
						<?php if ( $heading ) : ?>
							<h2 class="tca-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
						<?php endif; ?>
						<?php if ( $body ) : ?>
							<p class="tca-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
						<?php endif; ?>
					</div>

					<div class="tca-photo-col">
						<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
