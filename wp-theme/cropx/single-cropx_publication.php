<?php
/**
 * Single customer story template — cropx_publication CPT.
 *
 * Covers case studies and video testimonials (White Paper moved to the
 * cropx_resource CPT — see inc/cpts.php).
 * Mirrors the layout from blocks/publication-single.html.
 *
 * Page structure:
 *   Reading progress bar (fixed, JS-animated — same script as blog single)
 *   Nav
 *   .pub-chrome
 *     ├── Breadcrumb
 *     ├── Customer story header (content-type tag, title, excerpt-as-lead, meta)
 *     ─── divider ───
 *     ├── Hero image (21:9, full max-w) — only when featured image is set
 *     ├── Key Findings card — only when at least one stat is populated
 *     └── Body layout:
 *           ├── [LEFT] Sticky share sidebar (LinkedIn, X, copy link)
 *           └── [RIGHT] Article content (the_content())
 *                       ↳ Download CTA box — only when pub_download_url is set
 *                       ↳ Inline share row
 *   Related customer stories (3-col dark cards, same content type)
 *   Pre-footer CTA + Footer
 *
 * Meta fields used (registered in inc/cpts.php):
 *   pub_location       — short location string shown in the meta row
 *   pub_download_url   — URL to downloadable PDF; shows Download CTA when set
 *   pub_stat_1_value / pub_stat_1_label  — key findings stat 1
 *   pub_stat_2_value / pub_stat_2_label  — key findings stat 2
 *   pub_stat_3_value / pub_stat_3_label  — key findings stat 3
 *   cs_company         — (case studies) company / operation name
 *   cs_region          — (case studies) region / country
 *   cs_scale           — (case studies) farm/operation scale
 *   cs_challenge       — (case studies) main challenge description
 *   cs_solution        — (case studies) solution deployed description
 *
 * Assets:
 *   styles/pub-single.css      — enqueued on is_singular('cropx_publication')
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

// Content-type taxonomy terms (Case Study / Video Testimonial)
$content_types = get_the_terms( get_the_ID(), 'cropx_content_type' );

// Convenience booleans — drives copy differences between the two types.
// has_term() is safe to call inside the loop after the_post().
$is_case_study         = has_term( 'case-study',        'cropx_content_type' );
$is_video_testimonial  = has_term( 'video-testimonial', 'cropx_content_type' );

// Custom meta fields
$pub_location     = esc_html( get_post_meta( get_the_ID(), 'pub_location',     true ) );
$pub_download_url = esc_url(  get_post_meta( get_the_ID(), 'pub_download_url', true ) );

// Key findings stats — collect non-empty pairs
$stats = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$val = sanitize_text_field( get_post_meta( get_the_ID(), "pub_stat_{$i}_value", true ) );
	$lbl = sanitize_text_field( get_post_meta( get_the_ID(), "pub_stat_{$i}_label", true ) );
	if ( $val ) {
		$stats[] = array( 'value' => $val, 'label' => $lbl );
	}
}
$has_findings = ! empty( $stats );

// Case study sidebar meta — populated from the "Case Study Details" meta box.
// $has_cs_details drives whether the right column shows the details card or
// the share buttons (video testimonials / untagged customer stories keep share buttons).
$cs_company   = esc_html( get_post_meta( get_the_ID(), 'cs_company',   true ) );
$cs_region    = esc_html( get_post_meta( get_the_ID(), 'cs_region',    true ) );
$cs_challenge = esc_html( get_post_meta( get_the_ID(), 'cs_challenge', true ) );
$cs_solution  = esc_html( get_post_meta( get_the_ID(), 'cs_solution',  true ) );
$cs_scale     = esc_html( get_post_meta( get_the_ID(), 'cs_scale',     true ) );
$has_cs_details = $is_case_study && ( $cs_company || $cs_region || $cs_challenge || $cs_solution || $cs_scale );

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

$icon_download = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';

$icon_share = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';

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
			<a href="<?php echo esc_url( $pub_archive_url ); ?>"><?php esc_html_e( 'Customer Results', 'cropx' ); ?></a>

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
  Two-column grid: [article content 1fr] [sidebar]
  Content column is on the LEFT, sidebar on the RIGHT — mirrors the
  blog single's content + ToC layout exactly.

  Right column varies by type:
    Case study with CS details filled in     → pub-cs-details "At a Glance" card (17rem)
    Video testimonial / untagged             → share buttons sidebar (13rem default)

  The Key Findings card lives INSIDE pub-content-wrap (the 1fr column), not in
  a separate section, so its right edge is guaranteed to match the article and
  HR below it without any width calculations.
-->
<div class="wrap">
<div class="pub-body-section">
<div class="pub-body-layout<?php echo $has_cs_details ? ' pub-body-layout--cs' : ''; ?>">

	<!-- Article content (LEFT column) -->
	<div class="pub-content-wrap">

		<!-- Key Findings card — only rendered when at least one stat is populated.
		     Placed first so it sits directly below the header, above the article. -->
		<?php if ( $has_findings ) : ?>
		<div class="pub-findings-wrap">
			<div class="pub-findings" aria-label="<?php esc_attr_e( 'Key findings', 'cropx' ); ?>">
				<div class="pub-findings-grid">
					<?php foreach ( array_slice( $stats, 0, 2 ) as $stat ) : ?>
						<div class="pub-finding-item">
							<span class="pub-finding-stat"><?php echo esc_html( $stat['value'] ); ?></span>
							<?php if ( $stat['label'] ) : ?>
								<span class="pub-finding-label"><?php echo esc_html( $stat['label'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<article id="pub-article-content" class="pub-content" <?php post_class( '' ); ?>>

			<?php
			/*
			 * Case study body composed in the block editor.
			 * Typical structure: lead → challenge section → solution section
			 * → results section → methodology section → (optional blockquote)
			 *
			 * Typography for block output (p, h2–h4, blockquote, images, tables)
			 * is in styles/pub-single.css, scoped to .pub-content.
			 */
			the_content();
			?>

			<!-- Download CTA — only when pub_download_url is set.
			     Heading, body, and share-button text vary by content type. -->
			<?php if ( $pub_download_url ) :
				if ( $is_video_testimonial ) {
					$dl_heading    = __( 'Download the Video Transcript', 'cropx' );
					$dl_body       = __( 'Get the full transcript and supporting details in a printer-ready PDF.', 'cropx' );
					$dl_share_text = __( 'Share this video', 'cropx' );
				} else {
					// Default to case-study language (covers case studies + any untagged customer stories)
					$dl_heading    = __( 'Download the Full Case Study', 'cropx' );
					$dl_body       = __( 'Get the complete methodology, sensor configuration details, and data charts in a printer-ready PDF.', 'cropx' );
					$dl_share_text = __( 'Share this study', 'cropx' );
				}
			?>
			<div class="pub-download-cta">
				<h2 class="pub-download-heading"><?php echo esc_html( $dl_heading ); ?></h2>
				<p class="pub-download-body"><?php echo esc_html( $dl_body ); ?></p>
				<div class="pub-download-actions">
					<a href="<?php echo $pub_download_url; ?>" class="pub-btn-primary" target="_blank" rel="noopener noreferrer">
						<?php echo $icon_download; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
					</a>
					<button id="pub-copy-link-inline" class="pub-btn-link" type="button">
						<?php echo esc_html( $dl_share_text ); ?>
						<?php echo $icon_share; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>
			<?php endif; // pub_download_url ?>

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

	<!-- Right sidebar (sticky, hidden below 1100px via pub-share-sidebar class).
	     Case studies with details  → "At a Glance" details card.
	     Video testimonials / untagged → share buttons. -->
	<?php if ( $has_cs_details ) : ?>
	<aside class="pub-share-sidebar" aria-label="<?php esc_attr_e( 'Case study details', 'cropx' ); ?>">
		<div class="pub-cs-details">
			<p class="pub-cs-details-eyebrow"><?php esc_html_e( 'At a Glance', 'cropx' ); ?></p>

			<?php if ( $cs_company ) : ?>
			<div class="pub-cs-field">
				<span class="pub-cs-field-label"><?php esc_html_e( 'Company', 'cropx' ); ?></span>
				<span class="pub-cs-field-value"><?php echo $cs_company; ?></span>
			</div>
			<?php endif; ?>

			<?php if ( $cs_region ) : ?>
			<div class="pub-cs-field">
				<span class="pub-cs-field-label"><?php esc_html_e( 'Region', 'cropx' ); ?></span>
				<span class="pub-cs-field-value"><?php echo $cs_region; ?></span>
			</div>
			<?php endif; ?>

			<?php if ( $cs_scale ) : ?>
			<div class="pub-cs-field">
				<span class="pub-cs-field-label"><?php esc_html_e( 'Scale', 'cropx' ); ?></span>
				<span class="pub-cs-field-value"><?php echo $cs_scale; ?></span>
			</div>
			<?php endif; ?>

			<?php if ( $cs_challenge ) : ?>
			<div class="pub-cs-field pub-cs-field--long">
				<span class="pub-cs-field-label"><?php esc_html_e( 'Challenge', 'cropx' ); ?></span>
				<span class="pub-cs-field-value"><?php echo nl2br( $cs_challenge ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( $cs_solution ) : ?>
			<div class="pub-cs-field pub-cs-field--long">
				<span class="pub-cs-field-label"><?php esc_html_e( 'Solution', 'cropx' ); ?></span>
				<span class="pub-cs-field-value"><?php echo nl2br( $cs_solution ); ?></span>
			</div>
			<?php endif; ?>

		</div>
	</aside>
	<?php else : // Video testimonial / untagged — share buttons in sidebar ?>
	<aside class="pub-share-sidebar" aria-label="<?php esc_attr_e( 'Share this story', 'cropx' ); ?>">
		<span class="pub-share-label"><?php esc_html_e( 'Share', 'cropx' ); ?></span>
		<div class="pub-share-icons">

			<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $url_enc; ?>"
			   class="pub-share-btn"
			   aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'cropx' ); ?>"
			   target="_blank" rel="noopener noreferrer">
				<?php echo $icon_linkedin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<a href="https://twitter.com/intent/tweet?url=<?php echo $url_enc; ?>&text=<?php echo $title_enc; ?>"
			   class="pub-share-btn"
			   aria-label="<?php esc_attr_e( 'Share on X / Twitter', 'cropx' ); ?>"
			   target="_blank" rel="noopener noreferrer">
				<?php echo $icon_twitter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<button
				id="pub-copy-link"
				class="pub-share-btn"
				aria-label="<?php esc_attr_e( 'Copy link', 'cropx' ); ?>"
				type="button"
			>
				<?php echo $icon_copy_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

		</div>
	</aside>
	<?php endif; // sidebar type ?>

</div><!-- /pub-body-layout -->
</div><!-- /pub-body-section -->
</div><!-- /wrap (body) -->

</div><!-- /pub-chrome -->


<?php
// ── Related customer stories ──────────────────────────────────────────────────
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

// If we got fewer than 3, pad with any other customer stories
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

// Determine the archive heading based on primary content type
$related_heading = __( 'More Customer Stories', 'cropx' );
$related_sub     = __( 'Field-validated results and real customer outcomes from CropX.', 'cropx' );
$related_cta     = __( 'View All', 'cropx' );
$related_url     = $pub_archive_url;

if ( $content_types && ! is_wp_error( $content_types ) ) {
	$primary_name = $content_types[0]->name;
	if ( strtolower( $primary_name ) === 'case study' ) {
		$related_heading = __( 'More Case Studies', 'cropx' );
		$related_sub     = __( 'Field-validated results from CropX deployments across crops and regions.', 'cropx' );
		$related_cta     = __( 'View All Case Studies', 'cropx' );
		$related_url     = get_term_link( $content_types[0] );
	} elseif ( strtolower( $primary_name ) === 'video testimonial' ) {
		$related_heading = __( 'More Customer Videos', 'cropx' );
		$related_sub     = __( 'Hear directly from growers and agronomists using CropX in the field.', 'cropx' );
		$related_cta     = __( 'View All Videos', 'cropx' );
		$related_url     = get_term_link( $content_types[0] );
	}
}

if ( $related_posts ) :
?>
<hr style="border:none;border-top:1px solid var(--gray-200)">
<section class="pub-related" aria-label="<?php esc_attr_e( 'Related customer stories', 'cropx' ); ?>">
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
				$related_exc   = get_the_excerpt( $related_post );
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
// ── Pre-footer CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
