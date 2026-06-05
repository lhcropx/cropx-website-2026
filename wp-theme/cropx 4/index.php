<?php
/**
 * Default template — used as a fallback when a more specific template
 * isn't matched (archive, search results, etc.).
 */
get_header();
?>
<div class="entry-list">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry-summary"><?php the_excerpt(); ?></div>
		</article>
	<?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
