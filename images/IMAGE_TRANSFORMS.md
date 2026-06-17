# Image Transformation Reference

Source image: `fronchfry.png` — 2316×2974px, sRGB, black background

All transforms were generated with Python 3 using the libraries listed per section.
A shared virtualenv at `/tmp/imgvenv` was used for all scripts.

---

## Libraries Used

| Library | Version | Purpose |
|---|---|---|
| Pillow (PIL) | via pip | Image I/O, resizing, drawing, quantization |
| numpy | via pip | Pixel array manipulation |
| scipy | via pip | Morphology (fill holes, dilation), RBF interpolation |
| OpenCV (`opencv-python-headless`) | via pip | Filtering, edge detection, Delaunay, remapping |
| mediapipe | 0.10.35 via pip | Face landmark detection (Tasks API) |

---

## 1. Silhouette — `fronchfry_silhouette.png`

**Libraries:** PIL, numpy, scipy

**Method:**
1. Load as RGB. Mark pixels as background where all channels < 10 (pure black bg).
2. `scipy.ndimage.binary_fill_holes` — fills interior gaps (e.g. dark hair that reads as background).
3. `scipy.ndimage.binary_dilation` — smooth/expand subject edge.
4. Paint subject black, background white.

**Key parameters:**
- Background threshold: `r < 10, g < 10, b < 10`
- Dilation structure: `generate_binary_structure(2, 2)` (8-connected)
- Dilation iterations: `2`
- Output: black subject on white background, RGB

---

## 2. Silhouette Circle — `fronchfry_silhouette_circle.png`

**Libraries:** PIL, numpy

**Method:**
1. Find bounding box of black subject pixels.
2. Compute centroid, expand to square with 60px padding.
3. Crop to square, then apply an ellipse mask via `ImageDraw.ellipse`.
4. Composite onto transparent RGBA canvas.

**Key parameters:**
- Padding: `60px` around subject bounding box
- Mask: filled white ellipse `(0, 0, size-1, size-1)`
- Output: RGBA with transparent exterior, 2316×2316px

---

## 3. Duotone — `fronchfry_duotone.png`

**Libraries:** PIL, numpy

**Method:**
1. Convert to grayscale, normalize to 0–1.
2. Linearly interpolate each pixel between two colors based on brightness.

**Key parameters:**
- Dark tone (shadows): `#1a1a1a`
- Light tone (highlights): `#2d6a4f` (brand accent green)
- Formula: `output = dark + (light - dark) * gray_normalized`
- Output: RGB, original dimensions

---

## 4. Ink Sketch — `fronchfry_inksketch.png`

**Libraries:** OpenCV, numpy

**Method:**
1. Gaussian blur to reduce noise.
2. Canny edge detection for linework.
3. Dilate edges slightly to thicken lines.
4. Invert edges (black lines on white).
5. Blend with a washed-out grayscale layer for mid-tone shading.

**Key parameters:**
- Gaussian blur: kernel `(5, 5)`, sigma `0`
- Canny: `threshold1=30`, `threshold2=90`
- Dilation kernel: `(2, 2)`, iterations: `1`
- Grayscale wash: `alpha=0.35`, `beta=180` via `convertScaleAbs`
- Blend: `cv2.multiply(edges, gray_wash, scale=1/255.0)`

---

## 5. Posterize — `fronchfry_posterize.png`

**Libraries:** PIL, numpy

**Method:**
1. Convert to grayscale, normalize 0–1.
2. Threshold into 4 brightness bands and map each to a brand palette color.

**Key parameters:**
- Thresholds: `0.04`, `0.20`, `0.50`, `0.75`
- Palette (dark → light):
  - `#1a1a1a` — near black
  - `#2d6a4f` — brand dark green
  - `#74c69d` — mid green
  - `#d8f3dc` — pale green

---

## 6. Halftone — `fronchfry_halftone.png`

**Libraries:** OpenCV, numpy

**Method:**
1. Convert to grayscale.
2. Iterate a regular dot grid; at each center sample the brightness.
3. Draw a filled circle whose radius is inversely proportional to brightness (dark = big dot).

**Key parameters:**
- Dot spacing: `12px`
- Max radius: `dot_spacing / 2 * 0.95`
- Radius formula: `(1.0 - brightness) * max_radius`
- Dot color: `#1a1a1a`
- Canvas: white `(255, 255, 255)`

---

## 7. Neon Glow — `fronchfry_neon.png`

**Libraries:** OpenCV, numpy

**Method:**
1. Gaussian blur then Canny edge detection.
2. Dilate edges to thicken.
3. Build two Gaussian glow layers (tight + wide bloom) tinted with brand green.
4. Add a sharp bright core on top of the glow.

**Key parameters:**
- Gaussian blur: `(5, 5)`, sigma `0`
- Canny: `threshold1=20`, `threshold2=60`
- Dilation kernel: `(3, 3)`, iterations: `2`
- Tight glow: sigma `8`, intensity `1.8`
- Wide glow: sigma `20`, intensity `1.0`
- Sharp core: sigma `1.5`, multiplier `3.0`
- Color: brand green `#2d6a4f` — channel ratios R:`45/106`, G:`1.0`, B:`79/106`
- Background: black `(0, 0, 0)`

---

## 8. Low-Poly — `fronchfry_lowpoly.png`

**Libraries:** OpenCV, numpy

**Method:**
1. Canny edge detection to find feature-rich point locations.
2. Subsample edge points + add random scatter points + fixed corner anchors.
3. Build Delaunay triangulation with `cv2.Subdiv2D`.
4. Fill each triangle with the color sampled from the original image at the triangle centroid.

**Key parameters:**
- Gaussian blur: `(5, 5)`, sigma `0`
- Canny: `threshold1=30`, `threshold2=80`
- Edge points sampled: `2500` (random subset)
- Random scatter points: `800`
- Corner anchors: `8` (corners + edge midpoints)
- Color source: original image pixel at triangle centroid
- Random seed: `42`

---

## 9. Low-Poly Brand — `fronchfry_lowpoly_brand.png`

**Libraries:** OpenCV, numpy

Same triangulation as Low-Poly above, but each triangle is filled with the nearest brand palette color based on the centroid's grayscale brightness instead of the original color.

**Key parameters:** (same triangulation as above, plus)
- Brightness thresholds: `0.04`, `0.20`, `0.50`, `0.75`
- Palette (dark → light): `#000000`, `#1a1a1a`, `#2d6a4f`, `#74c69d`, `#d8f3dc`

---

## 10. Pixel Art — `fronchfry_pixel.png`

**Libraries:** PIL, numpy

**Method:**
1. Downscale to pixel grid using LANCZOS for clean averaging.
2. Quantize to a limited color palette using median-cut.
3. Scale back up with nearest-neighbor (no interpolation) to produce hard blocks.
4. Darken every Nth row and column to draw a subtle grid.

**Key parameters:**
- Grid size: `64 × 82` pixels
- Quantize: `24 colors`, method `MEDIANCUT`
- Scale-up factor: `16×` (each pixel → 16×16 block)
- Output size: `1024 × 1312px`
- Grid line darkening: `× 0.75`

---

## 11. Pixel Art (Large Blocks) — `fronchfry_pixel_large.png`

Same method as Pixel Art above with a coarser grid.

**Key parameters:**
- Grid size: `32 × 41` pixels
- Quantize: `24 colors`, method `MEDIANCUT`
- Scale-up factor: `32×` (each pixel → 32×32 block)
- Output size: `1024 × 1312px`
- Grid line darkening: `× 0.75`

---

## 12. Pixel Art Brand — `fronchfry_pixel_brand.png`

**Libraries:** PIL, numpy

Same pipeline as Pixel Art but replaces quantization with a 5-tone brand palette mapped from grayscale brightness thresholds.

**Key parameters:**
- Grid size: `64 × 82` pixels
- Scale-up factor: `16×`
- Thresholds: `0.04`, `0.20`, `0.50`, `0.75`
- Palette: `#000000`, `#1a1a1a`, `#2d6a4f`, `#74c69d`, `#d8f3dc`
- Grid line darkening: `× 0.70`

---

## 13. Pixel Art Brand (Large Blocks) — `fronchfry_pixel_brand_large.png`

Same as Pixel Art Brand above with a coarser grid.

**Key parameters:**
- Grid size: `32 × 41` pixels
- Scale-up factor: `32×`
- Output size: `1024 × 1312px`
- (All other parameters same as Pixel Art Brand)

---

## 14. Cartoon — `fronchfry_cartoon.png`

**Libraries:** OpenCV, numpy

**Method:**
1. Repeated bilateral filtering to flatten skin/texture while preserving edges.
2. Median blur then adaptive threshold on grayscale to extract ink edges.
3. Quantize the smoothed color image into flat tone bands.
4. `bitwise_and` combines the flat colors with the edge mask (edges knock out color to black).

**Key parameters:**
- Bilateral filter: `d=9`, `sigmaColor=75`, `sigmaSpace=75`, applied `7` times
- Median blur: kernel `7`
- Adaptive threshold: `ADAPTIVE_THRESH_MEAN_C`, `THRESH_BINARY`, `blockSize=9`, `C=2`
- Color quantization bin size: `40` (i.e. `value // 40 * 40`)

---

## 15. Caricature — `fronchfry_caricature.png`

**Libraries:** mediapipe, OpenCV, numpy, scipy

**Method:**
1. Detect 478 face landmarks using mediapipe FaceLandmarker (Tasks API, float16 model).
2. Define sparse source→destination warp points for each facial feature.
3. Fit a Thin Plate Spline RBF over those points to produce a smooth full-image warp field.
4. Sample the warp field on a coarse grid, upsample to full resolution, then remap.
5. Apply cartoon filter (same as above) on the warped image.

**Face warp parameters:**
- Eye ring expansion: `1.35×` scale around each eye center
- Eye center outward push: `0.18×` horizontal, `0.10×` vertical (relative to face center offset)
- Nose tip push: `0.12×` horizontal, `0.22×` vertical
- Nose bridge narrowing: `0.85×` horizontal
- Mouth corner widening: `0.18×` per side
- Chin downward push: `0.12×`
- Border anchors: `12` fixed points (corners + edge midpoints) to prevent background distortion

**Warp technical parameters:**
- RBF kernel: `thin_plate_spline`, `smoothing=0`
- Warp map sample grid step: `8px`
- Remap interpolation: `INTER_LINEAR`
- Remap border mode: `BORDER_REPLICATE`
- Mediapipe model: `face_landmarker.task` (float16, v1)
