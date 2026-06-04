---
name: feedback-static-source-of-truth
description: Always read blocks/*.html as the authoritative design source before proposing fixes — don't reason from intent alone
metadata:
  type: feedback
---

Before proposing any CSS fix on a ported Gutenberg block, read the corresponding static source file in `blocks/` first and treat it as the authoritative design spec. Do not infer how a block is supposed to work from the bug description alone.

**Why:** In the segment-hero `sgh-device` bug, the user's description mentioned "30px bleed" which I incorrectly applied to the device PNG. The static file `blocks/segment-hero.html` was explicit: only the phone bleeds (`bottom: -30px` outside the hero), the device is clipped inside `overflow: hidden`. Reading it first would have shown the correct fix immediately — just remove `max-height: 600px` — instead of two wrong attempts.

**How to apply:** When debugging a visual bug on any block, open `blocks/<block-name>.html` in parallel with `style.css`. Check how the element is positioned in the static CSS before writing any fix. If the static file and the WP block diverge, flag it — don't assume the divergence is intentional.
