<?php
/**
 * Team archive — archive-cropx_team_member.php
 *
 * Public-facing page at /team/. has_archive was flipped true for
 * cropx_team_member in Aug 2026 specifically to route here — see the
 * comment on that flag in inc/cpts.php. No "Team" WP Page existed on
 * staging at the time of that change, so there was nothing to collide with.
 *
 * Deliberately basic per Lauren's brief (Aug 2026): "very simple, using the
 * most basic pages we have — like the Terms & Conditions page — as a
 * reference point." A plain photo grid — headshot, name, job title — each
 * linking through to a simple bio page (single-cropx_team_member.php).
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
?>

<section class="section-padded">
	<div class="section-inner">

		<div class="pg-header section-header section-header--left">
			<span class="section-eyebrow"><?php esc_html_e( 'Team', 'cropx' ); ?></span>
			<h1 class="section-heading"><?php esc_html_e( 'Our Team', 'cropx' ); ?></h1>
			<p class="section-body"><?php esc_html_e( 'The people behind CropX.', 'cropx' ); ?></p>
		</div>

		<?php if ( ! have_posts() ) : ?>

			<div class="scpt-empty">
				<p><?php esc_html_e( 'No team members found.', 'cropx' ); ?></p>
			</div>

		<?php else : ?>

			<div class="scpt-grid">
				<?php
				while ( have_posts() ) :
					the_post();

					$member_id    = get_the_ID();
					$member_title = get_post_meta( $member_id, 'job_title', true );
					$member_thumb = get_the_post_thumbnail_url( null, 'medium' );
					?>
					<a href="<?php the_permalink(); ?>" class="scpt-card">

						<div class="scpt-card-img scpt-card-img--avatar">
							<?php if ( $member_thumb ) : ?>
								<img src="<?php echo esc_url( $member_thumb ); ?>"
									alt="<?php echo esc_attr( get_the_title() ); ?>"
									loading="lazy" decoding="async">
							<?php endif; ?>
						</div>

						<div class="scpt-card-body">
							<h2 class="scpt-card-title"><?php the_title(); ?></h2>
							<?php if ( $member_title ) : ?>
								<p class="scpt-card-meta"><?php echo esc_html( $member_title ); ?></p>
							<?php endif; ?>
						</div>

					</a>
				<?php endwhile; ?>
			</div>

			<?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
				<div class="scpt-pagination">
					<?php
					the_posts_pagination( array(
						'prev_text' => __( '← Previous', 'cropx' ),
						'next_text' => __( 'Next →', 'cropx' ),
					) );
					?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>

<?php
// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
$_team_pfc_ref = cropx_get_synced_block_ref( 'fallback-cat-pg-demo-form-cta' );
echo do_blocks( $_team_pfc_ref
	? '<!-- wp:block {"ref":' . $_team_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
