<?php
/**
 * Newsletter CTA block — front-end render.
 *
 * Attributes:
 *   bgColor          (string) 'white' | 'taupe' | 'deep-blue'  (default: 'white')
 *   heading          (string) Section heading.
 *   body             (string) Description paragraph.
 *   formShortcode    (string) HubSpot / Mailchimp / etc. shortcode. Empty → placeholder form.
 *   inputPlaceholder (string) Email input placeholder text.
 *   buttonLabel      (string) Submit button label.
 *   privacyText      (string) Privacy note shown below the form.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Attributes ────────────────────────────────────────────────────────────────
$bg_color          = isset( $attributes['bgColor'] )          ? $attributes['bgColor']          : 'white';
$heading           = isset( $attributes['heading'] )          ? $attributes['heading']          : __( 'Stay ahead in agronomy', 'cropx' );
$body              = isset( $attributes['body'] )             ? $attributes['body']             : '';
$form_shortcode    = isset( $attributes['formShortcode'] )    ? trim( $attributes['formShortcode'] ) : '';
$input_placeholder = isset( $attributes['inputPlaceholder'] ) ? $attributes['inputPlaceholder'] : __( 'Enter your work email', 'cropx' );
$button_label      = isset( $attributes['buttonLabel'] )      ? $attributes['buttonLabel']      : __( 'Subscribe', 'cropx' );
$privacy_text      = isset( $attributes['privacyText'] )      ? $attributes['privacyText']      : __( 'We care about your data in our privacy policy.', 'cropx' );
$privacy_url       = get_privacy_policy_url();

// Validate enum — fall back to white if an unexpected value was stored.
if ( ! in_array( $bg_color, array( 'white', 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}

// ── Topo drift injection (deep-blue only) ─────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video.

// ── Wrapper attributes ────────────────────────────────────────────────────────
$section_class = 'ncta-section ncta--bg-' . $bg_color;

$extra_attrs = array( 'class' => $section_class, 'aria-label' => __( 'Newsletter signup', 'cropx' ) );
if ( 'deep-blue' === $bg_color ) {
	$extra_attrs['style'] = '--ncta-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $extra_attrs );

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="ncta-inner">

		<!-- ── Envelope icon box ── -->
		<div class="ncta-icon-box" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
				<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
				<polyline points="22,6 12,13 2,6"/>
			</svg>
		</div>

		<h2 class="section-heading ncta-heading"><?php echo wp_kses_post( $heading ); ?></h2>

		<?php if ( $body ) : ?>
			<p class="section-body ncta-desc"><?php echo wp_kses_post( $body ); ?></p>
		<?php endif; ?>

		<?php if ( $form_shortcode ) : ?>
			<div class="ncta-form-wrap">
				<?php echo do_shortcode( $form_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php else : ?>
			<?php /* Placeholder form — replace formShortcode in the block editor with your provider embed */ ?>
			<form class="ncta-form" action="#" method="post">
				<input
					type="email"
					name="email"
					class="ncta-input"
					placeholder="<?php echo esc_attr( $input_placeholder ); ?>"
					aria-label="<?php esc_attr_e( 'Work email address', 'cropx' ); ?>"
					required
				>
				<button type="submit" class="ncta-btn-subscribe btn-primary">
					<?php echo esc_html( $button_label ); ?>
				</button>
			</form>
		<?php endif; ?>

		<p class="ncta-privacy">
			<?php
			if ( $privacy_url ) {
				printf(
					'%s <a href="%s">%s</a>',
					wp_kses_post( rtrim( $privacy_text, '.' ) ),
					esc_url( $privacy_url ),
					esc_html__( 'Privacy policy', 'cropx' )
				);
			} else {
				echo wp_kses_post( $privacy_text );
			}
			?>
		</p>

	</div>
</section>
