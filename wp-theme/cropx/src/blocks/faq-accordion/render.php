<?php
/**
 * FAQ Accordion block — front-end render.
 *
 * Each item uses <button aria-expanded aria-controls> + <div role="region" id>
 * for proper accordion ARIA semantics. view.js manages the is-open class and
 * aria-expanded state; CSS grid-template-rows handles the height animation.
 *
 * wp_unique_id( 'faq-' ) generates a block-level prefix (e.g. 'faq-0') that
 * is unique per page, so multiple FAQ blocks get distinct ARIA ID namespaces.
 * Per-item IDs are {prefix}-{index} (e.g. 'faq-0-0', 'faq-0-1').
 *
 * Items with neither a question nor an answer are skipped.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$show_header = (bool) ( $attributes['showHeader'] ?? true );
$eyebrow     =         $attributes['eyebrow']     ?? '';
$heading     =         $attributes['heading']      ?? '';
$items       = (array) ( $attributes['items']     ?? [] );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'faq-section' ) );

// Unique prefix per block instance — keeps aria-controls / id pairs unique
// even when two or more FAQ blocks appear on the same page.
$id_prefix = wp_unique_id( 'faq-' );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_answer = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$plus_svg = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">'
          . '<line class="faq-icon-line faq-icon-line--horizontal" x1="5" y1="12" x2="19" y2="12"/>'
          . '<line class="faq-icon-line faq-icon-line--vertical" x1="12" y1="5" x2="12" y2="19"/>'
          . '</svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="faq-section-inner">

		<?php if ( $show_header && ( $eyebrow || $heading ) ) : ?>
			<div class="faq-content">
				<?php if ( $eyebrow ) : ?>
					<span class="faq-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="faq-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="faq-list">
			<?php foreach ( $items as $idx => $item ) :
				$question = $item['question'] ?? '';
				$answer   = $item['answer']   ?? '';

				if ( ! $question && ! $answer ) { continue; }

				$panel_id = $id_prefix . '-' . $idx;
			?>
				<article class="faq-item">
					<button
						class="faq-toggle"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
					>
						<span class="faq-question"><?php echo wp_kses( $question, $allowed_inline ); ?></span>
						<span class="faq-icon" aria-hidden="true">
							<?php echo $plus_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</button>
					<div
						class="faq-answer-wrap"
						id="<?php echo esc_attr( $panel_id ); ?>"
						role="region"
					>
						<div class="faq-answer">
							<div class="faq-answer-inner">
								<p><?php echo wp_kses( $answer, $allowed_answer ); ?></p>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
