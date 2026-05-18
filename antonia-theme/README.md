# Antonia Zanolli WordPress Theme

This guide is for day-to-day content management. You should not need to edit code.

## Quick Setup

1. Upload the full antonia-theme folder to wp-content/themes.
2. Activate Antonia Zanolli in Appearance > Themes.
3. Go to Settings > Reading:
   - Homepage displays: A static page
   - Homepage: Home
   - Posts page: Blog
4. Create pages if needed: Home, Blog, Imprint, Privacy Policy.
5. Go to Settings > Permalinks and click Save Changes once.

## Menus

Go to Appearance > Menus and assign menus to these locations:

- Header - Left
- Header - Right
- Mobile Drawer
- Footer Links

Notes:

- Submenus are supported in desktop header and mobile drawer.
- Mobile Drawer controls what appears in the hamburger menu on phones/tablets.
- Footer links are shown inline.

## Books (Most Important)

Go to Books > Add New for each book.

Fill in:

- Title
- Main content (shown on the single book page)
- Excerpt (used in homepage carousel description)
- Featured Image (book cover)
- Book Series term (optional)

In the Book Details box:

- Subtitle
- Carousel Priority: higher number appears earlier on homepage carousel
- Buy Link (single URL)
- Buy Buttons (one per line): Label|URL format for multiple retailers

In Page Attributes:

- Order controls the order on the Books archive and within series displays.

Important ordering logic:

- Homepage carousel uses Carousel Priority first, then Order.
- Books archive and series lists use Order.

## Series Pages

When clicking a series tag/title:

- If a normal page exists with the same slug as the series term, that page opens.
- Otherwise, the default series taxonomy page opens.

Example:

- Series term slug: heartless-prince
- Create a page with slug heartless-prince to use a custom series landing page.

## Homepage Controls

Appearance > Customize > Homepage Hero:

- Hero Book Cover
- Hero Book Cover Link
- Scroll Down Badge Text

Homepage also includes:

- Book carousel from Books entries
- Latest blog previews
- Read the Blog button under previews

## Book Styling Controls

Appearance > Customize > Books Styling:

- Series tag colors (text, border, background)
- Book subtitle color
- Series title size and letter spacing
- Series description size and line height

## Blog Behavior

- Blog listing and homepage previews show formatted content when possible.
- If you set a manual Excerpt, that is used first.
- Posts page pagination is automatic.

## Gallery / Lightbox

On pages/posts, add a Gallery block as normal.

- Gallery images are clickable.
- Clicking opens a full-screen lightbox.
- Keyboard support: Left/Right arrows and Escape.

## Single Book Page Features

- All Books back link at top
- Series tag link near the title
- Buy buttons shown below the cover image

## Troubleshooting

If something looks outdated after changes:

1. Clear any caching plugin cache.
2. Hard refresh the browser.
3. Re-save Permalinks (Settings > Permalinks > Save Changes).

If mobile menu appears wrong:

- Confirm the menu is assigned to Mobile Drawer.
- Check in an incognito window to bypass cache.
