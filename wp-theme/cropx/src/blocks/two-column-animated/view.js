/**
 * 2 Columns with Text & Animated Image — front-end interaction (view.js)
 *
 * The .tcap-visual column already runs its own continuous CSS-only
 * crossfade/rotation animation (see render.php) and is left untouched here.
 * This file only wires up the shared scroll-reveal animation (see
 * src/shared/scrollReveal.js) for the .tcap-content text column.
 */

import { initScrollReveal } from '../../shared/scrollReveal';

initScrollReveal( '.tcap-section' );
