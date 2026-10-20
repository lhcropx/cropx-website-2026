import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * People Showcase — front-end controller.
 *
 * This block has no other front-end interactivity of its own — the only
 * reason it needs a view.js at all is to wire up the scroll-triggered
 * reveal animation (eyebrow/heading/body/team cards fading up as the
 * section scrolls into view) via the shared initScrollReveal() helper. See
 * src/shared/scrollReveal.js, and render.php for where the
 * reveal-group/reveal-up/reveal-item classes and --reveal-delay values are
 * set.
 *
 * Two separate selectors, not one for the whole block (Sep 2026): the intro
 * header (.section-header) and each repeatable sub-section (.people-group —
 * "Global Management Team", "Business Leads", "Board of Directors", etc. on
 * the About > Company page) are each their own observed root, so every
 * group fades in on its own as the visitor scrolls to IT specifically,
 * instead of every group animating together the instant the top of the
 * block first appears. initScrollReveal() already supports this — it
 * observes every element matched by the selector independently — so no
 * changes were needed there, just passing a selector that matches multiple
 * elements per block instance instead of just the outer wrapper.
 */
initScrollReveal( '.wp-block-cropx-people-showcase .section-header, .wp-block-cropx-people-showcase .people-group' );
