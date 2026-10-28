# Rad Brothers WordPress theme

Brochure theme for Rad Brothers Ltd. Every page is built from **Page sections** (a flexible content field), so the client can add, reorder and edit sections without touching code.

## Install

1. Install and activate **Secure Custom Fields** (free, from the WordPress plugin directory). ACF Pro also works.
2. Upload `radbrothers.zip` under Appearance → Themes → Add New → Upload, and activate it.
3. Click **Import starter content** in the notice that appears. This creates all pages, copy, placeholder photos, the three menus and Site settings. (Or run `wp radbrothers seed`.)
4. Settings → Permalinks → Save (if links 404).

You can also run (or re-run) the import any time from **Appearance → Starter content**. Re-running replaces the sections on the starter pages, so only do it before the client starts editing.

## Pages created

Home · Exhibition Services (with Education & Careers Fairs and Trade Shows & Corporate as child pages) · Client Portal · How It Works · Other Services · About Us · Clients & Testimonials · Contact · Privacy / Cookies / Terms (placeholder text).

## Section types

| Section | Used for |
|---|---|
| Hero / page header | Large home hero or compact inner-page header. `*word*` in the heading turns orange. |
| Client logos | Scrolling strip or grid |
| Cards | Services, features, pain points. Icon or photo, optional tag and tick list |
| Two big panels | Universities vs businesses |
| Process | Compact strip, or detailed timeline with a service-level filter |
| Why us / feature list | Navy list with icons, optional image |
| Testimonials | Rotating single quote or grid |
| Meet the team | Group photo with a hotspot per face |
| Text + image | Story, portal, etc. with optional floating badge |
| Client portal showcase | Sells the client portal: features, buttons and an animated, anonymised portal dashboard. Upload a demo-account screenshot to replace the illustration. |
| Comparison table | Delivery Only vs Full Service vs Build Partner |
| FAQs | Accordion, outputs FAQ schema |
| Case studies | Challenge / what we did / result |
| Call to action band | Orange or navy |
| Contact / quote form | Built-in form, or paste a CF7/WPForms/Gravity shortcode |
| Text | Plain rich text (legal pages) |

## Meet the team hotspots

Add the group photo, then one row per person with **Face X %** and **Face Y %** (position of the centre of their face as a percentage of the photo width/height). Desktop: hover a face. Touch: tap a face, or swipe the name chips. Arrow keys and Escape work too.

## Contact form email

Site settings → Contact form. Set the API key in `wp-config.php` rather than the database:

```php
define( 'RB_RESEND_API_KEY', 're_xxx' );
```

The From address must be on a domain verified in Resend (contact@radbrothers.co.uk is already set up). With no key, the form falls back to `wp_mail()`.

## Other notes

- Fonts (Space Grotesk, Jost, IBM Plex Mono) are self-hosted in `assets/fonts`, so no Google Fonts requests.
- Scroll animations are in `assets/js/main.js` (IntersectionObserver) and respect "reduce motion".
- Field groups are registered in `inc/fields.php`, so they ship with the theme and don't need syncing.
- Logos in `assets/img/brand` were extracted from the brand sheet as SVG.

## Client portal links

Set **Client portal URL** in Site settings. Any button whose link is `#portal-login` automatically points there. "Book a demo" buttons link to `/contact/?service=portal`, which pre-selects "Client portal demo" in the quote form.

## Changelog

### 1.1.0
- New Client portal showcase section with an animated, anonymised portal dashboard
- New Client Portal page (in Services menu and footer)
- Portal promoted on Home, Exhibition Services, Education, Trade Shows, How It Works, Other Services and Contact
- Quote form: "Client portal demo" option, pre-selected from `?service=portal`
- Appearance → Starter content page for running or re-running the import

### 1.0.3
- Mobile menu: header turns solid white straight away when the menu opens, so the logo and close icon no longer disappear into the dark hero

### 1.0.2
- Comparison table on mobile: dropped the navy column, Full Service is highlighted with an orange title and ticks instead

### 1.0.1
- Header, hero and mobile menu allow for the WordPress admin bar
- Comparison table fits on mobile (no sideways scrolling)
- Page can no longer scroll sideways on phones while sections animate in

### 1.0.0
- First release
