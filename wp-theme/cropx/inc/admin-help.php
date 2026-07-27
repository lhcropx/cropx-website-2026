<?php
/**
 * Admin help text — inline descriptions on each post type list screen.
 *
 * Displays a non-dismissible info notice at the top of each content list page
 * to help editors understand where specific content belongs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen ) return;

	// Fire on list pages (base='edit') AND individual editor pages (base='post').
	if ( ! in_array( $screen->base, array( 'edit', 'post' ), true ) ) return;

	$messages = array(

		// Standard Posts
		'post' => array(
			'icon'  => '✍️',
			'title' => 'Posts — blog articles &amp; press releases',
			'body'  => 'Use Posts for <strong>time-stamped content</strong> that lives in a chronological feed. '
				. 'Assign a category to organise: topic-based categories (e.g. Sustainability, Product Updates) for blog articles, '
				. 'and <em>Press Release</em> for news announcements that appear under About → News &amp; Press. '
				. '<strong>If your content is a case study or video testimonial, use Customer Stories instead. '
				. 'If it\'s a downloadable asset like a brochure, datasheet, or white paper, use Resources.</strong>',
		),

		// Customer Stories (cropx_publication)
		'cropx_publication' => array(
			'icon'  => '📄',
			'title' => 'Customer Stories — case studies &amp; video testimonials',
			'body'  => 'Customer Stories are <strong>formal, evergreen content</strong> — proof points people return to over time, '
				. 'not time-sensitive news. Use the <em>Content Type</em> panel on the right to tag each piece '
				. 'as a Case Study or Video Testimonial. Note: this is different from the short-quote '
				. '<em>Testimonials</em> post type used in the testimonial blocks below. '
				. '<strong>If your content is a time-sensitive news item or press release, use Posts instead. '
				. 'If it\'s a downloadable asset like a brochure, datasheet, or white paper, use Resources.</strong>',
		),

		// Resources (cropx_resource)
		'cropx_resource' => array(
			'icon'  => '📥',
			'title' => 'Resources — brochures, datasheets, reports &amp; white papers',
			'body'  => 'Resources are <strong>downloadable assets</strong> — the primary action is downloading a file, '
				. 'not reading content online. Each resource needs: a cover image (portrait ~595×841 px or landscape ~841×595 px), '
				. 'a download URL in the Download panel, and a Resource Type tag. '
				. '<strong>If your content is a long-form article people read online (like a case study or video testimonial), use Customer Stories instead.</strong>',
		),

		// Team Members (cropx_team_member)
		'cropx_team_member' => array(
			'icon'  => '👤',
			'title' => 'Team — About → Team &amp; Investors page',
			'body'  => 'Each team member entry appears on the About → Team &amp; Investors page. '
				. 'Required: <strong>name</strong> (Title field) and <strong>headshot</strong> (Featured Image). '
				. 'Optional: job title (Job Title field) and a short bio of 2–3 sentences (Bio field). '
				. 'Leave any optional fields blank and they simply won\'t appear on the page.',
		),

		// Dealers (cropx_dealer)
		'cropx_dealer' => array(
			'icon'  => '🏪',
			'title' => 'Dealers — dealer directory (Phase 2)',
			'body'  => 'Dealer entries power the dealer directory. '
				. 'Required: <strong>dealer name</strong> (Title field) and <strong>region</strong> (Dealer Details panel). '
				. 'Optional: website, phone, email, address, and a company logo or headshot (Featured Image). '
				. 'If you add an image, use the "Featured image is a…" toggle in the Dealer Details panel to flag whether it\'s a logo or a headshot. '
				. 'Leave any optional fields blank and they simply won\'t appear on the page.',
		),

		// Testimonials (cropx_testimonial)
		'cropx_testimonial' => array(
			'icon'  => '💬',
			'title' => 'Testimonials — data source for testimonial blocks',
			'body'  => 'Testimonials don\'t have public pages — they\'re pulled by the Testimonials Carousel and Testimonial Single blocks. '
				. 'Required: <strong>Quote</strong> (no quotation marks — the design adds them), '
				. '<strong>Title</strong> (person\'s full name), and <strong>Attribution</strong> (role and company, e.g. "VP of Agriculture, Reinke Manufacturing"). '
				. 'Optionally add a headshot or company logo as the Featured Image — '
				. '<strong>must be a perfect square, at least 250×250 px.</strong> '
				. 'Non-square images will be cropped to a square automatically.',
		),
	);

	$post_type = $screen->post_type ?: 'post';

	if ( ! isset( $messages[ $post_type ] ) ) {
		return;
	}

	$msg = $messages[ $post_type ];
	?>
	<div class="notice notice-info" style="border-left-color:#0ca8c0;padding:12px 16px;">
		<p style="margin:0;font-size:13px;line-height:1.6">
			<strong><?php echo $msg['icon'] . ' ' . $msg['title']; ?></strong><br>
			<?php echo $msg['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	</div>
	<?php
} );
