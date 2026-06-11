# LineUp Aligner — Responsive Media Guide

Designer reference for every image, video, placeholder, and hero size across the public website, admin CMS, and doctor portal.

**How to use this document**

| Column | Meaning |
|--------|---------|
| **Display size** | How the asset appears on screen (from CSS) |
| **Recommended upload** | Master file size to export (safe crop / scale-down) |
| **Aspect ratio** | Crop target; `object-fit: cover` crops edges on most cards |
| **Max file** | Server upload limit (Laravel `max:` is in **KB**) |
| **CMS field** | Where admins upload in **Website** or **Settings** |

> **No pixel validation on upload** — the server only checks file type and size. Export at the recommended dimensions so crops look correct on all breakpoints.

---

## Table of Contents

1. [Responsive breakpoints](#1-responsive-breakpoints)
2. [Global branding & logos](#2-global-branding--logos)
3. [Public website — heroes](#3-public-website--heroes)
4. [Public website — homepage sections](#4-public-website--homepage-sections)
5. [Public website — inner pages](#5-public-website--inner-pages)
6. [Public website — blog](#6-public-website--blog)
7. [Public website — case studies / portfolio](#7-public-website--case-studies--portfolio)
8. [Public website — services / features](#8-public-website--services--features)
9. [LineUp placeholder template](#9-lineup-placeholder-template)
10. [Smiliz template reference assets](#10-smiliz-template-reference-assets)
11. [Static Smiliz sections (not CMS)](#11-static-smiliz-sections-not-cms)
12. [Doctor & admin portal](#12-doctor--admin-portal)
13. [Patient case media](#13-patient-case-media)
14. [3D scans & viewer](#14-3d-scans--viewer)
15. [Admin CMS upload limits](#15-admin-cms-upload-limits)
16. [Designer cheat sheet](#16-designer-cheat-sheet)
17. [Source files index](#17-source-files-index)

---

## 1. Responsive breakpoints

The site uses these main breakpoints (width = viewport):

| Breakpoint | Typical devices | Notes |
|------------|-----------------|-------|
| **≤767px** | Mobile phones | Hero shorter; case carousel full-width; scan files below viewer |
| **≤991px** | Tablets | Why LINEUP grid → 2 columns; toolbar wraps |
| **≤1200px** | Small laptops | Mobile nav drawer; header logo smaller |
| **≥1201px** | Desktop | Full Smiliz layouts |

**Container max width:** ~1140–1320px (Bootstrap/Smiliz `.container`).

---

## 2. Global branding & logos

Uploaded in **Settings → Branding** (`logo`, `project_name`).

| Location | Display size | Recommended upload | Aspect ratio | Max file | Notes |
|----------|--------------|-------------------|--------------|----------|-------|
| **Public site header** (Smiliz) | `max-height: 96px` desktop → `56px` mobile; `max-width: min(220px, 52vw)` | PNG or **SVG** on transparent; width ≥ **240px** | Flexible horizontal | **2 MB** | `lineup-smiliz-overrides.css` |
| **Public site footer** (Smiliz) | `height: 60px` | PNG/SVG ~**240×60** | ~4:1 | **5 MB** (footer CMS) or branding logo | Footer can override via **Website → Navigation → Footer image** |
| **Admin top bar** | **36×36px** circle, `object-fit: cover` | Square ≥ **72×72** | 1:1 | **2 MB** | `lineup-topbar.css` |
| **Login page** | `max-width: 220px`, `max-height: 88px`, `object-fit: contain` | Horizontal logo | Flexible | **2 MB** | `login-page.css` |
| **Settings preview** | `max-height: 100px`, `max-width: 200px` | Same as logo | Flexible | **2 MB** | `settings-layout.css` |
| **Email / notifications** | Height ~40–60px | Same branding logo | Flexible | — | Pulled from settings |

**Formats:** JPEG, JPG, PNG, **SVG**, WebP (logo only in settings).

---

## 3. Public website — heroes

CMS: **Website → Hero** (`panel-hero.blade.php`).

### 3a. Homepage 1 — video hero (default)

| Property | Value |
|----------|-------|
| **CMS field** | `hero_video` |
| **Formats** | MP4, WebM |
| **Max file** | **50 MB** (51,200 KB) |
| **Container height** | Desktop: `min(82vh, 760px)`, min **500px** · Mobile: **72vh**, min **440px** |
| **Video rendering** | `width/height: 100%`, `object-fit: cover` (full bleed) |
| **Border radius** | 48px on hero block |
| **Recommended master** | **1920×1080** (16:9) or **1920×800** (2.4:1) landscape |
| **Safe zone** | Keep text/CTA area clear on the **left 55%** (dark gradient overlay) |
| **Default asset** | `storage/.../videos/primecare-video.mp4` |

### 3b. Homepage 2 — image slider

| Property | Value |
|----------|-------|
| **CMS field** | `slides[n].image_file` |
| **Formats** | JPEG, JPG, PNG, WebP |
| **Max file** | **5 MB** per slide |
| **Slide container** | Slider 01: height **845px** · Slider 02: height **1000px** |
| **Background** | `background-size: cover`, centered |
| **Template reference** | `slider-01-slide1.jpg` = **1920×844** (~2.27:1) |
| **Recommended upload** | **1920×844** or **1920×900** landscape |

### 3c. Hero side image (optional)

| Property | Value |
|----------|-------|
| **CMS field** | `hero_image` |
| **Max file** | **4 MB** |
| **Smiliz HP1 video layout** | Not shown in video hero (used for SEO meta) |
| **LineUp placeholder template** | `width: 100%`, ~50% grid column, `border-radius: 16px` |
| **Recommended upload** | **1200×900** (4:3) or **1000×1000** square |

---

## 4. Public website — homepage sections

### 4a. About section

| Property | Value |
|----------|-------|
| **CMS field** | `about_image` |
| **Max file** | **4 MB** |
| **Homepage 1 display** | `aspect-ratio: 4/3`, `min-height: 260px` (mobile) → **420px** (custom photo) |
| **Homepage 2 display** | Masked center image (`mask-img` + `masking.png`) |
| **Rendering** | `background-size: cover` (HP1) or masked PNG (HP2) |
| **Template reference** | Decorative PNG **940×642** · Mask photo **635×609** · Fallback JPG **698×546** |
| **Recommended upload** | **1200×900** (4:3) for HP1 · **800×800** square, subject centered, for HP2 mask |
| **Admin hint** | *"Large photo on the right (HP1) or center image (HP2)"* |

### 4b. Why LINEUP / platform feature cards

| Property | Value |
|----------|-------|
| **CMS field** | `features[n].image_file` |
| **Max file** | **5 MB** |
| **Display** | `aspect-ratio: 16/10`, `object-fit: cover` |
| **Grid** | 3 columns desktop → 2 tablet → 1 mobile |
| **Template reference** | `service-01.jpg` = **585×585** (legacy circle layout) |
| **Recommended upload** | **1280×800** (16:10) |

### 4c. Case results carousel (homepage)

| Property | Value |
|----------|-------|
| **CMS** | **Website → Portfolio / Showcases** (`before_image`, `after_image`) |
| **Desktop display** | Each image **300×310px**, `object-fit: cover` |
| **Mobile display** | `width: 100%`, `min-height: 220px`, `max-height: 300px` |
| **Tablet (≤1199px)** | Carousel cell height **350px** |
| **Template reference** | **1400×650** (~2.15:1) before/after pairs |
| **Recommended upload** | **1400×650** — keep before/after same dimensions and alignment |
| **Max file** | **5 MB** each |

### 4d. How it works — process steps

| Property | Value |
|----------|-------|
| **CMS field** | `process_steps[n].image_file` |
| **Max file** | **5 MB** |
| **Default assets** | SVG illustrations (`assets/website/process/step-*.svg`) |
| **If replacing with raster** | UI screenshots work well at **1280×800** (16:10) |
| **Admin note** | Screenshots shared across EN/AR |

### 4e. Stats, FAQ, CTA, partner blocks

| Section | Media |
|---------|-------|
| Stats | Text only — no image upload |
| FAQ | Text only |
| CTA banner | CSS background from Smiliz assets (static) |
| Partner quote panel | Text only; decorative BG from theme |

### 4f. Treatable cases (disabled by default)

| Property | Value |
|----------|-------|
| **CMS field** | `treatable_items[n].image_file` |
| **Max file** | **5 MB** |
| **Display** | Tall tab panel, `background-size: cover`, `border-radius: 50px` |
| **Template reference** | **445×529** |
| **Recommended upload** | **900×1060** (~portrait) |

Enable in `config/website.php` → `sections.treatable_cases`.

---

## 5. Public website — inner pages

### 5a. Title bar / page banner

Used on About, Services, Blog, FAQ, Contact, and other inner pages.

| Property | Value |
|----------|-------|
| **CMS field** | `titlebar_image` (**Website → Contact & SEO**) |
| **Max file** | **5 MB** |
| **Display** | `min-height: 450px`, `background-size: cover`, centered |
| **Admin hint** | *"Wide landscape photo works best (about **1920×450px**)"* |
| **Template reference** | `titlebar-bg.jpg` = **1920×550** |
| **Recommended upload** | **1920×450** to **1920×550** (~3.5:1 to 4:1) |
| **Safe zone** | Center area for page title + breadcrumb (avoid busy detail in vertical center) |

### 5b. Footer image

| Property | Value |
|----------|-------|
| **CMS field** | `footer_image` (**Website → Navigation**) |
| **Max file** | **5 MB** |
| **Display** | `height: 60px` in footer column |
| **Admin hint** | *"PNG or SVG-friendly logo on transparent background"* |
| **Recommended upload** | **240×60** to **300×80** horizontal logo |

---

## 6. Public website — blog

CMS: **Website → Blog**.

### 6a. Blog card (homepage + listing)

| Property | Value |
|----------|-------|
| **CMS field** | `blog_posts[n].image_file` |
| **Max file** | **5 MB** |
| **Display** | `aspect-ratio: 16/11`, `object-fit: cover`, `border-radius: 30px` |
| **Template reference** | `blog-img-01.jpg` **1630×1001** · Listing thumbs **890×540** |
| **Recommended upload** | **1280×880** or **1630×1000** (16:11) |

### 6b. Blog article detail

| Property | Value |
|----------|-------|
| **CMS field** | `blog_posts[n].detail.image_file` |
| **Max file** | **5 MB** |
| **Display** | Full content width, `border-radius: 20px` |
| **Recommended upload** | **1280×800** or match card image at higher res |

### 6c. Blog sidebar recent posts

| Property | Value |
|----------|-------|
| **Display** | **90px** wide link area; image cropped **circular** |
| **Source** | Same as post featured image |

---

## 7. Public website — case studies / portfolio

CMS: **Website → Case studies** + per-showcase CRUD.

### 7a. Showcase before / after (carousel + listing)

| Property | Value |
|----------|-------|
| **CMS fields** | `before_image`, `after_image` |
| **Max file** | **5 MB** each |
| **Homepage carousel** | See [§4c](#4c-case-results-carousel-homepage) |
| **Listing cards** | Same image pair, responsive width |

### 7b. Case study detail — before/after slider

| Property | Value |
|----------|-------|
| **Display** | `width: 100%`, `max-height: min(72vh, 640px)`, `object-fit: cover` |
| **Border radius** | 20px |
| **Recommended upload** | **1400×650** — identical framing for before & after |

### 7c. Case study detail — extra photos

| Property | Value |
|----------|-------|
| **CMS fields** | `detail_image1_file`, `detail_image2_file` |
| **Max file** | **5 MB** each |
| **Display** | Full column width, `border-radius: 10px` |
| **Template reference** | **445×416** (~1.07:1) |
| **Recommended upload** | **900×840** |

---

## 8. Public website — services / features

Each **Why LINEUP** card can expand to a full **service detail page** (`/services/{slug}`).

### 8a. Service detail hero image

| Property | Value |
|----------|-------|
| **CMS field** | `features[n].detail.image_file` or `service_page_image_file` |
| **Max file** | **5 MB** |
| **Display** | `width: 100%`, `border-radius: 20px` |
| **Optional video block** | `aspect-ratio: 2.333` (~21:9), `object-fit: cover` |
| **Template reference** | `service-single-01.jpg` = **1000×615** (~1.62:1) |
| **Recommended upload** | **1000×615** or **1280×800** |

### 8b. Service page shared image

| Property | Value |
|----------|-------|
| **CMS field** | `service_page_image_file` |
| **Max file** | **5 MB** |
| **Use** | Default hero for generic services landing if configured |

---

## 9. LineUp placeholder template

Lightweight alternate homepage (`config/website.php` → `lineup-placeholder`). Uses same CMS images where noted.

| Section | Display | Source field | Recommended |
|---------|---------|--------------|-------------|
| Nav logo | **40×40** | Settings logo | SVG/PNG ≥ 80px |
| Hero image | `width: 100%`, 50% column, `border-radius: 16px` | `hero_image` | **1200×900** |
| Hero mockup | CSS-only bars (**36px**, cards **48px**) | — | — |
| Case before/after | `aspect-ratio: 4/5`, `object-fit: cover` | Showcase images | **800×1000** (4:5) |
| Footer logo | **32×32** | Settings logo | Small square mark |

**CSS file:** `assets/css/lineup-public-website.css`

---

## 10. Smiliz template reference assets

Native pixel dimensions of bundled demo files under `assets/smiliz/images/`. Use as crop targets when matching the theme.

| Role | Path (under `assets/smiliz/images/`) | Pixels | Ratio |
|------|--------------------------------------|--------|-------|
| Hero slider slide | `banner-slider-img/slider-01-slide1.jpg` | **1920×844** | ~2.27:1 |
| Title bar | `bg/titlebar-bg.jpg` | **1920×550** | ~3.5:1 |
| About decorative | `homepage-1/bg/about-bg1.png` | **940×642** | ~1.46:1 |
| About masked (HP2) | `homepage-2/mask-img.png` | **635×609** | ~1:1 |
| Service card | `homepage-1/service/service-01.jpg` | **585×585** | 1:1 |
| Blog featured | `blog/blog-img-01.jpg` | **770×492** | ~1.56:1 |
| Blog listing | `homepage-2/blog/blog-01.jpg` | **890×540** | ~1.65:1 |
| Before/after | `homepage-2/portfolio/before-img-01.jpg` | **1400×650** | ~2.15:1 |
| Case detail extra | `portfolio/portfolio-detail-01.jpg` | **445×416** | ~1.07:1 |
| Treatable tab | `homepage-1/tab/tab-img-01.jpg` | **445×529** | ~0.84:1 |
| Service detail | `service/service-single-01.jpg` | **1000×615** | ~1.62:1 |
| Team card | `team/team-01.jpg` | **500×500** | 1:1 |
| Testimonial avatar | `testimonial/review-01.jpg` | **300×300** (shows **70×70**) | 1:1 |
| Client logo | `client/client-logo-01.png` | **150×50** | ~3:1 |
| Appointment BG (HP2) | `homepage-2/bg/appoinment-bg.jpg` | **1920×990** | ~1.94:1 |

---

## 11. Static Smiliz sections (not CMS)

These sections exist in the Smiliz HTML theme but are **not wired to the LineUp CMS**. They use static assets unless you extend the admin.

| Section | Display | Template asset size |
|---------|---------|---------------------|
| Team grid | `width: 100%`, circular crop | **500×500** |
| Team single hero | Full column, `border-radius: 20px` | Flexible |
| Testimonials style 1 | Avatar **70×70** circle | **300×300** source |
| Client / partner logos | Grid cell, grayscale filter | **150×50** typical |
| Decorative shapes | Various PNG overlays | See `assets/smiliz/images/` |

---

## 12. Doctor & admin portal

### 12a. Profile photos

| User | Display | Recommended upload | Max file | CMS / route |
|------|---------|-------------------|----------|-------------|
| **Admin** | **96×96** circle, `object-fit: cover` | **400×400** | **2 MB** | Settings → Profile |
| **Doctor** | **72×72** circle, `object-fit: cover` | **400×400** | **2 MB** | Profile |
| **Case chat** | **40×40** circle | Uses user photo or logo | — | Auto |
| **Patients list** | **40×40** circle | First case photo | — | Auto |

### 12b. Admin website CMS previews

Not public-facing — helps admins recognize uploads.

| Preview type | Size |
|--------------|------|
| Standard image preview | **120×80**, `object-fit: cover` |
| Media card thumbnail | **16:10** aspect |
| Logo preview | **96px** tall, `object-fit: contain` |

**CSS:** `assets/css/lineup-website-admin.css`

---

## 13. Patient case media

### 13a. Clinical photos (intraoral / extraoral)

| Context | Display size | Recommended upload | Max file | Formats |
|---------|--------------|-------------------|----------|---------|
| New/edit case — thumb preview | **72×72**, `object-fit: cover` | **2000px** long edge min | **100 MB** | JPEG, JPG, PNG, WebP |
| Photo dropzone area | `min-height: 180px` | — | — | — |
| Modification / refinement preview | **40×40** | Same as case photos | **100 MB** | Same |
| Gallery trigger thumbs | **40×40**, overlapping stack | — | — | — |
| Gallery modal — main image | `max-width: 100%`, `max-height: min(54vh, 500px)`, `object-fit: contain` | — | — | — |
| Gallery modal — stage area | `min-height: min(62vh, 560px)` | — | — | — |

**Note:** Gallery uses `object-fit: contain` — photos show full frame with letterboxing. Export at consistent orientation.

### 13b. Case chat attachments

| Property | Value |
|----------|-------|
| **Thumb display** | **52×52**, `object-fit: cover` |
| **Max file** | **25 MB** (any file type) |
| **Images** | JPEG, PNG, WebP, etc. |

### 13c. Case data ZIP

| Property | Value |
|----------|-------|
| **Max file** | **100 MB** |
| **Format** | ZIP only |

---

## 14. 3D scans & viewer

### 14a. Scan file uploads

| Property | Value |
|----------|-------|
| **Formats** | **STL, OBJ, PLY** (+ ZIP on modification/refinement flows) |
| **Max file** | **100 MB** per scan / ZIP |
| **Server requirement** | PHP `upload_max_filesize` & `post_max_size` ≥ **100 MB** |

### 14b. Scan placeholder graphics (UI only)

Static JPEGs shown in the 3D viewer file list before models load:

| Asset | Path | Display in UI |
|-------|------|---------------|
| Upper jaw placeholder | `assets/images/placeholders/uppper-paceholder.jpeg` | **48×32** thumb (`object-fit: cover`) |
| Lower jaw placeholder | `assets/images/placeholders/lower-paceholder.jpeg` | **48×32** thumb |

**Upload form placeholder graphic:** **160×100**, `object-fit: contain` (`patient-form.css`). Compact mod form: **96×60**.

### 14c. 3D viewer canvas

| Breakpoint | Viewer height |
|------------|---------------|
| Desktop | `min(524px, 52.4vh)`, min-height **323px** |
| ≤1199px | `min(424px, 45.4vh)` |
| ≤767px | `min(360px, 48vh)`, min-height **240px** |

| Layout | Behavior |
|--------|----------|
| Desktop | Scan file panel floats top-right over canvas |
| Mobile (≤767px) | Scan file list **below** the viewer (full width) |

---

## 15. Admin CMS upload limits

Quick reference — all validation in `WebsiteController.php` and `SettingsController.php`.

| Field / group | Max size | Formats |
|---------------|----------|---------|
| `hero_image`, `about_image` | **4 MB** | jpeg, jpg, png, webp |
| `hero_video` | **50 MB** | mp4, webm |
| `titlebar_image`, `footer_image` | **5 MB** | jpeg, jpg, png, webp |
| Hero slides `image_file` | **5 MB** | jpeg, jpg, png, webp |
| Features / Why LINEUP `image_file` | **5 MB** | jpeg, jpg, png, webp |
| Process steps `image_file` | **5 MB** | jpeg, jpg, png, webp |
| Blog `image_file` + detail | **5 MB** | jpeg, jpg, png, webp |
| Service detail `image_file` | **5 MB** | jpeg, jpg, png, webp |
| Showcase `before_image`, `after_image` | **5 MB** | jpeg, jpg, png, webp |
| Showcase `detail_image1/2_file` | **5 MB** | jpeg, jpg, png, webp |
| Treatable items `image_file` | **5 MB** | jpeg, jpg, png, webp |
| Settings `logo` | **2 MB** | jpeg, jpg, png, **svg**, webp |
| Settings / profile `photo` | **2 MB** | jpeg, jpg, png, webp |
| Patient photos | **100 MB** | jpeg, jpg, png, webp |
| 3D scans | **100 MB** | stl, obj, ply, zip |

---

## 16. Designer cheat sheet

One-page export checklist:

| Use case | Export size | Ratio | Max file | Format |
|----------|-------------|-------|----------|--------|
| Hero video | 1920×1080 | 16:9 | 50 MB | MP4/WebM |
| Hero slider slide | 1920×844 | 2.27:1 | 5 MB | JPG/WebP |
| Title bar / inner banner | 1920×450 | 3.5:1 | 5 MB | JPG/WebP |
| About (HP1) | 1200×900 | 4:3 | 4 MB | JPG/WebP |
| About (HP2 masked) | 800×800 | 1:1 | 4 MB | PNG/JPG |
| Feature / Why LINEUP card | 1280×800 | 16:10 | 5 MB | JPG/WebP |
| Blog card | 1280×880 | 16:11 | 5 MB | JPG/WebP |
| Blog detail hero | 1280×800 | 16:10 | 5 MB | JPG/WebP |
| Case before & after | 1400×650 | 2.15:1 | 5 MB | JPG/WebP |
| Case detail extras | 900×840 | 1.07:1 | 5 MB | JPG/WebP |
| Service detail | 1000×615 | 1.62:1 | 5 MB | JPG/WebP |
| Logo (header) | ≥240px wide | flexible | 2 MB | SVG/PNG |
| Footer logo | 240×60 | 4:1 | 5 MB | PNG/SVG |
| Avatar (doctor/admin) | 400×400 | 1:1 | 2 MB | JPG/PNG |
| Patient clinical photo | 2000px long edge | any | 100 MB | JPG/PNG |
| Process step screenshot | 1280×800 | 16:10 | 5 MB | PNG/JPG |

### General rules

1. **Landscape** for heroes, title bars, before/after pairs.
2. **16:10 or 16:11** for cards (blog, features).
3. **4:3** for about section (HP1).
4. **Match before/after** dimensions and horizon line for case sliders.
5. **SVG** for logos when possible; PNG with transparency as fallback.
6. **WebP** is accepted everywhere JPG/PNG is — smaller files at same quality.
7. Keep important content in the **center 80%** — mobile crops aggressively with `object-fit: cover`.

---

## 17. Source files index

| Purpose | Path |
|---------|------|
| Default demo content & image paths | `config/website.php` |
| CMS upload validation | `app/Http/Controllers/Admin/WebsiteController.php` |
| Branding uploads | `app/Http/Controllers/Admin/SettingsController.php` |
| Patient photos & scans | `app/Http/Controllers/PatientController.php` |
| Public display overrides | `assets/css/lineup-smiliz-overrides.css` |
| Smiliz theme dimensions | `assets/smiliz/css/shortcode.css`, `style.css`, `responsive.css` |
| Placeholder template | `assets/css/lineup-public-website.css` |
| Patient / case UI | `assets/css/patient-form.css`, `patient-case-study.css` |
| Admin field hints | `resources/views/admin/website/partials/panel-*.blade.php` |
| Scan placeholders | `assets/images/placeholders/` |
| Bundled demo images | `assets/smiliz/images/` |
| Process step SVGs | `assets/website/process/` |

---

*Last updated: June 2026 — matches LineUp Aligner codebase (Laravel 13, Smiliz marketing theme).*
