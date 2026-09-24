import { initScrollReveal } from '../../shared/scrollReveal';

/*
 * Photo Grid — front-end controller.
 *
 * This block has no other front-end interactivity of its own — the only
 * reason it needs a view.js at all is to wire up the scroll-triggered
 * reveal animation (eyebrow/heading/body/photos fading up as the section
 * scrolls into view) via the shared initScrollReveal() helper. See
 * src/shared/scrollReveal.js, and render.php for where the
 * reveal-group/reveal-up/reveal-item classes and --reveal-delay values are
 * set.
 *
 * No ".wp-block-cropx-photo-grid" ancestor prefix (Sep 2026 bugfix): this
 * block's block.json sets supports.className / customClassName to false,
 * which also stops WordPress from ever attaching the default
 * "wp-block-cropx-photo-grid" wrapper class it adds to every other block.
 * That made this selector match nothing — the reveal was silently broken
 * from the moment it shipped (caught the same day, via the identical bug
 * found on Resource Downloads, which has the same className:false setting).
 * ".pgd-section" is already unique to this block, so no namespace prefix
 * is needed.
 */
initScrollReveal( '.pgd-section' );
