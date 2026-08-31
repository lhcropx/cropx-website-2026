<?php
/**
 * Team member single — single-cropx_team_member.php
 *
 * Public-facing page at /team/{post-name}/. Deliberately basic per Lauren's
 * brief (Aug 2026): "very simple, using the most basic pages we have — like
 * the Terms & Conditions page — as a reference point." No newsletter
 * subscribe CTA — just photo, name, job title, bio, and an optional LinkedIn
 * link, plus the "Fallback Cat. Pg. Demo Form + CTA" synced pattern.
 *
 * cropx_team_member only supports 'title' and 'thumbnail' (no 'editor' — see
 * inc/cpts.php); the bio is a plain-text post meta field (bio), not
 * post_content, so it's escaped + nl2br'd here rather than run through
 * the_content()/do_blocks().
 *
 * Assets:
 *   styles/simple-cpt.css — enqueued on this + the other 5 "simple CPT"
 *                            templates, see inc/enqueue.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

while ( have_posts() ) :
	the_post();

	$member_id    = get_the_ID();
	$member_title = get_post_meta( $member_id, 'job_title', true );
	$member_bio   = get_post_meta( $member_id, 'bio', true );
	$member_li    = get_post_meta( $member_id, 'linkedin_url', true );
	$member_thumb = get_the_post_thumbnail_url( null, 'large' );
	?>

	<article class="section-padded">
		<div class="section-inner">

			<div class="scpt-detail">

				<div class="scpt-detail-photo">
					<?php if ( $member_thumb ) : ?>
						<img src="<?php echo esc_url( $member_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="eager" decoding="async">
					<?php endif; ?>
				</div>

				<div class="scpt-detail-body">

					<h1 class="section-heading"><?php the_title(); ?></h1>

					<?php if ( $member_title ) : ?>
						<p class="scpt-detail-role"><?php echo esc_html( $member_title ); ?></p>
					<?php endif; ?>

					<?php if ( $member_bio ) : ?>
						<p class="section-body"><?php echo wp_kses_post( nl2br( esc_html( $member_bio ) ) ); ?></p>
					<?php endif; ?>

					<?php if ( $member_li ) : ?>
						<p style="margin-top: var(--space-6);">
							<a class="cta-link" href="<?php echo esc_url( $member_li ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View LinkedIn profile', 'cropx' ); ?>
							</a>
						</p>
					<?php endif; ?>

					<p style="margin-top: var(--space-8);">
						<a class="cta-link" href="<?php echo esc_url( get_post_type_archive_link( 'cropx_team_member' ) ); ?>">
							<?php esc_html_e( '← Back to Team', 'cropx' ); ?>
						</a>
					</p>

				</div>
			</div>

		</div>
	</article>

<?php endwhile;

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
$_member_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_member_pfc_ref
	? '<!-- wp:block {"ref":' . $_member_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
