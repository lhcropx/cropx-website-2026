/**
 * Match a wp-block-image figure's rendered width to its photo, so a
 * figcaption below a portrait/narrower image can't stretch wider than the
 * photo itself.
 *
 * Why this is JS and not CSS: this looks like a classic "shrink a box to
 * its widest child" job (width: fit-content, or a CSS Grid min-content
 * column), and both were tried first. Both failed for real-world captions,
 * confirmed by live testing on staging (Aug 2026):
 *
 *   - width: fit-content shrinks the figure to whatever its widest CHILD
 *     prefers, and a figcaption's "preferred" width is its full text with
 *     zero line breaks — for any real, sentence-length caption that's
 *     usually wider than the photo, so fit-content just landed back on the
 *     full column width (the original bug).
 *   - grid-template-columns: minmax(0, min-content) sizes the column by
 *     the largest MIN-CONTENT contribution among its children. That
 *     sounds right (an <img> can't wrap, so its min-content should equal
 *     its real rendered width) but Chromium resolves the img's own
 *     max-width: 100% — a percentage — as effectively 0 during this
 *     specific intrinsic-size calculation (a known circularity in how
 *     percentages interact with grid intrinsic track sizing). Verified
 *     directly on staging: hiding the figcaption made the image's
 *     computed width collapse to 0px, proving the "column" was being
 *     sized by the caption's narrowest word the whole time, not the photo.
 *
 * Both approaches fail because they ask the browser to *infer* the photo's
 * width from intrinsic-sizing rules that don't behave the way they look
 * like they should once a percentage max-width and a manually-resized
 * (fixed pixel width) image are both in play. Measuring the image's actual
 * rendered box directly and applying that as the figure's width sidesteps
 * the inference entirely — it's the width, because we measured it.
 */
( function () {
	function candidateFigures() {
		return document.querySelectorAll(
			'.wp-block-image:not(.alignfull):not(.alignwide):not(.alignleft):not(.alignright)'
		);
	}

	function syncOne( figure ) {
		var img = figure.querySelector( 'img' );
		var caption = figure.querySelector( 'figcaption' );
		// Nothing to protect against if there's no caption to overflow.
		if ( ! img || ! caption ) {
			return null;
		}

		function apply() {
			var width = img.getBoundingClientRect().width;
			if ( width > 0 ) {
				figure.style.width = width + 'px';
			}
		}

		if ( img.complete ) {
			apply();
		} else {
			img.addEventListener( 'load', apply, { once: true } );
		}

		return apply;
	}

	function init() {
		var appliers = [];
		candidateFigures().forEach( function ( figure ) {
			var applier = syncOne( figure );
			if ( applier ) {
				appliers.push( applier );
			}
		} );

		if ( ! appliers.length ) {
			return;
		}

		// The figure's width has to track the image's rendered width across
		// breakpoints too (the image can shrink further on narrow screens
		// via its own max-width: 100%) — re-measure on resize.
		var resizeHandle;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeHandle );
			resizeHandle = setTimeout( function () {
				appliers.forEach( function ( applier ) {
					applier();
				} );
			}, 150 );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
