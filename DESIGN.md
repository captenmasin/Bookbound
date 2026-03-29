# Design System Strategy: The Digital Archivist

## 1. Overview & Creative North Star
The Creative North Star for this design system is **"The Curated Folio."** 

Moving away from the rigid, boxy constraints of standard web interfaces, this system treats the screen as an intentional editorial layout—a physical desk where rare manuscripts are preserved and displayed. We achieve a "tidier" look not through more lines, but through **asymmetric balance** and **rhythmic whitespace**. 

By utilizing the `Noto Serif` for authoritative headers and `Newsreader` for immersive reading, we bridge the gap between a historical archive and a high-end digital experience. The layout avoids the "template" look by using overlapping surface layers and high-contrast typographic scales to guide the eye through the scholarly narrative.

---

## 2. Colors & Surface Architecture
The palette is anchored by a deep, academic rust (`primary: #6d2400`) and grounded in warm, paper-like neutrals (`surface: #fef9f2`).

### The "No-Line" Rule
To maintain the sophisticated "Archivist" aesthetic, **1px solid borders are strictly prohibited for sectioning.** Boundaries must be defined through:
- **Tonal Shifts:** Placing a `surface-container-low` section against a `surface` background.
- **Negative Space:** Using the `8` (2.75rem) or `12` (4rem) spacing tokens to create mental boundaries without visual clutter.

### Surface Hierarchy & Nesting
Treat the UI as a series of stacked parchment sheets. Use the `surface-container` tiers to create depth:
1.  **Base Layer:** `surface` (#fef9f2) for the main background.
2.  **Primary Content Areas:** `surface-container-low` (#f8f3ec) to subtly group related metadata.
3.  **Interactive Elements:** `surface-container-highest` (#e7e2db) for elevated functional blocks.

### The "Glass & Gradient" Rule
To avoid a flat, "dated" feel, floating elements (like persistent search bars or quick-access navigation) should use **Glassmorphism**. Apply `surface-container-lowest` with a 85% opacity and a `backdrop-blur`. 
- **Signature Polish:** Use a subtle linear gradient on main CTAs, transitioning from `primary` (#6d2400) to `primary_container` (#913608) at a 135-degree angle. This provides a "bound leather" depth that flat hex codes cannot replicate.

---

## 3. Typography
The typography is the voice of the Archivist. It must feel both timeless and highly legible.

*   **Display (Noto Serif):** Use `display-lg` for landing hero moments. Set with tight letter-spacing (-0.02em) to feel like a premium masthead.
*   **Headlines (Noto Serif):** `headline-md` and `headline-sm` serve as chapter markers. These should always have generous top-margin (`spacing-12`) to allow the subject matter to breathe.
*   **Body (Newsreader):** The transition to `newsreader` for `body-lg` and `body-md` is intentional. Newsreader’s x-height is optimized for long-form scholarly reading.
*   **Labels (Work Sans):** Functional metadata (dates, filing numbers, tags) uses `work-sans`. This sans-serif contrast prevents the design from feeling overly "antique" and ensures modern utility.

---

## 4. Elevation & Depth
Depth in this system is organic, mimicking light falling on paper rather than digital shadows.

*   **The Layering Principle:** Avoid shadows for static content. Create "lift" by nesting `surface-container-lowest` cards inside a `surface-container-high` section.
*   **Ambient Shadows:** For high-priority floating modals, use a "Whisper Shadow": `0px 20px 40px rgba(29, 28, 23, 0.06)`. The tint uses `on-surface` (#1d1c17) rather than pure black to keep the warmth of the palette.
*   **The "Ghost Border" Fallback:** If accessibility requires a stroke (e.g., in high-contrast modes), use `outline-variant` at **15% opacity**. Never use a 100% opaque border.

---

## 5. Components

### Buttons & CTAs
*   **Primary:** Filled with the signature `primary` to `primary_container` gradient. Use `rounded-sm` (0.125rem) to maintain a crisp, formal edge.
*   **Secondary:** Ghost style with `primary` text. No border—interaction is signaled by a `surface-container-highest` background shift on hover.

### Inputs & Search
*   **The Scholar’s Input:** Text fields should not be boxes. Use a `surface-container-low` background with a `primary` bottom-bar (2px) that expands on focus. Use `label-md` for floating labels.

### Cards & Collections
*   **Forbid Dividers:** Do not use lines to separate archive entries. Use `spacing-6` (2rem) of vertical whitespace. 
*   **The "Folio" Card:** Use `surface-container-lowest` with a very slight `rounded-md` corner. Images should have a 1px `inset` shadow to make them appear "pressed" into the paper.

### Additional Archive Components
*   **The "Provenance" Tag:** A small `Work Sans` label in `tertiary` (#003d73) to denote the origin of a digital artifact, set in a `secondary_container` pill.
*   **The Manuscript Zoom:** A glassmorphic floating controller using `surface-bright` and `backdrop-blur` for high-resolution image inspection.

---

## 6. Do's and Don'ts

### Do:
*   **Embrace Asymmetry:** Align a headline to the left but push the body text to a central 60% column to mimic an editorial manuscript.
*   **Use Tonal Transitions:** Shift background colors from `surface` to `surface-container-low` to signal a change in content type.
*   **Respect the Serif:** Allow `Noto Serif` the space it needs. It is the primary visual "anchor."

### Don't:
*   **Don't use "Web Blue":** All links and actions must stay within the `primary` (rust) or `tertiary` (deep sea) spectrum.
*   **Don't use Heavy Shadows:** If the shadow is the first thing you notice, it is too dark. It should feel like an "ambient glow."
*   **Don't use Grid Borders:** Avoid the "Table" look. If you have a list of data, use alternating row fills (`surface` vs `surface-container-lowest`) rather than horizontal lines.