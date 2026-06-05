<?php
/**
 * Cards block — front-end render.
 *
 * cardVariant "white" (default) — white card, deep-blue tags (crd-tag--dark).
 * cardVariant "dark" — deep-blue card, white tags (crd-tag--white).
 *
 * Whole-card clickable via three separate anchors (photo + title + CTA), all
 * pointing to ctaUrl. The photo link is aria-hidden + tabindex="-1" so screen
 * readers only encounter the title link and CTA link, not a duplicate.
 *
 * Photos use wp_get_attachment_image() for srcset + lazy-loading with an
 * explicit sizes hint matching the 3-col → 2-col → 1-col responsive grid.
 *
 * Cards without a photo AND without a title are skipped.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$card_variant = $attributes['cardVariant'] ?? 'white';
$show_header  = (bool) ( $attributes['showHeader'] ?? true );
$eyebrow       = $attributes['eyebrow']      ?? '';
$eyebrow_color = $attributes['eyebrowColor'] ?? 'cropx-blue';
$heading      = $attributes['heading'] ?? '';
$cards        = (array) ( $attributes['cards'] ?? [] );

$is_dark    = ( $card_variant === 'dark' );
$tag_class  = $is_dark ? 'crd-tag crd-tag--white' : 'crd-tag crd-tag--dark';
$date_class = $is_dark ? 'crd-date crd-date--dark' : 'crd-date';

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'crd-section' ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

$arrow_svg = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
           . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
           . '</svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="crd-inner">

		<?php if ( $show_header && ( $eyebrow || $heading ) ) : ?>
			<div class="crd-header">
				<?php if ( $eyebrow ) : ?>
					<span class="crd-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="crd-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="crd-grid">
			<?php foreach ( $cards as $card ) :
				$photo_id  = (int)  ( $card['photoId']  ?? 0 );
				$photo_url =         $card['photoUrl']  ?? '';
				$photo_alt =         $card['photoAlt']  ?? '';
				$tag       =         $card['tag']       ?? '';
				$tag_url   =         $card['tagUrl']    ?? '#';
				$date      =         $card['date']      ?? '';
				$title     =         $card['title']     ?? '';
				$excerpt   =         $card['excerpt']   ?? '';
				$cta_label =         $card['ctaLabel']  ?? 'Read more';
				$cta_url   =         $card['ctaUrl']    ?? '#';

				// Resolve attachment URL at render time so media-library edits propagate.
				if ( $photo_id ) {
					$src = wp_get_attachment_image_src( $photo_id, 'full' );
					if ( $src ) { $photo_url = $src[0]; }
				}

				if ( ! $photo_url && ! $title ) { continue; }

				// Photo markup — prefer attachment ID for srcset; fall back to plain img.
				$photo_markup = '';
				if ( $photo_id ) {
					$photo_markup = wp_get_attachment_image( $photo_id, 'large', false, array(
						'class'   => 'crd-card-img',
						'alt'     => $photo_alt,
						'loading' => 'lazy',
						'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
					) );
				} elseif ( $photo_url ) {
					$photo_markup = '<img class="crd-card-img" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy">';
				}
			?>
				<article class="crd-card<?php echo $is_dark ? ' crd-card--dark' : ''; ?>">

					<?php if ( $photo_markup ) : ?>
						<div class="crd-card-img-wrap">
							<a href="<?php echo esc_url( $cta_url ); ?>" class="crd-card-img-link" tabindex="-1" aria-hidden="true">
								<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="crd-card-body">

						<?php if ( $tag || $date ) : ?>
							<div class="crd-meta">
								<?php if ( $tag ) : ?>
									<a href="<?php echo esc_url( $tag_url ); ?>" class="<?php echo esc_attr( $tag_class ); ?>">
										<?php echo esc_html( $tag ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $date ) : ?>
									<span class="<?php echo esc_attr( $date_class ); ?>">
										<?php echo esc_html( $date ); ?>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $title ) : ?>
							<h3 class="crd-title">
								<a href="<?php echo esc_url( $cta_url ); ?>" class="crd-title-link">
									<?php echo wp_kses( $title, $allowed_inline ); ?>
								</a>
							</h3>
						<?php endif; ?>

						<?php if ( $excerpt ) : ?>
							<p class="crd-excerpt"><?php echo wp_kses( $excerpt, $allowed_inline ); ?></p>
						<?php endif; ?>

						<?php if ( $cta_label ) : ?>
							<a href="<?php echo esc_url( $cta_url ); ?>" class="crd-cta">
								<?php echo esc_html( $cta_label ); ?>
								<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>

					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
