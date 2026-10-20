# CropX hero images — AI prompt pack

Three concepts × three formats (desktop 16:9, tablet 4:3, mobile 9:16) = 9 images.
Models covered: Gemini (Nano Banana), ChatGPT Image, Nano Banana in Higgsfield, Nano Banana in Leonardo.

---

## 1. How to work (read this first)

1. **Always attach reference photos.** Vertex, Evato (or Strato), and SCIO Cup. Without them every model invents a plausible-but-wrong product. Use clean, well-lit, ideally white-background shots from 2 angles if you have them.
2. **Generate desktop first, then derive the others from it.** Once you have a desktop image you like, attach it (plus the product photo) and use the "Re-compose" prompt in section 6. This keeps lighting, product, and field identical across all three sizes, which is much more consistent than prompting each size from scratch.
3. **Ask for empty space, not cards.** Models draw garbled fake UI. Every prompt below says "no cards, no UI, no text" and describes calm zones instead. Build the glassmorphic cards in HTML/CSS over the image.
4. **Screens and labels will be gibberish.** The vineyard laptop/phone screens and the "SCIOCUP" / "X" product lettering are the likeliest spots. Plan to composite real CropX app screenshots onto the screens and fix product lettering in Photoshop/Figma, or ask for "blank, softly glowing screens" and add the UI yourself.
5. **Generate at the highest resolution the tool allows, then upscale.** Suggested final sizes: desktop 2560×1440, tablet 2048×1536, mobile 1080×1920 (or 1440×2560 for retina).
6. **Safe zone for cropping.** Keep key subjects (sensor, laptop, cup, satellite) away from the outer 8% of the frame so object-fit: cover crops don't cut them.

### Per-model notes

| Model | Aspect ratio | Reference images | Tips |
|---|---|---|---|
| **Gemini (Nano Banana)** | Say the ratio in the prompt ("16:9 landscape") or pick it in the UI | Attach several; label them in the prompt ("Image 1 is the Vertex sensor") | Best at "keep this subject exactly, change the scene." Iterate conversationally ("move the sensor left, make the soil darker"). |
| **ChatGPT Image** | Native sizes are roughly 3:2 landscape, 2:3 portrait, 1:1. It may not give true 16:9, 4:3, or 9:16 | Attach and refer to "the attached photo" | Generate 3:2 and crop to 16:9 (leave extra sky/foreground top and bottom), 2:3 and crop to 9:16. For tablet, ask for 3:2 and crop to 4:3. Strong prompt-following on layout zones. |
| **Nano Banana in Higgsfield** | Pick ratio in the UI (16:9, 4:3, 9:16 are usually offered) | Upload to the reference/image slots | Same model family as Gemini, so the same prompts work. Check how many reference slots you get. |
| **Nano Banana in Leonardo** | Pick ratio in the UI | Use the image-guidance / reference slot | Same prompts work. If it exposes a "prompt enhance" toggle, turn it off so it doesn't add text or UI. |

UI options change often, so treat the above as the general pattern rather than exact menus.

---

## 2. Shared style block (paste at the start of every prompt)

> Photorealistic commercial photograph for a premium agtech website hero, shot on a full-frame camera, natural light, realistic materials, shallow-to-medium depth of field, rich but natural color grading (deep blues, healthy greens, warm gold light), crisp focus on the hero subject, soft atmospheric depth in the background. High-end, modern, trustworthy. No text, no logos, no watermarks, no UI panels, no cards, no captions, no infographics, no icons floating in the air, no people unless specified.

## 3. Shared negative / constraint line (append to every prompt)

> Do not add any text, lettering, numbers, charts, graphs, interface elements, or glass panels anywhere in the image. Leave the specified empty zones clean and uncluttered. Keep the product exactly as shown in the reference photo (shape, proportions, colors, materials) and do not invent extra products.

---

## 4. Concept A — Vertex in the soil + satellite NDVI over soybeans

**Reference to attach:** Vertex sensor photo.

**Scene description (reuse in all three formats):**
A single Vertex soil sensor installed vertically in the ground, shown in a clean soil cross-section (a "cutaway" view): the upper part of the sensor is visible just above the surface, the probe body continues down through layered dark, moist soil with visible texture, roots, and fine moisture detail. Above the soil line, a soybean field in mid-season stretches toward the horizon in rows. Over the field, a translucent NDVI vegetation-index map is draped across the crop like a thin digital heat map (healthy dark green, through light green and yellow, to a few orange/red stress patches), following the row geometry and the field's perspective. High in the sky, a small realistic Earth-observation satellite casts a soft, glowing cone of light-blue light down onto the field, as if scanning it. Soft, glowing blue concentric rings at the sensor tip suggest data transmission, subtle and elegant. Golden-hour sunrise light, hazy mountains or tree line on the horizon.

### A1. Desktop (16:9)

> [Shared style block] Attach: Image 1 = Vertex soil sensor, keep it exactly as shown. 16:9 landscape. [Scene description]
> **Composition:** the left 40% of the frame is calm and low-detail (soft sky and distant out-of-focus field) so a headline can sit there. The satellite is in the upper right, with its light cone sweeping down and left across the field. The soil cross-section occupies the lower right 55% of the frame, with the Vertex sensor placed roughly 70% from the left edge, standing vertically, its top just above ground. Leave two clean, uncluttered open areas for overlay cards: one in the middle-right of the field area (above the sensor) and one in the upper-middle sky. Only one sensor in the image.
> [Shared constraint line]

### A2. Tablet (4:3)

> [Shared style block] Attach: Image 1 = Vertex soil sensor, keep it exactly as shown. 4:3 landscape. [Scene description]
> **Composition:** more vertical than the desktop version. The top 25% is calm sky with the satellite in the upper right and its light cone coming down. The middle 35% is the NDVI-draped soybean field in perspective, with a clean open area on the left half for overlay cards. The bottom 40% is the soil cross-section with the Vertex sensor centered at about 60% from the left, vertical, with the top just above ground. Single sensor only.
> [Shared constraint line]

### A3. Mobile (9:16)

> [Shared style block] Attach: Image 1 = Vertex soil sensor, keep it exactly as shown. 9:16 portrait. [Scene description]
> **Composition:** top 28% is calm, simple dawn sky (clean area for headline), with the satellite small in the upper right and its beam angling down and left. The middle 32% is the NDVI-draped soybean field receding into the distance, with a wide clean area across the left two-thirds for a card. The lower 40% is the soil cross-section, with the single Vertex sensor centered, vertical, large and sharp, its top just above ground and glowing rings at the tip. Leave the bottom 8% simple and dark so a carousel control can sit there.
> [Shared constraint line]

---

## 5. Concept B — Evato (or Strato) in a vineyard with laptop + phone

**Which sensor?** I'd start with **Evato**: the white enclosure, small solar panel, black pole, and horizontal wind-sensor arm read clearly as a "weather station" and match the middle phone in your example. Strato will work too, but attach its own photo and swap the name in the prompt.

**Reference to attach:** Evato (or Strato) photo. Optional second reference: a screenshot of the CropX app on laptop and phone, to guide screen shape.

**Scene description (reuse in all three formats):**
A late-afternoon vineyard, with trellised grapevines in neat rows leading toward distant hills, warm golden-hour light, soft haze. In the mid-ground, the weather/evapotranspiration sensor is installed on a black pole in the vine rows, its white enclosure and small solar panel clearly visible. In the foreground, a laptop and a smartphone sit on a rustic wooden surface (a vineyard crate or table), angled toward the camera. The screens show a clean modern farm-management dashboard with a field map in green/yellow/red and soft data panels, but all text is blurred or abstract, nothing legible. A few extremely faint, thin blue connection arcs run from the sensor toward the devices (subtle, not cartoonish).

### B1. Desktop (16:9)

> [Shared style block] Attach: Image 1 = Evato sensor, keep it exactly as shown. 16:9 landscape. [Scene description]
> **Composition:** the left 40% is calm vineyard rows and soft sky for a headline. The sensor is placed right of center, about 65% from the left, upper two-thirds of the frame, in the vine rows. The laptop and phone sit in the lower right foreground, no more than 40% of the frame width, not cropped by the edges. Leave two clean open areas for overlay cards: one above the laptop on the right and one to the left of the sensor in the vine rows.
> [Shared constraint line]

### B2. Tablet (4:3)

> [Shared style block] Attach: Image 1 = Evato sensor, keep it exactly as shown. 4:3 landscape. [Scene description]
> **Composition:** the sensor stands in the upper-middle of the frame, slightly right of center, with vine rows receding behind it. The laptop and phone fill the lower 35%, centered, fully visible. A clean open area in the upper left sky/vines and another between the sensor and the laptop for overlay cards.
> [Shared constraint line]

### B3. Mobile (9:16)

> [Shared style block] Attach: Image 1 = Evato sensor, keep it exactly as shown. 9:16 portrait. [Scene description]
> **Composition:** top 28% calm golden sky with distant hills (clean area for headline). The sensor stands tall on the right side of the frame in the upper-middle, about 55–60% down from the top edge to its base, with vine rows converging behind it. Two clean open areas on the left side of the middle section for stacked overlay cards. The laptop and smartphone fill the bottom 35%, laptop on the left and phone on the right overlapping slightly, both fully in frame and angled toward the viewer, on a wooden surface. Keep the bottom 8% simple and dark.
> [Shared constraint line]

---

## 6. Concept C — SCIO Cup with feed corn, held by an agronomist

**Reference to attach:** SCIO Cup photo (two angles if you have them; the cup's black body, side handle, and base button are what the model needs to preserve).

**Scene description (reuse in all three formats):**
A dry, late-season feed-corn field at golden hour, with tall, tan and straw-colored stalks and drooping leaves, warm backlight with a soft haze. In the foreground, an agronomist (shown from the chest down, or turned three-quarters away from camera so the face isn't the focus) wears a casual field shirt and holds the black SCIO cup in one hand, with the other hand steadying it. The cup is filled to the brim with yellow dent/feed-corn kernels, which are visible and sharp. Only the SCIO cup appears. Do not show any handheld scanner or other device. A very subtle, thin blue glow or light shimmer on the surface of the kernels may suggest scanning, kept faint and natural.

### C1. Desktop (16:9)

> [Shared style block] Attach: Image 1 = SCIO cup, keep it exactly as shown. 16:9 landscape. [Scene description] People are allowed in this image.
> **Composition:** the left 40% is soft, out-of-focus dry corn field with warm light (clean area for headline). The agronomist and cup sit on the right 45% of the frame, the cup at roughly 70% from the left and about 60% down, large, sharp, and fully visible. Leave two clean open areas for overlay cards: one in the upper-middle of the frame above the cup, and one in the lower left.
> [Shared constraint line]

### C2. Tablet (4:3)

> [Shared style block] Attach: Image 1 = SCIO cup, keep it exactly as shown. 4:3 landscape. [Scene description] People are allowed in this image.
> **Composition:** the agronomist and cup are right of center and in the lower 60% of the frame, cup fully visible and sharp. The upper left 40% × 40% is a clean, soft, out-of-focus corn field and sky for overlay cards.
> [Shared constraint line]

### C3. Mobile (9:16)

> [Shared style block] Attach: Image 1 = SCIO cup, keep it exactly as shown. 9:16 portrait. [Scene description] People are allowed in this image.
> **Composition:** top 28% is calm golden sky and tall corn tops (clean area for headline). The middle section has a wide clean area on the left two-thirds for an overlay card. The cup, held by the agronomist, is in the lower-middle, centered slightly right, large and sharp, with kernels clearly visible. The bottom 8% is simple and dark.
> [Shared constraint line]

---

## 7. Re-compose prompt (use after you have a good desktop image)

Attach the approved desktop image and the product reference photo, then:

> Image 1 is the approved desktop hero. Image 2 is the product reference. Recreate exactly this scene (same product, same field, same light, same color grade, same time of day) as a [4:3 landscape / 9:16 portrait] image. Re-compose rather than crop: [paste the matching composition paragraph from sections 4-6 for that format]. Do not add any text, UI, or glass panels. Keep the product exactly as in Image 2.

If a model keeps the original framing, say: "Extend the scene and re-frame it. Don't just crop the original."

## 8. Fix-it one-liners

- Garbled text on screens: "Make the screens blank, softly glowing, with no text."
- Extra sensors appear: "There is exactly one sensor. Remove all others."
- Product looks wrong: "Match the product exactly to the reference photo, same shape, proportions, and colors."
- Fake UI/cards appear: "Remove all floating panels, charts, icons, and text. Keep only the photographic scene."
- Too busy behind the headline zone: "Make the [left 40% / top 28%] much calmer and lower in detail, with soft blur and low contrast."
- NDVI looks cartoonish: "Make the NDVI overlay thin, semi-transparent, and conforming to the crop rows, like a satellite analytics layer, not a painted-on gradient."
- Satellite looks like a toy: "Make the satellite a realistic small Earth-observation satellite with solar panels, in sharp focus, seen from below."

## 9. Open assumptions

- I assumed the hero headline sits on the **left** at desktop and **top** on mobile, with cards in the open zones. If the hero text is on the right, say "mirror the composition left-to-right" in the prompt.
- I don't have the final hero block dimensions. 16:9 / 4:3 / 9:16 follow your answer, and the 8% safe margin is meant to cover the difference. Once we change the hero block we can tune the ratios.
