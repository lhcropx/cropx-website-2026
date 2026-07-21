<?php
/**
 * Single blog post template — combined design.
 *
 * Sources:
 *   blocks/blog-post.html           — breadcrumb, article header, related posts, newsletter
 *   blog-post-v2-toc-sidebar.html   — body layout (ToC right sidebar), inline share, typography
 *   blog-post-v3-key-insights.html  — Topics footer tags at end of article
 *
 * Page structure:
 *   Reading progress bar (fixed, JS-animated)
 *   Nav
 *   ├── Breadcrumb + Article header (tags, title, lead, meta)
 *   ─── divider ───
 *   ├── Body layout:
 *   │     ├── [LEFT] Article content (the_content())
 *   │     │          ↳ Topics footer (outline-style tags from categories + tags)
 *   │     │          ↳ Inline share row
 *   │     └── [RIGHT] ToC sidebar (sticky, built by blog-single.js)
 *   ─── divider ───
 *   ├── Related posts (3-col cards, same-category)
 *   ├── Newsletter CTA
 *   └── Pre-footer + Footer blocks
 *
 * Assets:
 *   styles/single.css        — enqueued on is_singular('post')
 *   assets/js/blog-single.js — deferred JS: progress bar + ToC builder
 */

get_header();

if ( ! have_posts() ) {
	get_footer();
	return;
}

the_post();

// ── Post data ─────────────────────────────────────────────────────────────────

$post_date    = get_the_date( 'F j, Y' );
$_author_first = get_the_author_meta( 'first_name' );
$_author_last  = get_the_author_meta( 'last_name' );
$_author_full  = trim( "$_author_first $_author_last" );
$author_name   = esc_html( $_author_full ?: get_the_author() );
$reading_time = esc_html( cropx_reading_time( get_the_ID() ) );
$categories   = get_the_category();
$post_tags    = get_the_tags(); // false if no tags

// Blog archive URL — uses the "Posts page" if set, otherwise /blog.
$posts_page = (int) get_option( 'page_for_posts' );
$blog_url   = $posts_page ? get_permalink( $posts_page ) : home_url( '/blog' );

// URL-encoded values for share buttons.
$url_enc   = esc_attr( rawurlencode( get_permalink() ) );
$title_enc = esc_attr( rawurlencode( get_the_title() ) );

// ── SVG icons ─────────────────────────────────────────────────────────────────

$icon_linkedin = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';

$icon_twitter = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.623L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>';

$icon_copy_link = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';

$icon_clock  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';

$icon_arrow = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>';
?>

<!-- Reading progress bar — width animated by blog-single.js -->
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

<!-- ── White post chrome: breadcrumb → header → body ─────────────────────── -->
<div class="bsingle-post-chrome">

<div class="wrap">

	<!-- ── Breadcrumb ──────────────────────────────────────────────────────── -->
	<div class="bsingle-breadcrumb-bar">
		<nav class="bsingle-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'cropx' ); ?>">
			<a href="<?php echo esc_url( home_url( '/resources' ) ); ?>"><?php esc_html_e( 'Resources', 'cropx' ); ?></a>
			<span class="bsingle-breadcrumb-sep" aria-hidden="true">›</span>
			<a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'cropx' ); ?></a>
			<span class="bsingle-breadcrumb-sep" aria-hidden="true">›</span>
			<span class="bsingle-breadcrumb-current"><?php the_title(); ?></span>
		</nav>
	</div>

	<!-- ── Article header ─────────────────────────────────────────────────── -->
	<!--
	   Two layouts depending on featured image:
	   • With image: Hero C split — text left (1fr), image right (1fr)
	   • Without image: standard single-column header
	-->
	<header class="bsingle-article-header <?php echo has_post_thumbnail() ? 'bsingle-article-header--split' : ''; ?>">

		<!-- Text column (always present) -->
		<div class="bsingle-article-header-inner">

			<?php if ( $categories ) : ?>
				<div class="bsingle-article-tags">
					<?php foreach ( $categories as $i => $cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
						   class="bsingle-tag <?php echo $i === 0 ? 'bsingle-tag--dark' : 'bsingle-tag--teal'; ?>">
							<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<h1 class="bsingle-article-title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="bsingle-article-lead"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<div class="bsingle-article-meta">
				<span class="bsingle-meta-item"><?php echo esc_html( $post_date ); ?></span>
				<span class="bsingle-meta-dot" aria-hidden="true"></span>
				<span class="bsingle-meta-item"><?php echo $author_name; ?></span>
				<span class="bsingle-meta-dot" aria-hidden="true"></span>
				<span class="bsingle-meta-item"><?php echo $reading_time; ?></span>
			</div>

		</div>

		<!-- Image column (only when featured image is set) -->
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="bsingle-header-img-col">
				<?php the_post_thumbnail( 'full', array( 'class' => 'bsingle-header-img', 'loading' => 'eager', 'decoding' => 'async' ) ); ?>
			</div>
		<?php endif; ?>

	</header>

</div><!-- /wrap -->

<hr class="bsingle-section-divider">

<!-- ── Article body: content (left) + ToC sidebar (right) ─────────────────── -->
<div class="wrap">
	<div class="bsingle-article-body-section">
		<div class="bsingle-article-body-layout">

			<!-- LEFT: Article content ──────────────────────────────────────── -->
			<article id="bsingle-article-content" class="bsingle-article-content" <?php post_class( '' ); ?>>

				<?php
				/*
				 * Writers compose the body using the block editor.
				 * blog-single.js will scan this element for h2/h3/h4 headings,
				 * inject slug IDs on any heading that lacks one, and build the ToC.
				 *
				 * Typography for standard blocks (p, headings, blockquote, images)
				 * is in styles/single.css, scoped to .bsingle-article-content.
				 *
				 * For callout boxes: add CSS class "callout" to a Group block via
				 * the "Additional CSS class" field in the block's Advanced settings.
				 */
				the_content();
				?>

				<!-- ── Inline share row ────────────────────────────────────────── -->
				<div class="bsingle-share-inline">
					<span class="bsingle-share-inline-label"><?php esc_html_e( 'Share this article', 'cropx' ); ?></span>
					<div class="bsingle-share-inline-icons">

						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $url_enc; ?>"
						   class="bsingle-share-btn-inline"
						   aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'cropx' ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php echo $icon_linkedin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>

						<a href="https://twitter.com/intent/tweet?url=<?php echo $url_enc; ?>&text=<?php echo $title_enc; ?>"
						   class="bsingle-share-btn-inline"
						   aria-label="<?php esc_attr_e( 'Share on X / Twitter', 'cropx' ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php echo $icon_twitter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>

						<button
							id="bsingle-copy-link"
							class="bsingle-share-btn-inline"
							aria-label="<?php esc_attr_e( 'Copy link', 'cropx' ); ?>"
							type="button"
						>
							<?php echo $icon_copy_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="bsingle-copy-label screen-reader-text"><?php esc_html_e( 'Copy link', 'cropx' ); ?></span>
						</button>

					</div>
				</div>

			</article><!-- /bsingle-article-content -->

			<!-- RIGHT: ToC sidebar ─────────────────────────────────────────── -->
			<!-- blog-single.js builds the <ul> inside #bsingle-toc-nav.       -->
			<aside class="bsingle-toc-sidebar" aria-label="<?php esc_attr_e( 'Table of contents', 'cropx' ); ?>">

				<p class="bsingle-toc-label"><?php esc_html_e( 'In this article', 'cropx' ); ?></p>

				<nav id="bsingle-toc-nav" aria-label="<?php esc_attr_e( 'Article sections', 'cropx' ); ?>">
					<?php /* ToC list injected here by blog-single.js */ ?>
				</nav>

				<div class="bsingle-toc-divider" aria-hidden="true"></div>

				<div class="bsingle-toc-read-time">
					<?php echo $icon_clock; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo $reading_time; ?>
				</div>

			</aside>

		</div>
	</div>
</div><!-- /wrap -->

</div><!-- /bsingle-post-chrome -->

<?php
// ── Related posts (same-category, padded to 3) ───────────────────────────────
// Lives in inc/parts/related-posts.php — self-contained, reusable.
get_template_part( 'inc/parts/related-posts' );
?>

<?php
// ── Newsletter CTA ───────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ───────────────────────────────────────────────────────────
// Placed per-template so content/image/buttons can be customised per page.
// The global site footer is rendered by footer.php via get_footer() below.
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
