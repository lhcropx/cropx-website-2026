import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Stats Grid — front-end controller.
 *
 * This block has no other front-end interactivity of its own (the stat
 * numbers, icons, and CTA are all static markup) — the only reason it needs
 * a view.js at all is to wire up the scroll-triggered reveal animation
 * (eyebrow/heading/stat cards fading up as the section scrolls into view)
 * via the shared initScrollReveal() helper. See src/shared/scrollReveal.js,
 * and render.php for where the reveal-group/reveal-up/reveal-item classes
 * and --reveal-delay values are set.
 */
initScrollReveal( '.wp-block-cropx-stats-grid' );
