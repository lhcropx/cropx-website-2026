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
 * Maps a retired icon slug to the icon that replaced it.
 *
 * When an icon SVG is deleted from assets/icons/ because it turned out to be
 * a duplicate of another icon (e.g. "speed-2" duplicating "speed"), any
 * already-published block still has the OLD slug saved in its attributes.
 * Without this, cropx_allowed_icon_slugs() would reject that now-missing
 * slug and every block's render.php would silently fall back to its own
 * hardcoded default icon — a visible, confusing regression on pages nobody
 * touched. Resolving the alias here means the front end keeps showing the
 * (correct, still-present) icon with zero editor action required.
 *
 * Every block's render.php should call this on the raw `icon` attribute
 * value BEFORE checking it against cropx_allowed_icon_slugs().
 * src/shared/IconPicker.js keeps a matching ICON_SLUG_ALIASES map so the
 * editor canvas preview and "selected" swatch stay in sync with this.
 *
 * @param string $slug Icon slug as stored in the block attribute.
 * @return string The resolved slug — unchanged if it isn't a retired alias.
 */
function cropx_resolve_icon_slug( string $slug ): string {
	$aliases = array(
		'fields-2' => 'fields',
	);

	return $aliases[ $slug ] ?? $slug;
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
 * [cropx_year] shortcode — outputs the current 4-digit year.
 *
 * Added for the footer's legal/copyright line (Aug 2026 footer rebuild,
 * task #305): the old monolithic cropx/footer block computed the copyright
 * year in PHP ($copyright_year ?: date('Y')). The new footer is a Synced
 * Pattern made of plain core/paragraph blocks, so there's no PHP to do that
 * automatically — this shortcode is the one small dynamic piece kept, so
 * Lauren never has to manually bump the year on the footer copyright line.
 *
 * Usage inside any block's text content: © [cropx_year] CropX Technologies Ltd.
 *
 * Note: shortcodes only expand automatically inside the_content() (WordPress
 * hooks do_shortcode() onto that filter, not onto block rendering generally).
 * Anywhere block content is rendered directly via do_blocks() outside the
 * main loop — the footer pattern included — wrap the output in do_shortcode()
 * as well, e.g.: echo do_shortcode( do_blocks( '<!-- wp:block ... /-->' ) );
 */
add_shortcode( 'cropx_year', function () {
	return esc_html( date( 'Y' ) );
} );

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

	// Strip all remaining HTML tags BEFORE removing bare URLs (Sep 2026 fix
	// for a regression this same date — see below). A real hyperlink's
	// href/src value is discarded along with its tag here, leaving only the
	// tag's visible text (e.g. "by <a href=...>Xtremeag.farm</a>" correctly
	// becomes "by Xtremeag.farm"). Only a genuinely bare/pasted URL — one
	// that was never wrapped in a tag, e.g. a core/embed block that stores
	// its URL as plain text between wrapper markup — survives tag-stripping
	// as visible plain text, and that's what the regex below is actually
	// meant to catch.
	//
	// The previous version ran the URL-strip BEFORE stripping tags, matching
	// greedily on \S+ (non-whitespace). On a post whose body contains a real
	// <a href="https://...">Link Text</a>, that regex swallowed the closing
	// quote, ">", the visible link text, and the closing tags too (none of
	// those characters are whitespace) — leaving a dangling, unclosed
	// `<a href="` behind. PHP's tag-stripper is quote-aware (it treats
	// everything as "still inside a tag" until it finds a matching closing
	// quote), so that dangling open quote caused it to silently swallow
	// every paragraph after it, all the way until the next stray `"`
	// character anywhere later in the content — which is how some posts'
	// excerpts collapsed to a single leading word like "by".
	$content = wp_strip_all_tags( $content );
	$content = preg_replace( '#https?://\S+#i', ' ', $content );

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
 * Resolve the byline author name for a Post or Results & Research
 * (cropx_publication) entry.
 *
 * Replaces the WordPress user account name with a fixed "CropX Team"
 * default (Lauren, Sep 2026) — visitors shouldn't see which internal
 * WordPress account happened to click Publish. An editor can override this
 * per-post via the "Byline" panel in the Document sidebar (registered in
 * src/admin/editor-panels.js), which writes the three meta keys read below.
 * cropx_author_mode is the single source of truth for which override (if
 * any) applies — see the register_post_meta() calls in inc/cpts.php for the
 * full reasoning. Both override paths render as plain text, never a link
 * (even the Team-member path, which could technically link out to that
 * person's Team profile) — Lauren, Sep 2026 — so the byline always reads
 * identically regardless of which mode produced it.
 */
function cropx_get_byline_author_name( int $post_id ): string {
	$mode = get_post_meta( $post_id, 'cropx_author_mode', true ) ?: 'default';

	if ( 'custom' === $mode ) {
		$name = trim( (string) get_post_meta( $post_id, 'cropx_author_name', true ) );
		if ( '' !== $name ) {
			return $name;
		}
	}

	if ( 'team' === $mode ) {
		$team_id = (int) get_post_meta( $post_id, 'cropx_author_team_id', true );
		if ( $team_id && 'cropx_team_member' === get_post_type( $team_id ) && 'publish' === get_post_status( $team_id ) ) {
			return cropx_get_team_member_name( $team_id );
		}
	}

	return __( 'CropX Team', 'cropx' );
}

/**
 * Resolve the public display name for a Team Member CPT entry.
 *
 * As of Sep 2026 the post title is an internal-only label (editors append
 * " (alternate)" to flag intentional duplicates so nobody accidentally
 * deletes a needed entry) — the "Employee's Full Name" meta field is what
 * actually displays anywhere a team member's name is shown publicly. Falls
 * back to the raw post title if full_name hasn't been filled in yet, so a
 * half-migrated or newly-created entry never renders blank.
 */
function cropx_get_team_member_name( int $post_id ): string {
	$full_name = trim( (string) get_post_meta( $post_id, 'full_name', true ) );
	return '' !== $full_name ? $full_name : get_the_title( $post_id );
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

/**
 * US state/territory list for the "State/Province/Territory" dropdown on the
 * Zoho-backed form blocks (cropx/zoho-form, cropx/zoho-contact-form).
 *
 * Copied verbatim (value strings, including the "(XX)" abbreviation suffix)
 * from Zoho's own live field (Dropdown8) — same reasoning as
 * cropx_zoho_country_list() above: these are real Zoho Dropdown option
 * values, not display labels, so they have to match Zoho's side exactly.
 *
 * This field only exists on the live Zoho form as part of a field rule.
 *
 * Sept 10, 2026 correction: an earlier version of this comment said the
 * field only shows/requires for Country = "United States", and that County
 * (Dropdown7)'s trigger condition couldn't be confirmed. Both of those were
 * wrong — they were based on an automated sweep of the live form that (a)
 * checked the native `.required` DOM attribute, which Zoho does NOT use to
 * express requiredness (it's baked into static "* Required" label text and
 * enforced by Zoho's own JS at submit time instead), and (b) looped through
 * many countries rapidly in one page-script execution, which produced
 * stale/leaked visibility state. Lauren caught the discrepancy by checking
 * Zoho's own Field Rules admin panel directly (Fields → [Field] → Rules) —
 * the authoritative source — and supplied screenshots of every rule, which
 * were then individually re-verified against the live form's label text.
 * The real rules:
 *   • "Show States": Country ∈ {United States, Canada, Australia, Mexico} →
 *     show + require State/Province/Territory (Dropdown8), with Zoho
 *     swapping in a different per-country option list each time (see
 *     cropx_zoho_state_options_by_country() below).
 *   • "Show City in USA": Country = United States → show + require City
 *     (SingleLine2). US-only, unlike State.
 * A fourth rule, "Show California Counties" (State/Province/Territory =
 * "California" → show + require County/Dropdown7), exists in Zoho's admin
 * panel but is NOT replicated here — confirmed, by reading
 * zf_rule.ruleObjs directly out of the live form's own JS, that its
 * condition value is the literal string "California" while the real
 * State field value is "California (CA)". Those never match, so the rule
 * can never actually fire and County never appears on the live form for
 * any visitor. Lauren caught this by testing the live form directly after
 * our first pass added a County field on our side that Zoho's own form
 * doesn't show. cropx_zoho_california_county_list() below is kept (in case
 * Zoho fixes the condition on their end) but unused by both blocks.
 * Full writeup in PROGRESS.md.
 *
 * @return string[] State/territory names with abbreviation, in Zoho's order.
 */
function cropx_zoho_us_state_list(): array {
	return array(
		'Alabama (AL)', 'Alaska (AK)', 'Arizona (AZ)', 'Arkansas (AR)', 'California (CA)',
		'Colorado (CO)', 'Connecticut (CT)', 'Delaware (DE)', 'Florida (FL)', 'Georgia (GA)',
		'Hawaii (HI)', 'Idaho (ID)', 'Illinois (IL)', 'Indiana (IN)', 'Iowa (IA)', 'Kansas (KS)',
		'Kentucky (KY)', 'Louisiana (LA)', 'Maine (ME)', 'Maryland (MD)', 'Massachusetts (MA)',
		'Michigan (MI)', 'Minnesota (MN)', 'Mississippi (MS)', 'Missouri (MO)', 'Montana (MT)',
		'Nebraska (NE)', 'Nevada (NV)', 'New Hampshire (NH)', 'New Jersey (NJ)',
		'New Mexico (NM)', 'New York (NY)', 'North Carolina (NC)', 'North Dakota (ND)',
		'Ohio (OH)', 'Oklahoma (OK)', 'Oregon (OR)', 'Pennsylvania (PA)', 'Rhode Island (RI)',
		'South Carolina (SC)', 'South Dakota (SD)', 'Tennessee (TN)', 'Texas (TX)', 'Utah (UT)',
		'Vermont (VT)', 'Virginia (VA)', 'Washington (WA)', 'West Virginia (WV)',
		'Wisconsin (WI)', 'Wyoming (WY)',
	);
}

/**
 * Canadian province/territory list for the "State/Province/Territory"
 * dropdown — shown/required (via the "Show States" rule, see
 * cropx_zoho_us_state_list() above) when Country = "Canada". Copied verbatim
 * from Zoho's live Dropdown8 options with Canada selected.
 *
 * @return string[] Province/territory names, in Zoho's order.
 */
function cropx_zoho_canada_province_list(): array {
	return array(
		'Alberta (AB)', 'British Columbia (BC)', 'Manitoba (MB)', 'New Brunswick (NB)',
		'Newfoundland and Labrador', 'Northwest Territories (NWT)', 'Nova Scotia (NS)',
		'Nunavut', 'Ontario (ON)', 'Prince Edward Island (PE)', 'Quebec (QC)',
		'Saskatchewan (SK)', 'Yukon (YT)',
	);
}

/**
 * Australian state/territory list for the "State/Province/Territory"
 * dropdown — shown/required when Country = "Australia". Copied verbatim
 * from Zoho's live Dropdown8 options with Australia selected.
 *
 * @return string[] State names, in Zoho's order.
 */
function cropx_zoho_australia_state_list(): array {
	return array(
		'New South Wales', 'Queensland', 'South Australia', 'Tasmania', 'Victoria',
		'Western Australia',
	);
}

/**
 * Mexican state list for the "State/Province/Territory" dropdown — shown/
 * required when Country = "Mexico". Copied verbatim from Zoho's live
 * Dropdown8 options with Mexico selected.
 *
 * @return string[] State names, in Zoho's order.
 */
function cropx_zoho_mexico_state_list(): array {
	return array(
		'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas',
		'Chihuahua', 'Coahuila', 'Colima', 'Durango', 'Estado de Mexico', 'Guanajuato',
		'Guerrero', 'Hidalgo', 'Jalisco', 'Mexico City DF', 'Michoacan', 'Morelos', 'Nayarit',
		'Nuevo Leon', 'Oaxaca', 'Puebla', 'Queretaro', 'Quintana Roo', 'San Luis Potosi',
		'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatan',
		'Zacatecas',
	);
}

/**
 * Country → state/province option-list map for every country where Zoho's
 * live "Show States" field rule fires. Both Zoho blocks' render.php files
 * emit this as a `data-state-options` JSON blob on the State/Province/
 * Territory select, and view.js reads it to rebuild that dropdown's
 * <option> list whenever Country changes — single source of truth here
 * instead of duplicating four option lists as JS literals.
 *
 * @return array<string, string[]> Country name (as it appears in
 *                                  cropx_zoho_country_list()) => state list.
 */
function cropx_zoho_state_options_by_country(): array {
	return array(
		'United States' => cropx_zoho_us_state_list(),
		'Canada'        => cropx_zoho_canada_province_list(),
		'Australia'     => cropx_zoho_australia_state_list(),
		'Mexico'        => cropx_zoho_mexico_state_list(),
	);
}

/**
 * California county list for the "County" dropdown (Dropdown7). NOT
 * currently used by either Zoho block — Zoho's own "Show California
 * Counties" field rule can never fire on the live form (its condition
 * value "California" doesn't match the State field's real value
 * "California (CA)"; see cropx_zoho_us_state_list()'s doc comment above
 * for the full story), so County never appears for real visitors and our
 * replica shouldn't show it either. Left here, unused, only so this list
 * doesn't have to be re-captured from the live DOM if Zoho ever fixes
 * their rule's condition value.
 *
 * @return string[] County names, in Zoho's order.
 */
function cropx_zoho_california_county_list(): array {
	return array(
		'Alameda', 'Alpine', 'Amador', 'Butte', 'Calaveras', 'Colusa', 'Contra Costa',
		'Del Norte', 'El Dorado', 'Fresno', 'Glenn', 'Humboldt', 'Imperial', 'Inyo', 'Kern',
		'Kings', 'Lake', 'Lassen', 'Los Angeles', 'Madera', 'Marin', 'Mariposa', 'Mendocino',
		'Merced', 'Modoc', 'Mono', 'Monterey', 'Napa', 'Nevada', 'Orange', 'Placer', 'Plumas',
		'Riverside', 'Sacramento', 'San Benito', 'San Bernardino', 'San Diego', 'San Francisco',
		'San Joaquin', 'San Luis Obispo', 'San Mateo', 'Santa Barbara', 'Santa Clara',
		'Santa Cruz', 'Shasta', 'Sierra', 'Siskiyou', 'Solano', 'Sonoma', 'Stanislaus', 'Sutter',
		'Tehama', 'Trinity', 'Tulare', 'Tuolumne', 'Ventura', 'Yolo', 'Yuba',
	);
}

/**
 * Canonical list of brochure language/format versions for the
 * cropx_resource CPT — single source of truth for both the "Download
 * Files" admin meta box (inc/cpts.php) and the resource-downloads block's
 * front-end version picker (src/blocks/resource-downloads/render.php).
 *
 * Order matters: it's the order versions appear in the admin meta box and
 * in the front-end <select>, and it's also the fallback-selection order —
 * when English (A4) has no file, the first entry below that DOES have one
 * becomes the default. English (A4) is deliberately second (not first) so
 * it can be visually grouped with English (US Letter) as a pair in the
 * admin UI, while still being the intended default in practice.
 *
 * Each entry's `meta_key` is the cropx_resource post meta key holding that
 * version's file URL. The English Letter/A4 keys (download_url_letter,
 * download_url_a4) predate this multi-language picker and are kept as-is
 * rather than renamed, so every already-published resource keeps working
 * with zero data migration — this list is purely additive on top of them.
 *
 * Labels (Sep 2026 simplification — Lauren): each version used to spell out
 * paper size and measurement units too, e.g. "English (US Letter / Imperial
 * Units)" / "Español (A4 / Unidades métricas)". Visitors don't need that
 * detail — they need to know which LANGUAGE a file is in, plus the one real
 * distinction that matters within English: US Letter vs. A4/ISO paper size.
 * So every label is now just the language name on its own ("Español",
 * "Português", …), except the two English versions, which are the only pair
 * sharing a language and therefore the only ones that still need a
 * distinguishing qualifier: "English (US)" (Letter) and "English
 * (International)" (A4 — the ISO version, and the sitewide default/fallback).
 * This same label also feeds the admin "Download Files" meta box field
 * labels in inc/cpts.php, so admin gets the same simplification for free.
 *
 * @return array<int, array{code: string, meta_key: string, label: string, group: string}>
 */
function cropx_resource_brochure_versions(): array {
	return array(
		array(
			'code'     => 'en_letter',
			'meta_key' => 'download_url_letter',
			'label'    => __( 'English (US)', 'cropx' ),
			'group'    => 'english',
		),
		array(
			'code'     => 'en_a4',
			'meta_key' => 'download_url_a4',
			'label'    => __( 'English (International)', 'cropx' ),
			'group'    => 'english',
		),
		array(
			'code'     => 'es_a4',
			'meta_key' => 'download_url_es_a4',
			'label'    => __( 'Español', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'pt_a4',
			'meta_key' => 'download_url_pt_a4',
			'label'    => __( 'Português', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'fr_a4',
			'meta_key' => 'download_url_fr_a4',
			'label'    => __( 'Français', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'de_a4',
			'meta_key' => 'download_url_de_a4',
			'label'    => __( 'Deutsch', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'nl_a4',
			'meta_key' => 'download_url_nl_a4',
			'label'    => __( 'Nederlands', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'ro_a4',
			'meta_key' => 'download_url_ro_a4',
			'label'    => __( 'Română', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'ru_a4',
			'meta_key' => 'download_url_ru_a4',
			'label'    => __( 'Русский', 'cropx' ),
			'group'    => 'other',
		),
	);
}

/**
 * Online Guide versions — a webpage link alternative to a downloadable PDF
 * (Sep 2026). Same shape/purpose as cropx_resource_brochure_versions(), but
 * resolved by its own separate cropx_resource_get_guide_version() below
 * (never merged with the PDF list — see that function's doc comment for
 * why a guide gets its own independent button instead). English-only for
 * now; add another entry here (plus a matching register_post_meta() call and
 * admin field in cpts.php) whenever a translated guide exists — nothing
 * else needs to change.
 *
 * @return array<int, array{code: string, meta_key: string, label: string, language: string}>
 */
function cropx_resource_guide_versions(): array {
	return array(
		array(
			'code'     => 'guide_en',
			'meta_key' => 'guide_url_en',
			'label'    => __( 'English (Online Guide)', 'cropx' ),
			'language' => __( 'English', 'cropx' ),
		),
	);
}

/**
 * Resolve the PDF-format versions that actually have a file/URL for one
 * cropx_resource post — cropx_resource_brochure_versions() filtered down to
 * entries with a non-empty meta value, each carrying its resolved URL and
 * `type` => 'pdf'. Used by render.php to decide between the PDF download
 * button's states (none / single / picker) without duplicating the
 * meta-lookup-and-filter logic there.
 *
 * The Online Guide is deliberately NOT part of this list (Sep 2026 redesign —
 * see cropx_resource_get_guide_version()): a guide is a different action
 * from downloading a PDF ("view a webpage" vs. "download a file"), not just
 * another format of the same document, so it gets its own always-visible
 * button instead of living as an option buried in this dropdown. That also
 * means a visitor never has to open a dropdown, notice the guide option, and
 * select it just to reach a single click's worth of content.
 *
 * Legacy single-PDF resources (Sep 2026 fix): plenty of older resources
 * were never migrated onto the per-language brochure fields below — they
 * just have the old single "General URL" field (download_url) filled in.
 * Before the Online Guide feature, that was invisible to this function:
 * $available came back empty, and render.php's separate legacy-fallback
 * branch handled the plain "Download PDF" button directly from $download_url.
 * That broke the day a guide URL got added to one of these resources — the
 * PDF vanished because this function didn't know about it at all. Folding
 * the legacy URL in here (only when none of the newer per-language fields
 * are filled, so already-migrated multi-version resources are untouched)
 * fixes that. `is_legacy` lets cropx_resource_format_single_download_label()
 * keep the exact same plain "Download PDF" text this resource always had
 * when it's still the only PDF version — see that function for why.
 *
 * @param int $post_id
 * @return array<int, array{code: string, label: string, url: string, type: string, is_legacy?: bool}>
 */
function cropx_resource_get_pdf_versions( int $post_id ): array {
	$available = array();

	foreach ( cropx_resource_brochure_versions() as $version ) {
		$url = get_post_meta( $post_id, $version['meta_key'], true );
		if ( empty( $url ) ) {
			continue;
		}
		$available[] = array(
			'code'  => $version['code'],
			'label' => $version['label'],
			'url'   => $url,
			'type'  => 'pdf',
		);
	}

	if ( empty( $available ) ) {
		$legacy_url = get_post_meta( $post_id, 'download_url', true );
		if ( ! empty( $legacy_url ) ) {
			$available[] = array(
				'code'      => 'legacy_pdf',
				'label'     => __( 'English (PDF)', 'cropx' ),
				'url'       => $legacy_url,
				'type'      => 'pdf',
				'is_legacy' => true,
			);
		}
	}

	return $available;
}

/**
 * Resolve this resource's Online Guide — a single entry (today only ever
 * 'guide_en', since cropx_resource_guide_versions() only defines English so
 * far) or null when no guide URL is filled in. Returns one entry rather than
 * an array because, unlike PDFs, there's never a language *choice* to make
 * here yet — see cropx_resource_guide_versions() for how another language
 * would slot in later, at which point this would need its own small picker.
 *
 * Kept deliberately separate from cropx_resource_get_pdf_versions() — see
 * that function's doc comment for why a guide isn't just another PDF format.
 *
 * @param int $post_id
 * @return array{code: string, label: string, url: string, type: string, language: string}|null
 */
function cropx_resource_get_guide_version( int $post_id ): ?array {
	foreach ( cropx_resource_guide_versions() as $version ) {
		$url = get_post_meta( $post_id, $version['meta_key'], true );
		if ( empty( $url ) ) {
			continue;
		}
		return array(
			'code'     => $version['code'],
			'label'    => $version['label'],
			'url'      => $url,
			'type'     => 'guide',
			'language' => $version['language'],
		);
	}

	return null;
}

/**
 * Build the guide button's label — always the detailed "View Online Guide
 * (English)" form, since (unlike the PDF picker) this button is never
 * paired with a dropdown that could carry the language detail instead. A
 * one-line wrapper mainly so render.php and single-cropx_resource.php both
 * format it identically without repeating the sprintf().
 *
 * @param array{language: string} $guide
 * @return string
 */
function cropx_resource_format_guide_label( array $guide ): string {
	return sprintf(
		/* translators: %s: language name, e.g. "English" */
		__( 'View Online Guide (%s)', 'cropx' ),
		$guide['language']
	);
}

/**
 * Build the PDF download button's label for the "exactly one PDF version
 * available" case — the only place a version's language is spelled out
 * directly on the button. When there are 2+ PDF versions, the button uses a
 * plain generic "Download PDF" instead — the dropdown's own option text
 * already carries the language there, so repeating it on the button would be
 * redundant. See render.php.
 *
 * Simplified Sep 2026 (Lauren): versions used to carry paper-size/measurement
 * detail baked into their `label` as "Name (Detail)" (e.g. "English (A4 /
 * Metric Units)"), which this function reflowed into "Download PDF (Name /
 * Detail)". Labels are now just the plain language name on its own (see
 * cropx_resource_brochure_versions()) — "English (International)", "English
 * (US)", "Español", etc. — so this just wraps that value directly:
 * "Download PDF (English (International))".
 *
 * A legacy single-PDF version (see the `is_legacy` note on
 * cropx_resource_get_pdf_versions()) has no real language on file at all —
 * short-circuits straight to the plain "Download PDF" fallback rather than
 * the misleading "Download PDF (English)" (that "English (PDF)" label exists
 * only so it would still read sensibly if it ever ended up in a dropdown).
 *
 * @param array{label: string, is_legacy?: bool} $version
 * @return string
 */
function cropx_resource_format_single_download_label( array $version ): string {
	if ( $version['is_legacy'] ?? false ) {
		return __( 'Download PDF', 'cropx' );
	}

	return sprintf(
		/* translators: %s: language name, e.g. "English" or "US English" */
		__( 'Download PDF (%s)', 'cropx' ),
		$version['label']
	);
}

/**
 * The current site visitor's language, as a brochure-version code prefix
 * ('en', 'es', 'fr', ...). Single source of truth for "which language is
 * this visitor viewing the site in" — everything that needs to make a
 * language-aware fallback decision (right now, just the resource card
 * thumbnail below) should call this rather than hardcoding 'en' itself.
 *
 * The site is English-only today, so this always returns 'en'. Once a
 * language switcher exists (Polylang or similar — see PROGRESS.md's i18n
 * plan), replace the body with a real lookup, e.g.:
 *   return function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
 * No caller needs to change when that happens.
 *
 * @return string
 */
function cropx_resource_get_current_lang_code(): string {
	return 'en';
}

/**
 * Which brochure version's file should supply a resource card's cover
 * thumbnail — deliberately independent of whatever version a visitor has
 * picked in the download dropdown. Lauren's call (Aug 2026): the thumbnail
 * is a stable representation of "this resource" on the page, not a live
 * preview of the currently-selected download, so it never changes when the
 * dropdown selection changes (see render.php/view.js — the <select> only
 * ever updates the download link, never the cover).
 *
 * Resolution order:
 *   1. The current site language's A4 version (e.g. 'fr_a4' on a future
 *      French site) — the version a visitor in that language would expect.
 *   2. Any other version in that same language (covers 'en_letter' if
 *      'en_a4' isn't uploaded, so English visitors still get an English
 *      thumbnail rather than skipping straight to another language).
 *   3. The English A4 version — the sitewide fallback today, and the
 *      fallback for every other language until that language's own file
 *      is uploaded.
 *   4. Whatever version happens to be first, if even English A4 is missing.
 *
 * On today's English-only site this always resolves to step 1 (lang is
 * always 'en', so it's functionally "English A4, else whatever's first") —
 * the extra steps exist so this function doesn't need to change shape when
 * cropx_resource_get_current_lang_code() starts returning other languages.
 *
 * Online Guide versions (Sep 2026) never enter into this at all — a guide is
 * a webpage, not a PDF, so there's no first page for pdf.js to render, and
 * cropx_resource_get_pdf_versions() already excludes it entirely. A
 * resource with only a guide and no PDF simply gets no generated thumbnail
 * (falls back to the placeholder icon), rather than pdf.js silently failing
 * to load the guide's URL as a PDF.
 *
 * @param int $post_id
 * @return array{code: string, label: string, url: string}|null
 */
function cropx_resource_get_thumbnail_version( int $post_id ): ?array {
	$versions = cropx_resource_get_pdf_versions( $post_id );
	if ( empty( $versions ) ) {
		return null;
	}

	$lang = cropx_resource_get_current_lang_code();

	foreach ( $versions as $v ) {
		if ( $lang . '_a4' === $v['code'] ) {
			return $v;
		}
	}
	foreach ( $versions as $v ) {
		if ( 0 === strpos( $v['code'], $lang . '_' ) ) {
			return $v;
		}
	}
	foreach ( $versions as $v ) {
		if ( 'en_a4' === $v['code'] ) {
			return $v;
		}
	}

	return $versions[0];
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

/**
 * Resolve an intrinsic width/height HTML attribute string for a manually
 * echoed <img> tag (Sep 2026 — PageSpeed "Image elements do not have
 * explicit width and height" diagnostic).
 *
 * Most of our blocks build their <img> markup by hand (string concatenation
 * in render.php) rather than via wp_get_attachment_image(), which is the
 * only core function that adds width/height automatically. This helper
 * gives hand-built tags the same behavior: pass the attachment ID when the
 * block already has one in scope (cheapest — one cached metadata lookup,
 * no HTTP/disk read), or a bare URL as a fallback for the rarer case where
 * only a URL was stored (e.g. a legacy attribute saved before a block
 * tracked photoId). Returns '' on any failure so a bad/foreign URL just
 * degrades to today's behavior (no dimensions) instead of a PHP notice.
 *
 * NOTE: on blocks built per CLAUDE.md gotcha #9 (photo wrapped in a
 * fixed-size/aspect-ratio container, <img> using object-fit:cover), the
 * wrapper's own CSS already reserves the layout space — these width/height
 * attributes are redundant belt-and-suspenders for CLS on those, but they
 * still satisfy the Lighthouse diagnostic and matter more for any image
 * NOT inside such a wrapper.
 *
 * @param int    $attachment_id Attachment post ID, 0 if unknown.
 * @param string $fallback_url  Image URL to resolve via attachment_url_to_postid()
 *                               when $attachment_id is 0.
 * @return string Either '' or a leading-space-prefixed ' width="123" height="456"'
 *                ready to concatenate straight into an <img ...> string.
 */
function cropx_img_dims_attr( int $attachment_id = 0, string $fallback_url = '' ): string {
	if ( ! $attachment_id && $fallback_url ) {
		$attachment_id = attachment_url_to_postid( $fallback_url );
	}

	if ( ! $attachment_id ) {
		return '';
	}

	$meta = wp_get_attachment_metadata( $attachment_id );

	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return '';
	}

	return ' width="' . (int) $meta['width'] . '" height="' . (int) $meta['height'] . '"';
}

/**
 * Build a responsive srcset for a full-bleed hero background photo.
 *
 * All six hero-family blocks (hero, hero-curved, hero-curved-standard,
 * hero-curved-animated, hero-blog, segment-hero) render their background
 * photo as a real <img> that fills its wrapper via `position:absolute;
 * inset:0` + `object-fit:cover` — see each block's *-bg CSS rule. That
 * wrapper always spans the full viewport width at every breakpoint, so
 * `sizes="100vw"` is accurate everywhere; there's no split-column layout
 * to account for. Confirmed via PageSpeed audit (Sep 2026): without this,
 * every visitor — phone included — downloaded the same ~2400px full-size
 * original, which PageSpeed flagged as "larger than it needs to be for its
 * displayed dimensions."
 *
 * wp_get_attachment_image_srcset() pulls from WordPress's own registered
 * intermediate sizes (thumbnail/medium/medium_large/large/full), which
 * already exist for every uploaded photo with no extra work needed.
 *
 * Only works when there's a real attachment ID — the hardcoded fallback
 * illustrations some blocks ship with (SVG/PNG in assets/) aren't
 * WordPress attachments and have no intermediate sizes to draw from, so
 * this returns '' for those and the <img> just keeps its plain src.
 */
function cropx_bg_img_responsive_attr( int $attachment_id = 0 ): string {
	if ( ! $attachment_id ) {
		return '';
	}

	$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
	if ( ! $srcset ) {
		return '';
	}

	return ' srcset="' . esc_attr( $srcset ) . '" sizes="100vw"';
}

/**
 * Resolve the best-sized URL for a small logo image (Logo Carousel block).
 *
 * Logos render inside a fixed 120x120 slot (see .ls-logo-slot in
 * logo-strip/style.css) via object-fit: contain, but the block was serving
 * whatever resolution the editor originally uploaded — PageSpeed audit
 * (Sep 2026) caught several customer logos going out at 400x400 for a
 * 120x120 display, wasting a few KiB per logo across every page load.
 *
 * WordPress's built-in 'medium' size (300px max dimension, proportional —
 * never cropped) already exists for every raster image in the library with
 * zero extra setup: no add_image_size() registration and no thumbnail
 * regeneration needed for already-uploaded logos, unlike a custom size
 * would require. 300px is comfortably larger than the 120px display size
 * for 2x/3x retina screens while still being far smaller than a random
 * upload's original resolution.
 *
 * SVG logos are left untouched — they're vector, so there's no smaller
 * raster rendition to request and no size to gain from trying.
 */
function cropx_logo_img_url( int $attachment_id, string $fallback_url ): string {
	if ( ! $attachment_id ) {
		return $fallback_url;
	}

	if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
		return $fallback_url;
	}

	$resized = wp_get_attachment_image_src( $attachment_id, 'medium' );

	return $resized ? $resized[0] : $fallback_url;
}

