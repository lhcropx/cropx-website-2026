<?php
/**
 * Newsletter CTA block — front-end render.
 *
 * Attributes:
 *   heading          (string) Section heading.
 *   body             (string) Description paragraph.
 *   formShortcode    (string) HubSpot / Mailchimp / etc. shortcode. Empty → placeholder form.
 *   inputPlaceholder (string) Email input placeholder text.
 *   buttonLabel      (string) Submit button label.
 *   privacyText      (string) Privacy note shown below the form.
 */

$heading           = isset( $attributes['heading'] )          ? $attributes['heading']          : __( 'Stay ahead in agronomy', 'cropx' );
$body              = isset( $attributes['body'] )             ? $attributes['body']             : '';
$form_shortcode    = isset( $attributes['formShortcode'] )    ? trim( $attributes['formShortcode'] ) : '';
$input_placeholder = isset( $attributes['inputPlaceholder'] ) ? $attributes['inputPlaceholder'] : __( 'Enter your work email', 'cropx' );
$button_label      = isset( $attributes['buttonLabel'] )      ? $attributes['buttonLabel']      : __( 'Subscribe', 'cropx' );
$privacy_text      = isset( $attributes['privacyText'] )      ? $attributes['privacyText']      : __( 'We care about your data in our privacy policy.', 'cropx' );
$privacy_url       = get_privacy_policy_url();

$anchor = ! empty( $attributes['anchor'] ) ? ' id="' . esc_attr( $attributes['anchor'] ) . '"' : '';
?>
<section class="cropx-newsletter"<?php echo $anchor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Newsletter signup', 'cropx' ); ?>">
	<div class="cropx-newsletter-inner">

		<div class="cropx-newsletter-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
		</div>

		<h2 class="cropx-newsletter-title"><?php echo wp_kses_post( $heading ); ?></h2>

		<?php if ( $body ) : ?>
			<p class="cropx-newsletter-desc"><?php echo wp_kses_post( $body ); ?></p>
		<?php endif; ?>

		<?php if ( $form_shortcode ) : ?>
			<div class="cropx-newsletter-form-wrap">
				<?php echo do_shortcode( $form_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php else : ?>
			<?php /* Placeholder form — replace formShortcode in the block editor with your provider's embed */ ?>
			<form class="cropx-newsletter-form" action="#" method="post">
				<div class="cropx-input-wrap">
					<span class="cropx-input-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					</span>
					<input
						type="email"
						name="email"
						class="cropx-newsletter-input"
						placeholder="<?php echo esc_attr( $input_placeholder ); ?>"
						aria-label="<?php esc_attr_e( 'Work email address', 'cropx' ); ?>"
						required
					>
				</div>
				<button type="submit" class="cropx-btn-subscribe">
					<?php echo esc_html( $button_label ); ?>
				</button>
			</form>
		<?php endif; ?>

		<p class="cropx-newsletter-privacy">
			<?php
			if ( $privacy_url ) {
				// Append a linked "privacy policy" to whatever the editor typed.
				// If $privacy_text already contains a link, this will double-link —
				// in that case set privacyText to empty and rely solely on the WP policy page.
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
