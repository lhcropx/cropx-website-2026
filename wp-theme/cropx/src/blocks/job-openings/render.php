<?php
/**
 * Job Openings (Workable) — front-end render.
 *
 * Pulls live listings from Workable via cropx_get_workable_jobs()
 * (inc/workable-jobs-api.php), which sources data through the "Workable API"
 * plugin's own credentials (wp-admin → Settings → Workable API).
 *
 * Three states:
 *   1. Not configured yet          — render sample placeholder jobs, with an
 *                                    admin-only banner explaining why.
 *   2. Configured but fetch failed — same sample fallback, admin-only banner
 *                                    says the connection failed instead.
 *   3. Configured and working      — real jobs, or the configurable empty
 *                                    state message if there are genuinely
 *                                    zero open roles right now.
 *
 * Sample data is never shown to a regular site visitor once Workable is
 * successfully connected — only when there's nothing real to show yet.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$heading         = $attributes['heading']        ?? '';
$show_heading    = (bool) ( $attributes['showHeading'] ?? true );
$heading_align   = $attributes['headingAlign']   ?? 'left';
$bg_color        = $attributes['bgColor']        ?? 'white';
$limit           = (int) ( $attributes['limit']  ?? 0 );
$empty_state_text = $attributes['emptyStateText'] ?? '';

if ( ! in_array( $bg_color, array( 'white', 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}
if ( ! in_array( $heading_align, array( 'left', 'center' ), true ) ) {
	$heading_align = 'left';
}

$result       = function_exists( 'cropx_get_workable_jobs' ) ? cropx_get_workable_jobs() : array( 'configured' => false, 'error' => false, 'jobs' => array() );
$is_admin     = current_user_can( 'manage_options' );
$show_sample  = false;
$sample_notice = '';

if ( ! $result['configured'] ) {
	$jobs          = function_exists( 'cropx_get_sample_workable_jobs' ) ? cropx_get_sample_workable_jobs() : array();
	$show_sample   = true;
	$sample_notice = __( 'Preview data — Workable isn\'t connected yet. Add your subdomain + access token in Settings → Workable API to show live openings.', 'cropx' );
} elseif ( $result['error'] ) {
	$jobs          = function_exists( 'cropx_get_sample_workable_jobs' ) ? cropx_get_sample_workable_jobs() : array();
	$show_sample   = true;
	$sample_notice = __( 'Preview data — couldn\'t reach Workable just now. Double-check the access token in Settings → Workable API.', 'cropx' );
} else {
	$jobs = $result['jobs'];
}

if ( $limit > 0 ) {
	$jobs = array_slice( $jobs, 0, $limit );
}

$section_class = 'cjo-section cjo-section--bg-' . $bg_color;
$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class ) );

$header_class = 'cjo-header section-header' . ( 'left' === $heading_align ? ' section-header--left' : '' );
$has_header   = $show_heading && $heading;

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cjo-inner">

		<?php if ( $has_header ) : ?>
		<div class="<?php echo esc_attr( $header_class ); ?>">
			<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
		</div>
		<?php endif; ?>

		<?php if ( $show_sample && $is_admin ) : ?>
		<div class="cjo-admin-notice">
			<?php echo esc_html( $sample_notice ); ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $jobs ) ) : ?>
			<div class="cjo-list">
				<?php foreach ( $jobs as $job ) :
					$job_title      = $job['title']      ?? '';
					$job_department = $job['department'] ?? '';
					$job_location   = $job['location']   ?? '';
					$job_url        = $job['url']         ?? '#';

					if ( '' === $job_title ) {
						continue;
					}
				?>
					<a href="<?php echo esc_url( $job_url ); ?>" class="cjo-job"<?php echo ( ! $show_sample ) ? ' target="_blank" rel="noopener"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span class="cjo-job-main">
							<span class="cjo-job-title"><?php echo esc_html( $job_title ); ?></span>
							<?php if ( $job_department || $job_location ) : ?>
								<span class="cjo-job-meta">
									<?php if ( $job_department ) : ?><?php echo esc_html( $job_department ); ?><?php endif; ?>
									<?php if ( $job_department && $job_location ) : ?><span class="cjo-job-dot" aria-hidden="true">•</span><?php endif; ?>
									<?php if ( $job_location ) : ?><?php echo esc_html( $job_location ); ?><?php endif; ?>
								</span>
							<?php endif; ?>
						</span>
						<span class="cjo-job-arrow" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="cjo-empty"><?php echo esc_html( $empty_state_text ); ?></p>
		<?php endif; ?>

	</div>
</section>
