# Antonia Zanolli WordPress Theme

This guide is for editors and authors. You should not need to edit CSS or theme files.

## 1) Quick Setup

1. Upload the full antonia-theme folder to wp-content/themes.
2. Activate Antonia Zanolli in Appearance > Themes.
3. Go to Settings > Reading:
   - Homepage displays: A static page
   - Homepage: Home
   - Posts page: Blog
4. Create pages if needed: Home, Blog, Imprint, Privacy Policy.
5. Go to Settings > Permalinks and click Save Changes once.

## 2) Menus

Go to Appearance > Menus and assign menus to:

- Header - Left
- Header - Right
- Mobile Drawer
- Footer Links

Notes:

- Desktop supports submenu dropdowns.
- Mobile Drawer controls hamburger-menu links on phones/tablets.
- Footer links are shown inline.

## 3) Books and Carousel

Go to Books > Add New for each title.

Fill in:

- Title
- Main content (book page body)
- Excerpt (used for homepage carousel description)
- Featured Image (cover)
- Book Series term (optional)

In Book Details:

- Subtitle
- Carousel Priority (higher = earlier on homepage carousel)
- Buy Link (single URL)
- Buy Buttons (one per line): Label|URL

In Page Attributes:

- Order controls sequence on Books archive and series lists.

Ordering logic:

- Homepage carousel: Carousel Priority first, then Order.
- Books archive/series lists: Order.

## 4) Theme Customizer (No CSS Needed)

Go to Appearance > Customize.

Homepage Hero:

- Hero Book Cover
- Hero Book Cover Link
- Scroll Down Badge Text

Books Styling:

- Series tag colors
- Subtitle color
- Series title size and letter spacing
- Series description size and line height

Layout and Spacing:

- Header Menu Side Width (%)
- Header Menu Min Width (px)
- Desktop Top Gap Under Menu (rem)
- Mobile Footer Clearance (rem)

Typography:

- Base Paragraph Size (rem)
- Header Menu Font Size (rem)
- Site Title Max Size (rem)

Colors and Buttons:

- Footer Background Color
- Footer Text Color
- Footer Heading Color
- Primary Button Background
- Primary Button Text Color
- Primary Button Radius (rem)

Footer:

- Footer Notice

## 5) Blog and Gallery

Blog:

- Listing and homepage previews show formatted content when possible.
- Manual excerpt overrides automatic preview.
- Pagination is automatic.

Gallery:

- Gallery images are clickable.
- Lightbox supports Left/Right arrows and Escape.

## 6) FAQ

Q: Why is my manual series page not opening?

A: The theme checks for a normal WordPress page whose slug exactly matches the Book Series term slug.

Important details:

- It does not require the page URL to be under /book-series/.
- It can be a top-level page or a child page.
- Matching is by final page slug only.

Example:

- Book Series term slug: prince-series
- Create a page with slug: prince-series
- Final page URL can be either:
  - yoursite.com/prince-series
  - or a nested page URL ending in /prince-series

If it still opens the automatic taxonomy page:

1. Confirm the term slug is exactly the same (including hyphens).
2. Confirm the page is Published (not Draft/Private).
3. Re-save Permalinks once.
4. Clear cache and hard refresh.

Q: To change padding between menu and title/page content, do I edit CSS variables?

A: No. Use Appearance > Customize > Layout and Spacing.

Use these controls:

- Header Menu Side Width (%)
- Header Menu Min Width (px)
- Desktop Top Gap Under Menu (rem)

You do not need to edit:

- --header-menu-width
- --header-menu-min-width

Those are now controlled by Customizer values.

## 7) Troubleshooting Checklist

If something looks outdated:

1. Clear any cache plugin cache.
2. Hard refresh browser.
3. Re-save Permalinks.

If mobile menu looks wrong:

1. Confirm a menu is assigned to Mobile Drawer.
2. Test in private/incognito mode.
