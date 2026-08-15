/**
 * Logo Carousel — front-end interaction (view.js)
 *
 * Fits each marquee on the page so a small logo set never shows its
 * duplicated copy simultaneously — see src/shared/logoMarqueeFit.js for
 * the actual measuring logic (shared with the editor canvas).
 */

import { observeLogoMarquee } from '../../shared/logoMarqueeFit';

document.querySelectorAll( '.ls-marquee' ).forEach( ( marquee ) => {
	observeLogoMarquee( marquee );
} );
