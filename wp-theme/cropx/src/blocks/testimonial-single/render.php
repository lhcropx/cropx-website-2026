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
 *
 * Content source (same three-mode system as Testimonial Carousel):
 *   manual — quote data stored directly in block attributes (fields below).
 *   pick   — a single specific cropx_testimonial CPT post chosen by the editor.
 *   auto   — WP_Query for the newest cropx_testimonial post, optionally
 *            filtered by a cropx_testimonial_category term.
 * For pick/auto, authorName is read from the person_name post meta field, NOT
 * post_title — Quote titles now follow the "[Business Name] - [Product]"
 * convention and no longer hold the quoted person's name. authorCompany is
 * left blank in those modes since company now lives in the Business Name tag,
 * not a dedicated field on the CPT.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$background_variant = $attributes['backgroundVariant'] ?? 'taupe';
$content_source      = $attributes['contentSource']     ?? 'manual';
$show_photo          = (bool) ( $attributes['showPhoto'] ?? true );

// ── Resolve quote/author/photo fields based on content source ──────────────
if ( 'manual' === $content_source ) {

	$quote          = $attributes['quote']         ?? '';
	$author_name    = $attributes['authorName']    ?? '';
	$author_title   = $attributes['authorTitle']   ?? '';
	$author_company = $attributes['authorCompany'] ?? '';
	$photo_id       = (int) ( $attributes['photoId'] ?? 0 );
	$photo_url      = $attributes['photoUrl']        ?? '';
	$photo_alt      = $attributes['photoAlt']        ?? '';

} else {

	$quote          = '';
	$author_name    = '';
	$author_title   = '';
	$author_company = '';
	$photo_id       = 0;
	$photo_url      = '';
	$photo_alt      = '';

	$resolved_post = null;

	if ( 'pick' === $content_source ) {

		$testimonial_id = (int) ( $attributes['testimonialId'] ?? 0 );
		if ( $testimonial_id ) {
			$candidate = get_post( $testimonial_id );
			if ( $candidate && 'cropx_testimonial' === $candidate->post_type && 'publish' === $candidate->post_status ) {
				$resolved_post = $candidate;
			}
		}
	} elseif ( 'auto' === $content_source ) {

		$category   = trim( $attributes['testimonialCategory'] ?? '' );
		$query_args = array(
			'post_type'              => 'cropx_testimonial',
			'posts_per_page'         => 1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => (bool) $category,
		);
		if ( $category ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'cropx_testimonial_category',
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}
		$q = new WP_Query( $query_args );
		if ( $q->posts ) {
			$resolved_post = $q->posts[0];
		}
		wp_reset_postdata();
	}

	if ( $resolved_post ) {
		$quote        = get_post_meta( $resolved_post->ID, 'quote_text',   true ) ?: '';
		$author_name  = get_post_meta( $resolved_post->ID, 'person_name',  true ) ?: '';
		$author_title = get_post_meta( $resolved_post->ID, 'attribution',  true ) ?: '';
		$photo_id     = (int) get_post_thumbnail_id( $resolved_post->ID );
		$photo_alt    = $author_name ?: $resolved_post->post_title;
	}
}

$_ts_attrs = array( 'class' => 'ts-section ts-section--' . esc_attr( $background_variant ) );
if ( in_array( $background_variant, array( 'taupe', 'white' ), true ) ) {
	$_ts_attrs['data-section-bg'] = $background_variant;
}
// Deep-blue drift pattern: inject the asset's real URL via a CSS custom
// property instead of a relative url() in style.css — see the comment there.
if ( 'blue' === $background_variant ) {
	$_ts_attrs['style'] = '--ts-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}
$wrapper_attrs = get_block_wrapper_attributes( $_ts_attrs );

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

		<?php
		// Center the attribution block whenever no photo actually renders —
		// not just when the "Show Photo" toggle is off. A quote with the
		// toggle left on but no photo uploaded (or no featured image in
		// Auto mode) still needs centering; checking $show_photo alone
		// missed that case and left the attribution stuck at its
		// photo-adjacent left alignment.
		$photo_will_show = $show_photo && $photo_markup;
		?>
		<?php if ( $author_name || $photo_markup ) : ?>
			<div class="ts-author<?php echo ! $photo_will_show ? ' ts-author--no-photo' : ''; ?>">
				<?php if ( $photo_will_show ) : ?>
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
