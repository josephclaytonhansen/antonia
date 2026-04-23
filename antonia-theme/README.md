# Antonia Zanolli – WordPress Theme

## Deployment Steps

### 1 – Upload the theme folder
The theme folder is self-contained — all assets are already bundled inside it. Upload the entire `antonia-theme/` folder to `wp-content/themes/` on the WordPress server.

### 2 – Activate the theme
WordPress Admin → **Appearance → Themes** → activate **Antonia Zanolli**.

### 3 – Configure the front page
WordPress Admin → **Settings → Reading**:
- *Your homepage displays* → **A static page**
- *Homepage* → select (or first create) a blank page titled **Home**
- *Posts page* → select (or first create) a blank page titled **Blog**

### 4 – Create static pages
WordPress Admin → **Pages → Add New** for each:

| Title | Slug | Content source |
|---|---|---|
| Home | `home` | Leave blank – uses `front-page.php` |
| Blog | `blog` | Leave blank – uses `home.php` (post listing) |
| Imprint | `imprint` | Paste from `imprint.html` |
| Privacy Policy | `privacy-policy` | Paste from `privacy.html` |

### 5 – Set up navigation menus
WordPress Admin → **Appearance → Menus**.

Create **four** menus and assign each to its location:

#### Header – Left
*Location: Header – Left*
| Label | Link to |
|---|---|
| Books | Page: Books (or custom URL `/books`) |
| Blog | Page: Blog |
| Art | Custom URL `/art` |

#### Header – Right
*Location: Header – Right*
| Label | Link to |
|---|---|
| Author | Custom URL `/author` |
| Contact | Custom URL `/contact` |
| Press | Custom URL `/press` |

#### Mobile Drawer
*Location: Mobile Drawer*
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
*Location: Footer Links*
| Label | Link to |
|---|---|
| Imprint | Page: Imprint |
| Privacy Policy | Page: Privacy Policy |

> **Tip:** Header and mobile links will render as styled navigation items automatically. Footer links appear pipe-separated ( Imprint | Privacy Policy ) by CSS.

> **Important:** If no menu is assigned to a location, that location simply renders nothing — no broken HTML. You can populate them gradually.