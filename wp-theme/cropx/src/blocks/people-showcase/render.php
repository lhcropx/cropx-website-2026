<?php
/**
 * People Showcase — front-end render
 *
 * Team members are now driven by the cropx_team_member CPT. Each member item
 * in the teamMembers attribute stores only { type: 'member', postId: 123 }.
 * This template fetches the post data live so changes to a team member's CPT
 * entry (name, photo, title, LinkedIn) are immediately reflected everywhere
 * the block appears, with no block re-save needed.
 *
 * Attributes:
 *   backgroundStyle        string  'taupe' | 'white' | 'dark'
 *   showIntro              bool
 *   showEyebrow            bool
 *   showBody               bool
 *   introAlignment         string  'center' | 'left'
 *   eyebrow / heading / body  strings
 *   photoRatio             string  'square' | 'portrait'
 *   groupHeadingAlignment  string  'left' | 'center'
 *   teamMembers            array   flat array of:
 *                            { type: 'member', postId: 123 }
 *                            { type: 'group',  label: '…'  }
 */

$bg_style      = $attributes['backgroundStyle'] ?? 'white';
$show_intro    = $attributes['showIntro']        ?? true;
$show_eyebrow  = $attributes['showEyebrow']      ?? true;
$show_body     = $attributes['showBody']         ?? true;
$eyebrow_color_map = [
	'cropx-blue' => 'var(--cropx-blue)',
	'deep-blue'  => 'var(--deep-blue)',
	'white'      => '#fff',
];
$eyebrow_color_css    = $eyebrow_color_map[ $attributes['eyebrowColor'] ?? 'cropx-blue' ] ?? 'var(--cropx-blue)';
$intro_align          = $attributes['introAlignment']        ?? 'center';
$eyebrow              = $attributes['eyebrow']               ?? '';
$heading              = $attributes['heading']               ?? '';
$body                 = $attributes['body']                  ?? '';
$photo_ratio          = $attributes['photoRatio']            ?? 'square';
$card_style           = $attributes['cardStyle']             ?? 'white';
$group_heading_align  = $attributes['groupHeadingAlignment'] ?? 'left';
$group_heading_class  = 'people-group-heading' . ( $group_heading_align === 'center' ? ' people-group-heading--center' : '' );
$team_members         = $attributes['teamMembers']           ?? [];

$section_class = 'cropx-people-showcase people--' . esc_attr( $bg_style );
$header_class  = 'section-header' . ( $intro_align === 'left' ? ' section-header--left' : '' );
$photo_class   = 'team-photo team-photo--' . esc_attr( $photo_ratio );
$card_class    = 'team-card team-card--' . esc_attr( $card_style );

$linkedin_svg = '<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';

// Inject drift pattern URL for dark bg variant.
if ( $bg_style === 'dark' ) {
	$drift_url = esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' );
	$block_id  = 'ppl-' . substr( md5( serialize( $attributes ) ), 0, 8 );
	echo '<style>.cropx-people-showcase[data-drift="' . esc_attr( $block_id ) . '"]::before{background-image:url(' . $drift_url . ')}</style>';
}
?>

<?php
$_ppl_attrs = [
	'class'      => esc_attr( $section_class ),
	'data-drift' => isset( $block_id ) ? esc_attr( $block_id ) : '',
];
if ( in_array( $bg_style, array( 'taupe', 'white' ), true ) ) {
	$_ppl_attrs['data-section-bg'] = $bg_style;
}
$wrapper_attrs = get_block_wrapper_attributes( $_ppl_attrs );
?>
<section <?php echo $wrapper_attrs; ?>>
	<div class="section-inner">

		<?php if ( $show_intro ) : ?>
		<div class="<?php echo esc_attr( $header_class ); ?>">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: <?php echo esc_attr( $eyebrow_color_css ); ?>"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $show_body && $body ) : ?>
				<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php
		// Build sections from the flat mixed-type array.
		$sections        = [];
		$current_section = [ 'heading' => null, 'members' => [] ];

		foreach ( $team_members as $item ) {
			$type = $item['type'] ?? 'member';
			if ( $type === 'group' ) {
				if ( ! empty( $current_section['members'] ) || $current_section['heading'] !== null ) {
					$sections[] = $current_section;
				}
				$current_section = [ 'heading' => $item, 'members' => [] ];
			} else {
				$current_section['members'][] = $item;
			}
		}
		if ( ! empty( $current_section['members'] ) || $current_section['heading'] !== null ) {
			$sections[] = $current_section;
		}
		?>

		<?php foreach ( $sections as $section ) : ?>
		<div class="people-group">
			<?php if ( ! empty( $section['heading']['label'] ) ) : ?>
				<h3 class="<?php echo esc_attr( $group_heading_class ); ?>"><?php echo esc_html( $section['heading']['label'] ); ?></h3>
			<?php endif; ?>
			<div class="people-grid">
				<?php foreach ( $section['members'] as $member ) :
					// Pull the post ID from the attribute; skip if missing or invalid.
					$post_id = intval( $member['postId'] ?? 0 );
					if ( ! $post_id ) continue;

					$post = get_post( $post_id );
					if ( ! $post || $post->post_status !== 'publish' ) continue;

					// Data from CPT —————————————————————————————————————————
					$name         = esc_html( get_the_title( $post_id ) );
					$role         = esc_html( get_post_meta( $post_id, 'job_title', true ) );
					$linkedin_url = get_post_meta( $post_id, 'linkedin_url', true );

					// Featured image — use medium_large for crisp display.
					$photo_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
					if ( ! $photo_url ) {
						$photo_url = get_the_post_thumbnail_url( $post_id, 'large' );
					}
					$photo_url = $photo_url ? esc_url( $photo_url ) : '';

					// Alt text: from attachment meta, fall back to person's name.
					$thumb_id  = (int) get_post_thumbnail_id( $post_id );
					$photo_alt = $thumb_id
						? esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ?: get_the_title( $post_id ) )
						: esc_attr( get_the_title( $post_id ) );
				?>
				<article class="<?php echo esc_attr( $card_class ); ?>">
					<div class="<?php echo esc_attr( $photo_class ); ?>">
						<?php if ( $photo_url ) : ?>
							<img src="<?php echo $photo_url; ?>" alt="<?php echo $photo_alt; ?>" loading="lazy">
						<?php endif; ?>
					</div>
					<div class="team-info">
						<div class="team-name-row">
							<p class="team-name"><?php echo $name; ?></p>
							<?php if ( $linkedin_url ) : ?>
								<a href="<?php echo esc_url( $linkedin_url ); ?>" class="team-linkedin-icon" aria-label="<?php echo $name; ?> on LinkedIn" target="_blank" rel="noopener noreferrer">
									<span class="linkedin-badge"><?php echo $linkedin_svg; ?></span>
								</a>
							<?php endif; ?>
						</div>
						<?php if ( $role ) : ?>
							<p class="team-role"><?php echo $role; ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>

	</div>
</section>
