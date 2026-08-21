<?php
/**
 * Single Results & Research template — Video Testimonial variant.
 *
 * Swapped in for cropx_publication posts tagged "Video Testimonial" by the
 * template_include filter in inc/customer-stories.php (WordPress's template
 * hierarchy has no native "single-{post_type}-{term}.php" pattern, so a
 * custom filter is what makes this per-content-type template possible).
 * single-cropx_publication.php remains the default for case studies and any
 * untagged Results & Research entries.
 *
 * This is a trimmed copy of single-cropx_publication.php with:
 *   - The right-hand sticky "Share" sidebar removed — video testimonials
 *     keep only the share row below the article (no duplicate share links).
 *   - The Key Findings card removed (pub_stat_1..3 fields).
 *   - The Download CTA box removed (pub_download_url field) — video
 *     testimonials don't offer a downloadable PDF.
 *   - The Case Study "At a Glance" fields removed entirely (cs_company,
 *     cs_region, cs_scale, cs_challenge, cs_solution) — those are
 *     case-study-only fields with nothing to show on a video testimonial.
 * Everything else (breadcrumb, header, hero image, article content, bottom
 * share row, related Results & Research entries, pre-footer CTA) mirrors the
 * case study template so the two content types still feel like the same site.
 *
 * Page structure:
 *   Reading progress bar (fixed, JS-animated — same script as blog single)
 *   Nav
 *   .pub-chrome
 *     ├── Breadcrumb
 *     ├── Results & Research header (content-type tag, title, excerpt-as-lead, meta)
 *     ─── divider ───
 *     ├── Hero image (21:9, full max-w) — only when featured image is set
 *     └── Article content (the_content()) — single column, no sidebar
 *                 ↳ Inline share row (LinkedIn, X, copy link)
 *   Related Results & Research (3-col dark cards, same content type)
 *   Pre-footer CTA + Footer
 *
 * Meta fields used (registered in inc/cpts.php):
 *   pub_location — short location string shown in the meta row
 *
 * Assets:
 *   styles/pub-single.css      — enqueued on is_singular('cropx_publication') — shared
 *                                 with the case study template, keyed off post type,
 *                                 not the template file
 *   assets/js/blog-single.js   — reading progress bar + copy-link handler
 */

get_header();

if ( ! have_posts() ) {
	get_footer();
	return;
}

the_post();

// ── Post data ─────────────────────────────────────────────────────────────────

$post_date    = get_the_date( 'F Y' ); // "March 2026" — shorter than blog posts
$reading_time = esc_html( cropx_reading_time( get_the_ID() ) );

// Content-type taxonomy terms — normally just "Video Testimonial", but a post
// could in principle carry more than one term, so this stays generic rather
// than assuming a single fixed term.
$content_types = get_the_terms( get_the_ID(), 'cropx_content_type' );

// Custom meta fields
$pub_location = esc_html( get_post_meta( get_the_ID(), 'pub_location', true ) );

// Customer Results page URL — has_archive is off for cropx_publication (the
// "results" Page owns /results/ now — see page-results.php), so this resolves
// that real Page rather than a CPT archive link.
$pub_archive_url = cropx_get_customer_stories_url();

// URL-encoded values for share buttons
$url_enc   = esc_attr( rawurlencode( get_permalink() ) );
$title_enc = esc_attr( rawurlencode( get_the_title() ) );

// ── SVG icons ─────────────────────────────────────────────────────────────────

$icon_linkedin = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';

$icon_twitter = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.623L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>';

$icon_copy_link = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';

$icon_globe = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>';

$icon_arrow = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>';
?>

<!-- Reading progress bar (animated by blog-single.js) -->
<div
	id="bsingle-reading-progress"
	class="bsingle-reading-progress"
	role="progressbar"
	aria-label="<?php esc_attr_e( 'Reading progress', 'cropx' ); ?>"
	aria-valuenow="0"
	aria-valuemin="0"
	aria-valuemax="100"
></div>

<?php cropx_render_nav( array( 'login_url' => '#' ) ); ?>

<!-- ── Publication chrome ──────────────────────────────────────────────────── -->
<div class="pub-chrome">

<div class="wrap">

	<!-- ── Breadcrumb ───────────────────────────────────────────────────────── -->
	<div class="pub-breadcrumb-bar">
		<nav class="pub-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'cropx' ); ?>">
			<a href="<?php echo esc_url( $pub_archive_url ); ?>"><?php esc_html_e( 'Results & Research', 'cropx' ); ?></a>

			<?php if ( $content_types && ! is_wp_error( $content_types ) ) :
				$primary_type = $content_types[0];
				// Explicit plural map — avoids naive "name + s" which breaks irregular plurals.
				$type_plurals  = array(
					'case study'        => __( 'Case Studies', 'cropx' ),
					'video testimonial' => __( 'Video Testimonials', 'cropx' ),
				);
				$breadcrumb_label = $type_plurals[ strtolower( $primary_type->name ) ] ?? $primary_type->name . 's';
			?>
				<span class="pub-breadcrumb-sep" aria-hidden="true">›</span>
				<a href="<?php echo esc_url( get_term_link( $primary_type ) ); ?>">
					<?php echo esc_html( $breadcrumb_label ); ?>
				</a>
			<?php endif; ?>

			<span class="pub-breadcrumb-sep" aria-hidden="true">›</span>
			<span class="pub-breadcrumb-current"><?php the_title(); ?></span>
		</nav>
	</div>

	<!-- ── Publication header ─────────────────────────────────────────────── -->
	<!--
	  When a featured image is set, we render a two-column split:
	    left  — tags, title, excerpt, meta
	    right — featured image (8:7 aspect, --radius-photo)
	  This mirrors the blog single's --split header pattern exactly.
	  Without a thumbnail the header is single-column, left-aligned.
	-->
	<header class="pub-header<?php echo has_post_thumbnail() ? ' pub-header--split' : ''; ?>">

		<div class="pub-header-inner">

			<!-- Content type tag(s) -->
			<?php if ( $content_types && ! is_wp_error( $content_types ) ) : ?>
				<div class="pub-header-tags">
					<?php foreach ( $content_types as $i => $type ) : ?>
						<a href="<?php echo esc_url( get_term_link( $type ) ); ?>"
						   class="pub-tag <?php echo $i === 0 ? 'pub-tag--dark' : 'pub-tag--outline'; ?>">
							<?php echo esc_html( $type->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Title -->
			<h1 class="pub-title"><?php the_title(); ?></h1>

			<!-- Lead (post excerpt) -->
			<?php if ( has_excerpt() ) : ?>
				<p class="pub-lead"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<!-- Meta row: date · reading time · location -->
			<div class="pub-meta">
				<span class="pub-meta-item"><?php echo esc_html( $post_date ); ?></span>
				<span class="pub-meta-dot" aria-hidden="true"></span>
				<span class="pub-meta-item"><?php echo $reading_time; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php if ( $pub_location ) : ?>
					<span class="pub-meta-dot" aria-hidden="true"></span>
					<span class="pub-meta-item">
						<?php echo $icon_globe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo $pub_location; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				<?php endif; ?>
			</div>

		</div><!-- /pub-header-inner -->

		<?php if ( has_post_thumbnail() ) : ?>
		<div class="pub-header-img-col">
			<?php the_post_thumbnail( 'large', array(
				'loading'  => 'eager',
				'decoding' => 'async',
			) ); ?>
		</div>
		<?php endif; ?>

	</header>

</div><!-- /wrap (breadcrumb + header) -->

<hr class="pub-divider">


<!-- ── Article body ───────────────────────────────────────────────────────── -->
<!--
  Single column — no right-hand sidebar for video testimonials (that's the
  whole point of this template variant: no duplicate share links competing
  with the share row below the article). pub-body-layout--video forces the
  shared .pub-body-layout grid down to one column instead of the case study
  template's [article | sidebar] two-column grid.
-->
<div class="wrap">
<div class="pub-body-section">
<div class="pub-body-layout pub-body-layout--video">

	<!-- Article content -->
	<div class="pub-content-wrap">

		<article id="pub-article-content" class="pub-content" <?php post_class( '' ); ?>>

			<?php
			/*
			 * Video testimonial body composed in the block editor — typically
			 * an embedded video plus a short write-up.
			 *
			 * Typography for block output (p, h2–h4, blockquote, images, tables)
			 * is in styles/pub-single.css, scoped to .pub-content.
			 */
			the_content();
			?>

		</article>

		<!-- Inline share row (below article) -->
		<div class="pub-share-inline">
			<span class="pub-share-inline-label"><?php esc_html_e( 'Share this story', 'cropx' ); ?></span>
			<div class="pub-share-inline-icons">

				<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $url_enc; ?>"
				   class="pub-share-btn-inline"
				   aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'cropx' ); ?>"
				   target="_blank" rel="noopener noreferrer">
					<?php echo $icon_linkedin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>

				<a href="https://twitter.com/intent/tweet?url=<?php echo $url_enc; ?>&text=<?php echo $title_enc; ?>"
				   class="pub-share-btn-inline"
				   aria-label="<?php esc_attr_e( 'Share on X / Twitter', 'cropx' ); ?>"
				   target="_blank" rel="noopener noreferrer">
					<?php echo $icon_twitter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>

				<button
					id="bsingle-copy-link"
					class="pub-share-btn-inline"
					aria-label="<?php esc_attr_e( 'Copy link', 'cropx' ); ?>"
					type="button"
				>
					<?php echo $icon_copy_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Copy link', 'cropx' ); ?></span>
				</button>

			</div>
		</div>

	</div><!-- /pub-content-wrap -->

</div><!-- /pub-body-layout -->
</div><!-- /pub-body-section -->
</div><!-- /wrap (body) -->

</div><!-- /pub-chrome -->


<?php
// ── Related Results & Research ─────────────────────────────────────────────────
// Query: same content type(s) first, excluding current post, up to 3.
// Falls back to any cropx_publication if the same-type pool is too small.

$related_args = array(
	'post_type'      => 'cropx_publication',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'exclude'        => array( get_the_ID() ),
	'orderby'        => 'date',
	'order'          => 'DESC',
);

if ( $content_types && ! is_wp_error( $content_types ) ) {
	$related_args['tax_query'] = array( array(
		'taxonomy' => 'cropx_content_type',
		'field'    => 'term_id',
		'terms'    => wp_list_pluck( $content_types, 'term_id' ),
	) );
}

$related_query = new WP_Query( $related_args );
$related_posts = $related_query->posts;

// If we got fewer than 3, pad with any other Results & Research entries
if ( count( $related_posts ) < 3 ) {
	$exclude_ids = array_merge(
		array( get_the_ID() ),
		wp_list_pluck( $related_posts, 'ID' )
	);
	$pad_query = new WP_Query( array(
		'post_type'      => 'cropx_publication',
		'posts_per_page' => 3 - count( $related_posts ),
		'post_status'    => 'publish',
		'exclude'        => $exclude_ids,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$related_posts = array_merge( $related_posts, $pad_query->posts );
}

// This template is always a video testimonial, so the related-content copy
// and link are fixed rather than branching on content type.
$related_heading = __( 'More Customer Videos', 'cropx' );
$related_sub     = __( 'Hear directly from growers and agronomists using CropX in the field.', 'cropx' );
$related_cta     = __( 'View All Videos', 'cropx' );
$related_url     = ( $content_types && ! is_wp_error( $content_types ) ) ? get_term_link( $content_types[0] ) : $pub_archive_url;

if ( $related_posts ) :
?>
<hr style="border:none;border-top:1px solid var(--gray-200)">
<section class="pub-related" aria-label="<?php esc_attr_e( 'Related Results & Research', 'cropx' ); ?>">
	<div class="wrap">

		<div class="pub-related-head">
			<div>
				<h2 class="pub-related-title"><?php echo esc_html( $related_heading ); ?></h2>
			</div>
			<?php if ( $related_url && ! is_wp_error( $related_url ) ) : ?>
				<a href="<?php echo esc_url( $related_url ); ?>" class="pub-view-all">
					<?php echo esc_html( $related_cta ); ?>
					<?php echo $icon_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>

		<?php
		// Arrow for card CTAs — matches cards/render.php exactly.
		$crd_arrow = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		           . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		           . '</svg>';
		?>
		<div class="crd-grid">
			<?php foreach ( $related_posts as $related_post ) :

				$related_types    = get_the_terms( $related_post->ID, 'cropx_content_type' );
				$related_type_obj = ( $related_types && ! is_wp_error( $related_types ) ) ? $related_types[0] : null;
				$related_type     = $related_type_obj ? $related_type_obj->name : '';
				$related_type_url = $related_type_obj ? get_term_link( $related_type_obj ) : '#';
				$related_thumb_id = (int) get_post_thumbnail_id( $related_post->ID );
				$related_thumb    = $related_thumb_id
					? wp_get_attachment_image( $related_thumb_id, 'large', false, array(
						'class'   => 'crd-card-img',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
					) )
					: '';
				$related_url_p = get_permalink( $related_post );
				$related_exc   = cropx_get_card_excerpt( $related_post );
			?>
			<article class="crd-card crd-card--dark">

				<?php if ( $related_thumb ) : ?>
					<div class="crd-card-img-wrap">
						<a href="<?php echo esc_url( $related_url_p ); ?>" class="crd-card-img-link" tabindex="-1" aria-hidden="true">
							<?php echo $related_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="crd-card-body">

					<?php if ( $related_type ) : ?>
						<div class="crd-meta">
							<a href="<?php echo esc_url( is_wp_error( $related_type_url ) ? '#' : $related_type_url ); ?>" class="crd-tag crd-tag--white">
								<?php echo esc_html( $related_type ); ?>
							</a>
						</div>
					<?php endif; ?>

					<h3 class="crd-title crd-title--lines-2">
						<a href="<?php echo esc_url( $related_url_p ); ?>" class="crd-title-link">
							<?php echo esc_html( get_the_title( $related_post ) ); ?>
						</a>
					</h3>

					<?php if ( $related_exc ) : ?>
						<p class="crd-excerpt crd-excerpt--lines-3"><?php echo esc_html( $related_exc ); ?></p>
					<?php endif; ?>

					<a href="<?php echo esc_url( $related_url_p ); ?>" class="crd-cta">
						<?php esc_html_e( 'Read more', 'cropx' ); ?>
						<?php echo $crd_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>

				</div>

			</article>
			<?php endforeach; ?>
		</div>

	</div><!-- /wrap (related) -->
</section>
<?php endif; // related_posts ?>


<?php
// ── Newsletter CTA ────────────────────────────────────────────────────────────
// White, matching the rest of Results & Research (was missing entirely on
// single Customer Story posts until this fix — Aug 2026).
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ────────────────────────────────────────────────────────────
// Lauren's "Results Cat. Pg. Demo Form + CTA" pattern, replacing the plain
// default block (Aug 2026) — same pattern as single-cropx_publication.php and
// the category/tag archive templates for Results & Research. Looked up by
// slug so this stays correct across environments; falls back to the plain
// default block if the pattern hasn't been created yet on this environment,
// so nothing goes missing.
$_vid_pfc_ref = cropx_get_synced_block_ref( 'results-cat-pg-demo-form-cta' );
echo do_blocks( $_vid_pfc_ref
	? '<!-- wp:block {"ref":' . $_vid_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
