# Antonia Zanolli – WordPress Theme

## Deployment Steps

### 1 – Upload the theme folder

The theme folder is self-contained — all assets are already bundled inside it. Upload the entire `antonia-theme/` folder to `wp-content/themes/` on the WordPress server.

### 2 – Activate the theme

WordPress Admin → **Appearance → Themes** → activate **Antonia Zanolli**.

### 3 – Configure the front page

WordPress Admin → **Settings → Reading**:

- _Your homepage displays_ → **A static page**
- _Homepage_ → select (or first create) a blank page titled **Home**
- _Posts page_ → select (or first create) a blank page titled **Blog**

### 4 – Create static pages

WordPress Admin → **Pages → Add New** for each:

| Title          | Slug             | Content source                               |
| -------------- | ---------------- | -------------------------------------------- |
| Home           | `home`           | Leave blank – uses `front-page.php`          |
| Blog           | `blog`           | Leave blank – uses `home.php` (post listing) |
| Imprint        | `imprint`        | Paste from `imprint.html`                    |
| Privacy Policy | `privacy-policy` | Paste from `privacy.html`                    |

### 5 – Set up navigation menus

WordPress Admin → **Appearance → Menus**.

Create **four** menus and assign each to its location:

#### Header – Left

_Location: Header – Left_
| Label | Link to |
|---|---|
| Books | Page: Books (or custom URL `/books`) |
| Blog | Page: Blog |
| Art | Custom URL `/art` |

#### Header – Right

_Location: Header – Right_
| Label | Link to |
|---|---|
| Author | Custom URL `/author` |
| Contact | Custom URL `/contact` |
| Press | Custom URL `/press` |

#### Mobile Drawer

_Location: Mobile Drawer_
All links that should be reachable on mobile. Recommended: the same six items as the two header menus combined.
| Label | Link to |
|---|---|
| Books | Page: Books (or custom URL `/books`) |
| Blog | Page: Blog |
| Art | Custom URL `/art` |
| Author | Custom URL `/author` |
| Contact | Custom URL `/contact` |
| Press | Custom URL `/press` |

#### Footer Links

_Location: Footer Links_
| Label | Link to |
|---|---|
| Imprint | Page: Imprint |
| Privacy Policy | Page: Privacy Policy |

> **Tip:** Header and mobile links will render as styled navigation items automatically. Footer links appear pipe-separated ( Imprint | Privacy Policy ) by CSS.

> **Important:** If no menu is assigned to a location, that location simply renders nothing — no broken HTML. You can populate them gradually.

---

## Managing Books

### Adding a book

1. WordPress Admin → **Books → Add New**
2. Fill in the **Title** (the book's name)
3. Write a short description in the **body editor** — this appears on the book's individual page
4. Add a short **excerpt** (a few sentences) — this is what shows in the homepage carousel underneath the covers
5. Set a **Featured Image** — used as the cover in both the carousel and the Books overview grid
6. In the **Book Details** box (right sidebar), fill in:
   - **Subtitle** — e.g. "Book One of the Realm Series"
   - **Buy Link** — the full URL where readers can purchase the book
7. In the **Book Series** box (right sidebar), assign the book to a series (or create a new one)
8. In the **Page Attributes** box (right sidebar), set an **Order** number — books are displayed lowest-to-highest, left-to-right in the carousel and on the Books page. Set `1`, `2`, `3`… to control the sequence.
9. Click **Publish**

> **Tip:** The middle book in the carousel is highlighted as "active" by default. If you have three books, give them orders 1, 2, 3 — book 2 will be centred.

### Managing series

WordPress Admin → **Books → Book Series**

Create a series (e.g. "The Heartless Prince Series"), then assign books to it when editing each book. On the Books overview page, all books in the same series are grouped together under that series name. Books with no series are grouped under "Standalones".

You can add a short description to each series — it appears as a subtitle below the series name on the Books page.

### Books overview page (`/books`)

This page is generated automatically — you don't need to create it in Pages. It shows all published books grouped by series, with circular thumbnails. Each thumbnail links to that book's individual page.

> **Important:** After adding the Books section for the first time, go to **Settings → Permalinks** and click **Save Changes** (without changing anything). This is a one-time step that tells WordPress where to find the `/books/` URLs.

---

## Homepage Carousel

The book carousel on the homepage is driven directly by your Books. To change what appears:

- **Add/remove books** — publish or unpublish them under **Books**
- **Change the order** — edit the "Order" number in the Page Attributes box
- **Change a cover image** — update the Featured Image on that book
- **Change the description** — update the Excerpt on that book

No code editing needed.

---

## Theme Customizer

Go to **Appearance → Customize** to edit the following settings live (you'll see a preview on the right as you type).

### Homepage Hero

| Setting                      | What it does                                                                                             |
| ---------------------------- | -------------------------------------------------------------------------------------------------------- |
| **Hero Book Cover**          | The large rotating book cover in the centre of the homepage. Upload a new image from your media library. |
| **Hero Book Cover Link**     | Where the cover links to when clicked. Leave empty to auto-link to the Books page.                       |
| **"Scroll Down" Badge Text** | The word in the red badge at the bottom of the hero (default: "books").                                  |

### Footer

| Setting           | What it does                                                                                                                                     |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Footer Notice** | The short statement at the top of the footer (default: "The content on this site was not generated by AI."). Leave it blank to hide it entirely. |

> **Tip:** The copyright line in the footer always uses your **site title** from Settings → General, so you only need to set it once.

---

## Art / Gallery Pages

When you add a **Gallery block** in the Gutenberg editor on any page, images in that gallery become clickable thumbnails automatically. Clicking one opens a full-screen lightbox with:

- Arrow buttons (or ← → keyboard keys) to browse through the gallery
- Click outside the image or press Escape to close

No plugins needed — this is built into the theme.

---

## Blog

### Excerpt length

- On the **Blog listing page**, each post shows roughly 55 words as a preview.
- On the **homepage**, the three latest posts show roughly 30 words.

If you want to show more or less, you can set a **manual excerpt** on any individual post (in the Excerpt box below the editor) and it will use that instead of the automatic cut-off.

### Blog listing page

This is driven by whatever page you assigned as the "Posts page" in **Settings → Reading**. WordPress handles pagination automatically as you add more posts.

---

## Scrollbar

The page scrollbar is visible and styled to match the dark theme (dark track, amber thumb). This is purely cosmetic and handled by CSS — no settings needed.
