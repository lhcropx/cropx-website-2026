<?php
/**
 * Taxonomy template — taxonomy-cropx_content_type.php
 *
 * Handles filtered archive views:
 *   /content-type/case-study/
 *   /content-type/white-paper/
 *
 * Delegates entirely to the publication archive template so the layout,
 * filter bar, grid, and load-more button are identical to /publications/,
 * with the active filter pill automatically highlighted via is_tax().
 */

get_template_part( 'archive', 'cropx_publication' );
