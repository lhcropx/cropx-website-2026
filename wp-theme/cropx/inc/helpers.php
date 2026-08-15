<?php
/**
 * Theme helper functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All valid icon slugs, used by every block's IconPicker to validate the
 * saved `icon` attribute before building the front-end <img src="...">.
 *
 * This reads assets/icons/*.svg directly off disk instead of a hardcoded
 * list, so it can never drift out of sync with the actual icon library —
 * add or remove an SVG in assets/icons/ and every block picks it up on the
 * next page load automatically, no render.php edits required.
 *
 * (Several blocks previously hardcoded their own copy of this list at the
 * point the shared IconPicker component was wired in. A couple of those
 * copies were never updated to the full icon set, so choosing most icons
 * in the editor would silently fall back to the block's default icon on
 * the published page — the attribute saved fine, it just failed this
 * allowlist check. Centralizing it here removes that whole class of bug.)
 *
 * @return string[] Icon slugs (filenames without the .svg extension), sorted.
 */
function cropx_allowed_icon_slugs(): array {
	static $slugs = null;

	if ( null !== $slugs ) {
		return $slugs;
	}

	$slugs = array();
	$files = glob( CROPX_THEME_DIR . 'assets/icons/*.svg' );

	if ( $files ) {
		foreach ( $files as $file ) {
			$slugs[] = basename( $file, '.svg' );
		}
		sort( $slugs );
	}

	return $slugs;
}

/**
 * Convert a same-domain absolute URL to a root-relative path.
 *
 * Use this for every link href output in render.php files. It strips the
 * site's own domain so links work correctly across local → staging → production
 * without needing database search-replace on deployment.
 *
 * Rules:
 *   - Empty string or '#'  → returned as-is
 *   - Same-domain URL      → domain stripped, relative path returned
 *   - External URL         → returned as-is
 *
 * Examples (on cropx.com):
 *   cropx_url('https://cropx.com/enterprise/')   → '/enterprise/'
 *   cropx_url('http://cropx-2026-2.local/about') → '/about'
 *   cropx_url('/already-relative')               → '/already-relative'
 *   cropx_url('https://example.com/page')        → 'https://example.com/page'
 *   cropx_url('#')                               → '#'
 *
 * DO NOT use for image src attributes — only for link hrefs and navigation URLs.
 *
 * @param  string $url The URL to normalise.
 * @return string      Root-relative path for same-domain URLs; original for all others.
 */
/**
 * Estimate reading time for a post.
 *
 * Strips HTML, counts words, and divides by a 200 wpm reading speed.
 * Returns a localised string like "5 min read".
 *
 * @param  int|null $post_id Post ID, or null to use the current global post.
 * @return string            Localised reading-time label.
 */
/**
 * Get the post ID of a synced WordPress pattern (wp_block CPT) by slug.
 *
 * Synced patterns are stored as wp_block posts. Their numeric IDs differ
 * between environments (local / staging / production), so we look them up
 * by slug which is consistent. Used by page pattern PHP files so they stay
 * portable across environments — no hardcoded IDs needed.
 *
 * Usage in a pattern file:
 *   $ref = cropx_get_synced_block_ref( 'segment-navigation' );
 *   if ( $ref ) echo '<!-- wp:block {"ref":' . $ref . '} /-->';
 *
 * @param  string $slug The wp_block post slug (e.g. 'segment-navigation').
 * @return int          The post ID, or 0 if the pattern doesn't exist yet.
 */
function cropx_get_synced_block_ref( string $slug ): int {
	$post = get_page_by_path( $slug, OBJECT, 'wp_block' );
	return $post ? (int) $post->ID : 0;
}

/**
 * Get a card-safe excerpt for a post.
 *
 * Behaves exactly like get_the_excerpt() when the post has a manual excerpt
 * (author-written excerpts are always plain text, so there's nothing to
 * strip there). When there's no manual excerpt, this auto-generates one
 * from the post content with any heading blocks removed FIRST — otherwise
 * WordPress's own excerpt generator just strips HTML tags and runs the
 * heading text straight into the paragraph that follows it, so heading
 * copy ends up bleeding into the excerpt shown on cards (e.g. "Our Mission
 * CropX helps growers..." instead of just "CropX helps growers...").
 *
 * Use this everywhere a post is rendered as a card — the Cards block,
 * Resource Grid, related-posts partials, and every archive/grid template —
 * instead of calling get_the_excerpt() or wp_trim_words( get_the_content() )
 * directly.
 *
 * @param  WP_Post|int|null $post       Post object, ID, or null for the current global post.
 * @param  int              $word_count Words to trim to when auto-generating. Ignored when a
 *                                       manual excerpt exists (get_the_excerpt() never re-trims
 *                                       a manual excerpt either, so this matches that behaviour).
 * @return string           Plain-text excerpt. Same escaping contract as get_the_excerpt() —
 *                           callers still run it through esc_html() themselves.
 */
function cropx_get_card_excerpt( $post = null, int $word_count = 55 ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	if ( has_excerpt( $post ) ) {
		return get_the_excerpt( $post );
	}

	// Strip whole heading blocks — tag *and* inner text — before stripping
	// the remaining tags. This is the step get_the_excerpt() and
	// wp_trim_words() skip, which is why heading text normally leaks into
	// auto-generated excerpts.
	$content = strip_shortcodes( $post->post_content );
	$content = preg_replace( '#<h[1-6][^>]*>.*?</h[1-6]>#is', ' ', $content );

	return wp_trim_words( $content, $word_count );
}

function cropx_reading_time( ?int $post_id = null ): string {
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) round( $word_count / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'cropx' ), $minutes );
}

/**
 * Resolve the fill colour for the hero curved-swoop bottom-right corner.
 *
 * The swoop SVG path must match the background of the section directly below
 * the hero. When the page author places a CropX block next, we read that
 * block's bgColor attribute and return the matching hex value. When native
 * WordPress content follows (paragraphs, headings, etc.) there is no bgColor
 * to read, so we fall back to white — which matches WordPress's default body
 * background for pages that use the base theme styles.
 *
 * A manual override (the swoopFill block attribute) takes priority over
 * auto-detection; pass an empty string to trigger auto-detection.
 *
 * @param  string $block_name  Full block name of the calling hero, e.g. 'cropx/hero-curved'.
 * @param  string $manual      Manual override value ('white', 'taupe', 'deep-blue', …) or ''
 *                             for auto-detect.
 * @return string              Hex colour string, e.g. '#ffffff'.
 */
function cropx_get_swoop_fill( string $block_name, string $manual = '' ): string {
	// Map of bgColor attribute token values → hex colours.
	$color_map = array(
		'white'          => '#ffffff',
		'taupe'          => '#f3f1f1',
		'deep-blue'      => '#243565',
		'dark'           => '#243565',
		'blue'           => '#0CA8C0',
		'cropx-blue'     => '#0CA8C0',
		'gold'           => '#E9C242',
		'terra'          => '#E48C4D',
		'new-leaf'       => '#96C05A',
	);

	// Manual override takes priority.
	if ( $manual !== '' && isset( $color_map[ $manual ] ) ) {
		return $color_map[ $manual ];
	}

	// Auto-detect: parse the current post's block list and find the first
	// non-empty block that immediately follows our hero block.
	$post = get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return '#fbfaf9';
	}

	$blocks = parse_blocks( $post->post_content );
	$found  = false;

	foreach ( $blocks as $block ) {
		if ( $found ) {
			// Skip null/whitespace-only blocks that WordPress inserts between blocks.
			if ( null === $block['blockName'] ) {
				continue;
			}
			$bg = $block['attrs']['bgColor'] ?? '';
			return isset( $color_map[ $bg ] ) ? $color_map[ $bg ] : '#fbfaf9';
		}
		if ( $block['blockName'] === $block_name ) {
			$found = true;
		}
	}

	// Hero is last block, or only native content follows → match the site's
	// body background (#fbfaf9, taupe-50) set in theme.json. This is the correct
	// default for privacy/terms/legal pages with native WordPress content.
	return '#fbfaf9';
}

/**
 * Build the <img>/<picture> markup for one image (device or phone) within one
 * pair of the "Animated Device Hero — Curved Edge" block's `pairs` list, plus
 * the inline style attribute carrying its position/scale custom properties
 * and the `animation` shorthand that drives its slot in the pair slideshow.
 *
 * This lives here (rather than as a function declared inside render.php)
 * because block render.php templates are `include`d fresh on every render —
 * if the block appears more than once on a page, a function declared inside
 * render.php would be declared twice and fatal with "Cannot redeclare
 * function". inc/helpers.php is required exactly once per request, so this
 * is safe to call from any number of block instances.
 *
 * NOTE: deliberately no hardcoded 'style' => 'height: 100%;' passed to
 * wp_get_attachment_image() — see hero-curved-standard's render.php for why
 * that silently breaks the Size (%) control (inline styles beat CSS).
 *
 * @param  string $kind          'device' or 'phone'.
 * @param  int    $id            Attachment ID, or 0 if using $url or the
 *                                 fallback illustration.
 * @param  string $url           External image URL, ignored if $id is set.
 * @param  int    $scale         Size (%), same meaning as hero-curved-standard's
 *                                 deviceScale/phoneScale.
 * @param  int    $offset_x      Horizontal offset in px.
 * @param  int    $offset_y      Vertical offset in px.
 * @param  string $animation_css Full `animation: ...` shorthand value already
 *                                 assembled by render.php (keyframe name,
 *                                 duration, delay — identical for the device
 *                                 and phone in a pair so they move together).
 * @param  string $theme_uri     CROPX_THEME_URI, for the fallback illustration.
 * @return string Markup for one <img> or <picture>, or '' if this is a phone
 *                 slot with nothing selected (phones are optional per pair;
 *                 devices always fall back to the default illustration).
 */
function cropx_hca_render_image( string $kind, int $id, string $url, int $scale, int $offset_x, int $offset_y, string $animation_css, string $theme_uri ): string {
	$is_phone = 'phone' === $kind;

	// A blank phone slot means "no app image for this pair" — skip it
	// entirely rather than falling back to a default, since that's the
	// whole point of making it optional per pair.
	if ( $is_phone && ! $id && ! $url ) {
		return '';
	}

	$item_css_vars = sprintf(
		'--i-scale:%d;--i-x:%dpx;--i-y:%dpx;animation:%s',
		$scale, $offset_x, $offset_y, $animation_css
	);

	$css_class = $is_phone ? 'shca-item--phone' : 'shca-item--device';
	$alt       = $is_phone
		? esc_attr__( 'CropX app on iPhone', 'cropx' )
		: esc_attr__( 'CropX soil sensor', 'cropx' );

	if ( $id ) {
		return wp_get_attachment_image( $id, 'full', false, array(
			'class' => $css_class,
			'alt'   => $alt,
			'style' => esc_attr( $item_css_vars ),
		) );
	}

	if ( $url ) {
		return '<img class="' . esc_attr( $css_class ) . '" style="' . esc_attr( $item_css_vars ) . '" src="' . esc_url( $url ) . '" alt="' . $alt . '" loading="eager">';
	}

	// Device with nothing selected — fall back to the same default
	// illustration hero-curved-standard uses, so a freshly inserted block
	// still looks intentional out of the box. (Phones never reach here —
	// handled by the early return above.)
	$default_base = 'vertex-partial-a';
	$markup  = '<picture>';
	$markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/' . $default_base . '.webp' ) . '">';
	$markup .= '<img class="' . esc_attr( $css_class ) . '" style="' . esc_attr( $item_css_vars ) . '" src="' . esc_url( $theme_uri . 'assets/images/illustrations/' . $default_base . '.png' ) . '" alt="' . $alt . '" loading="eager">';
	$markup .= '</picture>';
	return $markup;
}

/**
 * Country list for the "Country" dropdown on the Zoho-backed form blocks
 * (cropx/zoho-form, cropx/zoho-contact-form).
 *
 * Copied verbatim from the Zoho Forms "Export as HTML & CSS" download for
 * the Country field (Dropdown1) — including a couple of oddities that are
 * Zoho's own, not typos on our end: "Saint BarthÃ©lemy" is mojibake already
 * present in Zoho's exported option value, and a few entries are disputed
 * territories (Kosovo, Palestine, Western Sahara, etc.) that Zoho includes
 * in its own default list. These are left exactly as exported because the
 * <option> value is literally what gets submitted to Zoho — "fixing" the
 * text would send a value that no longer matches whatever Zoho has on its
 * side for that field.
 *
 * If Zoho's own list ever changes, re-export the form and diff the
 * Dropdown1 <option> values against this array rather than hand-editing.
 *
 * @return string[] Country names, in the exact order Zoho exports them.
 */
function cropx_zoho_country_list(): array {
	return array(
		'Åland Islands', 'Afghanistan', 'Akrotiri', 'Albania', 'Algeria', 'American Samoa',
		'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina',
		'Armenia', 'Aruba', 'Ashmore and Cartier Islands', 'Australia', 'Austria', 'Azerbaijan',
		'Bahrain', 'Bangladesh', 'Barbados', 'Bassas Da India', 'Belarus', 'Belgium', 'Belize',
		'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana',
		'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'British Virgin Islands',
		'Brunei', 'Bulgaria', 'Burkina Faso', 'Burma', 'Burundi', 'Cambodia', 'Cameroon',
		'Canada', 'Cape Verde', 'Caribbean Netherlands', 'Cayman Islands',
		'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island',
		'Clipperton Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Cook Islands',
		'Coral Sea Islands', 'Costa Rica', 'Cote D\'Ivoire', 'Croatia', 'Cuba', 'Curaçao',
		'Cyprus', 'Czech Republic', 'Democratic Republic of the Congo', 'Denmark', 'Dhekelia',
		'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador',
		'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Europa Island',
		'Falkland Islands (Islas Malvinas)', 'Faroe Islands', 'Federated States of Micronesia',
		'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia',
		'French Southern and Antarctic Lands', 'Gabon', 'Gaza Strip', 'Georgia', 'Germany',
		'Ghana', 'Gibraltar', 'Glorioso Islands', 'Greece', 'Greenland', 'Grenada',
		'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Guyana',
		'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City)', 'Honduras',
		'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland',
		'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Jan Mayen', 'Japan', 'Jersey', 'Jordan',
		'Juan De Nova Island', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo', 'Kuwait',
		'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya',
		'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar',
		'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique',
		'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Moldova', 'Monaco', 'Mongolia',
		'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru',
		'Navassa Island', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia',
		'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island',
		'North Korea', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau',
		'Palestine', 'Panama', 'Papua New Guinea', 'Paracel Islands', 'Paraguay', 'Peru',
		'Philippines', 'Pitcairn Islands', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar',
		'Republic of the Congo', 'Reunion', 'Romania', 'Russia', 'Rwanda', 'Saint BarthÃ©lemy',
		'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Martin',
		'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino',
		'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
		'Sierra Leone', 'Singapore', 'Sint Maarten', 'Slovakia', 'Slovenia', 'Solomon Islands',
		'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands',
		'South Korea', 'South Sudan', 'Spain', 'Spratly Islands', 'Sri Lanka', 'Sudan',
		'Suriname', 'Svalbard', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan',
		'Tajikistan', 'Tanzania', 'Thailand', 'The Bahamas', 'The Gambia', 'Timor-leste',
		'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tromelin Island', 'Tunisia',
		'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine',
		'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan',
		'Vanuatu', 'Venezuela', 'Vietnam', 'Virgin Islands', 'Wake Island', 'Wallis and Futuna',
		'West Bank', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe',
	);
}

function cropx_url( string $url ): string {
	if ( empty( $url ) || $url === '#' ) {
		return $url;
	}

	// Already relative — nothing to do.
	if ( strncmp( $url, '/', 1 ) === 0 && strncmp( $url, '//', 2 ) !== 0 ) {
		return $url;
	}

	$home = untrailingslashit( home_url() );

	// Case-insensitive check: does this URL start with our home URL?
	if ( strncasecmp( $url, $home, strlen( $home ) ) === 0 ) {
		$path = substr( $url, strlen( $home ) );
		return $path !== '' ? $path : '/';
	}

	return $url;
}

