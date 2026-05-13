<?php
/**
 * Logo strip block — front-end render.
 *
 * Full-width scrolling marquee of 10 customer logos. The track renders
 * each logo twice so the CSS translateX(-50%) animation loops seamlessly.
 * Only the eyebrow text is editable; logos are hardcoded here.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow   = $attributes['eyebrow'] ?? 'Trusted by leading brands worldwide';
$logos_dir = CROPX_THEME_URI . 'assets/logos/';

$logos = array(
	array( 'file' => 'anheuser-busch-a.svg', 'alt' => 'AB InBev' ),
	array( 'file' => 'dairy-holdings.svg',   'alt' => 'Dairy Holdings' ),
	array( 'file' => 'general-mills.svg',    'alt' => 'General Mills' ),
	array( 'file' => 'hzpc.svg',             'alt' => 'HZPC' ),
	array( 'file' => 'mccain.svg',           'alt' => 'McCain' ),
	array( 'file' => 'nasa.svg',             'alt' => 'NASA' ),
	array( 'file' => 'nec.svg',              'alt' => 'NEC' ),
	array( 'file' => 'nestle.svg',           'alt' => 'Nestlé' ),
	array( 'file' => 'pepsico.svg',          'alt' => 'PepsiCo' ),
	array( 'file' => 'ritter-sport.svg',     'alt' => 'Ritter Sport' ),
);

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'logo-strip' ) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Customer logos', 'cropx' ); ?>">
	<div class="logo-strip-inner">
		<?php if ( $eyebrow ) : ?>
			<p class="logo-strip-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
	</div>

	<div class="ls-marquee">
		<div class="ls-track">
			<?php foreach ( $logos as $logo ) : ?>
				<img
					src="<?php echo esc_url( $logos_dir . $logo['file'] ); ?>"
					alt="<?php echo esc_attr( $logo['alt'] ); ?>"
					class="ls-logo"
				>
			<?php endforeach; ?>
			<?php foreach ( $logos as $logo ) : ?>
				<img
					src="<?php echo esc_url( $logos_dir . $logo['file'] ); ?>"
					alt=""
					aria-hidden="true"
					class="ls-logo"
				>
			<?php endforeach; ?>
		</div>
	</div>
</section>
