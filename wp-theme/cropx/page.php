<?php
/**
 * Page template — used for static pages built out of CropX blocks.
 *
 * The Gutenberg blocks the editor adds via the_content() are what
 * render the actual page sections. We don't add any additional chrome
 * around them — each block provides its own full-bleed section.
 */
get_header();
?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php the_content(); ?>
	</article>
<?php endwhile; endif; ?>
<?php get_footer(); ?>
