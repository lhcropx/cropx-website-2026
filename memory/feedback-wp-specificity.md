---
name: feedback-wp-specificity
description: WordPress global styles override :where() rules; use two-class selectors for properties that must land
metadata:
  type: feedback
---

All block styles use `:where(.block-class) .element` (zero specificity from `:where()`). WordPress global styles inject properties on elements/classes with specificity 0-1-1 or higher, which beats `:where()` rules silently.

**Why:** Discovered repeatedly during the Phase 2 polish pass — `width: 100%` on `.cnav-mobile-item > a` clobbered `display: inline-flex` on the login button; WP `a { background-color: transparent }` clobbered `background: var(--deep-blue)` on the mobile login button; WP list margins clobbered `margin: 0` on `.cnav-mobile-links`.

**How to apply:** Whenever a `:where()` rule isn't taking effect in the browser, add a two-class override block (e.g. `.wp-block-cropx-nav .cnav-mobile-login`) with specificity 0-2-0. This beats WP global styles reliably. Properties most commonly needing this: `display`, `width`, `padding`, `background`, `color`, `margin` on `<a>` and `<ul>` elements.
