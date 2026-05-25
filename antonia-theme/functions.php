<?php

/**
 * Antonia Zanolli Theme – functions.php
 *
 * Theme setup, script/style enqueueing, and helper functions.
 */

// ─── Theme Setup ────────────────────────────────────────────────────────────

function antonia_setup()
{
	// Let WordPress manage <title>
	add_theme_support('title-tag');

	// Featured images on posts and pages
	add_theme_support('post-thumbnails');

	// HTML5 markup support
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'navigation-widgets',
	]);

	// Navigation menus
	register_nav_menus([
		'header-left'  => __('Header – Left',    'antonia-zanolli'),
		'header-right' => __('Header – Right',   'antonia-zanolli'),
		'mobile'       => __('Mobile Drawer',    'antonia-zanolli'),
		'footer'       => __('Footer Links',     'antonia-zanolli'),
	]);
}
add_action('after_setup_theme', 'antonia_setup');

// ─── Enqueue Scripts & Styles ────────────────────────────────────────────────

function antonia_enqueue_scripts()
{
	$theme_version = wp_get_theme()->get('Version');

	// Main theme stylesheet (style.css in theme root)
	wp_enqueue_style(
		'antonia-style',
		get_stylesheet_uri(),
		[],
		$theme_version
	);

	// Google Fonts – preconnect hints go in header.php; the actual CSS sheet here
	wp_enqueue_style(
		'antonia-google-fonts',
		'https://fonts.googleapis.com/css2?family=Caudex:ital,wght@0,400;0,700;1,400;1,700&family=Germania+One&family=Grenze+Gotisch:wght@100..900&family=Grenze:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
		[],
		null
	);

	// Mobile drawer (runs on every page)
	// The drawer HTML is now server-rendered in header.php; this script only
	// handles open/close behaviour and has no external dependencies.
	wp_enqueue_script(
		'antonia-mobile-menu',
		get_template_directory_uri() . '/mobile-menu.js',
		[],
		$theme_version,
		true  // footer
	);

	// Book carousel – front page only
	if (is_front_page()) {
		wp_enqueue_script(
			'antonia-carousel',
			get_template_directory_uri() . '/carousel.js',
			[],
			$theme_version,
			true
		);

		// Pass Book CPT data to the carousel script so it's editable from WP Admin.
		// Books are ordered by the "Order" field (Page Attributes box in the editor).
		$_carousel_posts = get_posts([
			'post_type'      => 'book',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
			'meta_key'       => '_book_carousel_priority',
			'orderby'        => [
				'meta_value_num' => 'DESC',
				'menu_order'     => 'ASC',
			],
		]);

		$_carousel_data = [];
		foreach ($_carousel_posts as $_cb) {
			$_carousel_data[] = [
				'title'       => get_the_title($_cb),
				'description' => get_the_excerpt($_cb),
				'link'        => get_permalink($_cb),
			];
		}

		// antoniaCarouselBooks is read by carousel.js; empty array = use JS fallback
		wp_localize_script('antonia-carousel', 'antoniaCarouselBooks', $_carousel_data);
	}

	// Lightbox – all pages except front page (galleries live on Art/Books/etc.)
	if (! is_front_page()) {
		wp_enqueue_script(
			'antonia-lightbox',
			get_template_directory_uri() . '/lightbox.js',
			[],
			$theme_version,
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'antonia_enqueue_scripts');

// ─── Theme Helpers ───────────────────────────────────────────────────────────

/**
 * Return the best public URL for a book series term.
 *
 * If a normal Page exists with the same slug as the series term,
 * that page is used as the destination. Otherwise, the taxonomy archive link
 * is returned.
 */
function antonia_get_series_landing_url($term)
{
	if (! $term || is_wp_error($term)) {
		return '';
	}

	// Match by page slug, even if the page is nested under a parent page.
	$_series_pages = get_posts([
		'post_type'      => 'page',
		'name'           => $term->slug,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
	]);
	if (! empty($_series_pages)) {
		return get_permalink($_series_pages[0]);
	}

	$_term_link = get_term_link($term);
	if (is_wp_error($_term_link)) {
		return '';
	}

	return $_term_link;
}

/**
 * Sanitize numeric customizer fields that may include locale commas.
 */
function antonia_sanitize_float_range($value, $min, $max, $default)
{
	$_raw = str_replace(',', '.', trim((string) $value));
	if ($_raw === '' || ! is_numeric($_raw)) {
		return (float) $default;
	}

	$_num = (float) $_raw;
	if ($_num < $min) {
		return (float) $min;
	}
	if ($_num > $max) {
		return (float) $max;
	}

	return $_num;
}

/**
 * Sanitize integer customizer fields.
 */
function antonia_sanitize_int_range($value, $min, $max, $default)
{
	$_raw = trim((string) $value);
	if ($_raw === '' || ! is_numeric($_raw)) {
		return (int) $default;
	}

	$_num = (int) round((float) $_raw);
	if ($_num < $min) {
		return (int) $min;
	}
	if ($_num > $max) {
		return (int) $max;
	}

	return $_num;
}

/**
 * Build display-ready buy-link buttons for a book.
 *
 * Supports two dashboard fields:
 *  1. Legacy single URL field (`_book_buy_link`)
 *  2. Multi-link textarea (`_book_buy_links`) with one `Label|URL` per line
 */
function antonia_get_book_buy_links($post_id)
{
	$links = [];

	$_raw_multi = trim((string) get_post_meta($post_id, '_book_buy_links', true));
	if ($_raw_multi !== '') {
		$_lines = preg_split('/\r\n|\r|\n/', $_raw_multi);
		foreach ($_lines as $_line) {
			$_line = trim((string) $_line);
			if ($_line === '') {
				continue;
			}

			$_parts = array_map('trim', explode('|', $_line, 2));
			$_label = ! empty($_parts[0]) ? $_parts[0] : __('Get the Book', 'antonia-zanolli');
			$_url   = isset($_parts[1]) ? esc_url($_parts[1]) : '';

			if ($_url !== '') {
				$links[] = [
					'label' => sanitize_text_field($_label),
					'url'   => $_url,
				];
			}
		}
	}

	if (empty($links)) {
		$_legacy = esc_url((string) get_post_meta($post_id, '_book_buy_link', true));
		if ($_legacy !== '') {
			$links[] = [
				'label' => __('Get the Book', 'antonia-zanolli'),
				'url'   => $_legacy,
			];
		}
	}

	return $links;
}

/**
 * Render a formatted post preview with basic Gutenberg block styling.
 *
 * This is used for excerpt cards/listings when no manual excerpt is set.
 */
function antonia_get_post_preview_html($post = null, $max_blocks = 2, $word_limit = 55)
{
	$_post = get_post($post);
	if (! $_post instanceof WP_Post) {
		return '';
	}

	if (has_excerpt($_post)) {
		$_excerpt = get_the_excerpt($_post);
		return wpautop(wp_kses_post($_excerpt));
	}

	$_allowed_blocks = [
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/quote',
		'core/pullquote',
	];

	$_html_parts = [];
	$_parsed     = parse_blocks((string) $_post->post_content);
	foreach ($_parsed as $_block) {
		if (! isset($_block['blockName']) || ! in_array($_block['blockName'], $_allowed_blocks, true)) {
			continue;
		}

		$_rendered = trim((string) render_block($_block));
		if ($_rendered !== '') {
			$_html_parts[] = wp_kses_post($_rendered);
		}

		if (count($_html_parts) >= $max_blocks) {
			break;
		}
	}

	if (! empty($_html_parts)) {
		return implode("\n", $_html_parts);
	}

	$_fallback = wp_trim_words(wp_strip_all_tags((string) $_post->post_content), $word_limit, '…');
	return '<p>' . esc_html($_fallback) . '</p>';
}

// ─── Excerpt Customisation ────────────────────────────────────────────────────

/**
 * Excerpt length: 55 words on the Blog listing page, 30 everywhere else.
 * To change these, adjust the numbers below.
 */
function antonia_excerpt_length($length)
{
	if (is_home() || is_archive()) {
		return 55;
	}
	return 30;
}
add_filter('excerpt_length', 'antonia_excerpt_length', 999);

function antonia_excerpt_more($more)
{
	return '…';
}
add_filter('excerpt_more', 'antonia_excerpt_more');

// ─── Add wp_body_open support for older WordPress versions ───────────────────

if (! function_exists('wp_body_open')) {
	function wp_body_open()
	{
		do_action('wp_body_open');
	}
}

// ─── Custom Nav-Menu Walkers ──────────────────────────────────────────────────

/**
 * Header walker: outputs <a href="…"><h3>Label</h3></a> per item.
 * Used for the desktop header-left and header-right menus.
 */
class Antonia_Header_Walker extends Walker_Nav_Menu
{

	public function start_lvl(&$output, $depth = 0, $args = null) {}
	public function end_lvl(&$output, $depth = 0, $args = null) {}

	public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0)
	{
		if ($depth > 0) return; // top-level items only

		$item = $data_object;

		$atts = [
			'href'   => ! empty($item->url)    ? $item->url    : home_url('/'),
			'target' => ! empty($item->target) ? $item->target : '',
			'rel'    => ! empty($item->xfn)    ? $item->xfn    : '',
		];

		if (in_array('current-menu-item', (array) $item->classes, true)) {
			$atts['aria-current'] = 'page';
		}

		$attributes = '';
		foreach ($atts as $attr => $value) {
			if ('' !== $value) {
				$value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
				$attributes .= " {$attr}=\"$value\"";
			}
		}

		$title   = apply_filters('nav_menu_item_title', $item->title, $item, $args, $depth);
		$output .= "<a{$attributes}><h3>" . esc_html($title) . "</h3></a>\n";
	}

	public function end_el(&$output, $data_object, $depth = 0, $args = null) {}
}

/**
 * Plain-link walker: outputs <a href="…">Label</a> per item.
 * Used for the mobile drawer and footer menus.
 */
class Antonia_Plain_Walker extends Walker_Nav_Menu
{

	public function start_lvl(&$output, $depth = 0, $args = null) {}
	public function end_lvl(&$output, $depth = 0, $args = null) {}

	public function start_el(&$output, $data_object, $depth = 0, $args = null, $current_object_id = 0)
	{
		if ($depth > 0) return;

		$item = $data_object;

		$atts = [
			'href'   => ! empty($item->url)    ? $item->url    : home_url('/'),
			'target' => ! empty($item->target) ? $item->target : '',
			'rel'    => ! empty($item->xfn)    ? $item->xfn    : '',
		];

		if (in_array('current-menu-item', (array) $item->classes, true)) {
			$atts['aria-current'] = 'page';
		}

		$attributes = '';
		foreach ($atts as $attr => $value) {
			if ('' !== $value) {
				$value       = ('href' === $attr) ? esc_url($value) : esc_attr($value);
				$attributes .= " {$attr}=\"$value\"";
			}
		}

		$title   = apply_filters('nav_menu_item_title', $item->title, $item, $args, $depth);
		$output .= "<a{$attributes}>" . esc_html($title) . "</a>\n";
	}

	public function end_el(&$output, $data_object, $depth = 0, $args = null) {}
}

// ─── Book Custom Post Type & Taxonomy ────────────────────────────────────────

/**
 * Register the 'book' custom post type and 'book_series' taxonomy.
 *
 * Books appear at /books/ (archive) and /books/book-slug/ (single).
 * Book Series appear at /book-series/series-slug/.
 *
 * After activating or updating the theme, visit Settings › Permalinks and
 * click Save to flush rewrite rules.
 */
function antonia_register_books()
{

	// ── Taxonomy: Book Series ──────────────────────────────────────────────
	register_taxonomy('book_series', 'book', [
		'label'              => __('Book Series', 'antonia-zanolli'),
		'labels'             => [
			'name'          => __('Book Series',       'antonia-zanolli'),
			'singular_name' => __('Book Series',       'antonia-zanolli'),
			'add_new_item'  => __('Add New Series',    'antonia-zanolli'),
			'edit_item'     => __('Edit Series',       'antonia-zanolli'),
			'new_item_name' => __('New Series Name',   'antonia-zanolli'),
			'search_items'  => __('Search Series',     'antonia-zanolli'),
			'all_items'     => __('All Series',        'antonia-zanolli'),
		],
		'hierarchical'       => true,      // acts like categories (parent/child)
		'public'             => true,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'rewrite'            => ['slug' => 'book-series'],
	]);

	// ── Post Type: Book ────────────────────────────────────────────────────
	register_post_type('book', [
		'label'               => __('Books', 'antonia-zanolli'),
		'labels'              => [
			'name'               => __('Books',           'antonia-zanolli'),
			'singular_name'      => __('Book',            'antonia-zanolli'),
			'add_new_item'       => __('Add New Book',    'antonia-zanolli'),
			'edit_item'          => __('Edit Book',       'antonia-zanolli'),
			'new_item'           => __('New Book',        'antonia-zanolli'),
			'view_item'          => __('View Book',       'antonia-zanolli'),
			'search_items'       => __('Search Books',    'antonia-zanolli'),
			'not_found'          => __('No books found.', 'antonia-zanolli'),
			'not_found_in_trash' => __('No books in trash.', 'antonia-zanolli'),
		],
		'description'         => __("Antonia's published books.", 'antonia-zanolli'),
		'public'              => true,
		'has_archive'         => 'books',   // archive at /books/
		'rewrite'             => ['slug' => 'books'],
		'menu_icon'           => 'dashicons-book-alt',
		'menu_position'       => 5,
		'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'],
		'taxonomies'          => ['book_series'],
		'show_in_rest'        => true,
	]);
}
add_action('init', 'antonia_register_books');

/**
 * Custom meta box: subtitle and buy link for each book.
 */
function antonia_book_meta_box()
{
	add_meta_box(
		'antonia_book_details',
		__('Book Details', 'antonia-zanolli'),
		'antonia_book_meta_box_html',
		'book',
		'side',
		'high'
	);
}
add_action('add_meta_boxes', 'antonia_book_meta_box');

function antonia_book_meta_box_html($post)
{
	wp_nonce_field('antonia_book_meta_save', 'antonia_book_meta_nonce');
	$subtitle = get_post_meta($post->ID, '_book_subtitle', true);
	$buy_link = get_post_meta($post->ID, '_book_buy_link', true);
	$buy_links = get_post_meta($post->ID, '_book_buy_links', true);
	$carousel_priority = get_post_meta($post->ID, '_book_carousel_priority', true);
?>
	<p>
		<label for="book_subtitle"><strong><?php esc_html_e('Subtitle', 'antonia-zanolli'); ?></strong></label><br />
		<input type="text" id="book_subtitle" name="book_subtitle"
			value="<?php echo esc_attr($subtitle); ?>" style="width:100%" />
	</p>
	<p>
		<label for="book_carousel_priority"><strong><?php esc_html_e('Carousel Priority', 'antonia-zanolli'); ?></strong></label><br />
		<input type="number" id="book_carousel_priority" name="book_carousel_priority"
			value="<?php echo esc_attr($carousel_priority); ?>" style="width:100%" />
		<small><?php esc_html_e('Higher numbers appear first in the homepage carousel. Keep "Order" (Page Attributes) for series-page ordering.', 'antonia-zanolli'); ?></small>
	</p>
	<p>
		<label for="book_buy_link"><strong><?php esc_html_e('Buy Link (URL)', 'antonia-zanolli'); ?></strong></label><br />
		<input type="url" id="book_buy_link" name="book_buy_link"
			value="<?php echo esc_attr($buy_link); ?>" style="width:100%"
			placeholder="https://…" />
	</p>
	<p>
		<label for="book_buy_links"><strong><?php esc_html_e('Buy Buttons (one per line)', 'antonia-zanolli'); ?></strong></label><br />
		<textarea id="book_buy_links" name="book_buy_links" rows="5" style="width:100%" placeholder="Amazon|https://amazon.com/...\nBarnes & Noble|https://..."><?php echo esc_textarea($buy_links); ?></textarea>
		<small><?php esc_html_e('Format: Label|URL. Use this for multiple retailers.', 'antonia-zanolli'); ?></small>
	</p>
<?php
}

function antonia_book_meta_save($post_id)
{
	if (get_post_type($post_id) !== 'book') return;
	if (! isset($_POST['antonia_book_meta_nonce'])) return;
	if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['antonia_book_meta_nonce'])), 'antonia_book_meta_save')) return;
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (! current_user_can('edit_post', $post_id)) return;

	if (isset($_POST['book_subtitle'])) {
		update_post_meta($post_id, '_book_subtitle', sanitize_text_field(wp_unslash($_POST['book_subtitle'])));
	}
	if (isset($_POST['book_buy_link'])) {
		update_post_meta($post_id, '_book_buy_link', esc_url_raw(wp_unslash($_POST['book_buy_link'])));
	}

	if (isset($_POST['book_buy_links'])) {
		$_raw_lines = preg_split('/\r\n|\r|\n/', (string) wp_unslash($_POST['book_buy_links']));
		$_cleaned   = [];

		foreach ($_raw_lines as $_line) {
			$_line = trim((string) $_line);
			if ($_line === '') {
				continue;
			}

			$_parts = array_map('trim', explode('|', $_line, 2));
			if (count($_parts) !== 2) {
				continue;
			}

			$_label = sanitize_text_field($_parts[0]);
			$_url   = esc_url_raw($_parts[1]);
			if ($_label !== '' && $_url !== '') {
				$_cleaned[] = $_label . '|' . $_url;
			}
		}

		update_post_meta($post_id, '_book_buy_links', implode("\n", $_cleaned));
	}

	if (isset($_POST['book_carousel_priority'])) {
		update_post_meta($post_id, '_book_carousel_priority', intval($_POST['book_carousel_priority']));
	}
}
add_action('save_post', 'antonia_book_meta_save');

// ─── Theme Customizer ─────────────────────────────────────────────────────────

/**
 * Register Customizer sections and settings.
 * Editable via Appearance › Customize in WP Admin.
 *
 * Settings exposed:
 *  – Homepage Hero: book cover image, book cover link URL, scroll-down badge text
 *  – Footer: the notice line (e.g. "not generated by AI")
 */
function antonia_customize_register($wp_customize)
{

	// ── Section: Homepage Hero ────────────────────────────────────────────
	$wp_customize->add_section('antonia_hero', [
		'title'    => __('Homepage Hero', 'antonia-zanolli'),
		'priority' => 30,
	]);

	// Hero book cover image (uploaded via media library)
	$wp_customize->add_setting('antonia_hero_book_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'postMessage',
	]);
	$wp_customize->add_control(
		new WP_Customize_Image_Control($wp_customize, 'antonia_hero_book_image', [
			'label'       => __('Hero Book Cover', 'antonia-zanolli'),
			'description' => __('The large rotating book cover displayed in the hero section. Defaults to the theme placeholder if left empty.', 'antonia-zanolli'),
			'section'     => 'antonia_hero',
		])
	);

	// Hero book cover link URL
	$wp_customize->add_setting('antonia_hero_book_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control('antonia_hero_book_url', [
		'label'       => __('Hero Book Cover Link', 'antonia-zanolli'),
		'description' => __('Where the rotating book cover links to. Leave empty to auto-link to the Books archive (/books).', 'antonia-zanolli'),
		'section'     => 'antonia_hero',
		'type'        => 'url',
	]);

	// Scroll-down badge text
	$wp_customize->add_setting('antonia_scroll_label', [
		'default'           => 'books',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	]);
	$wp_customize->add_control('antonia_scroll_label', [
		'label'       => __('"Scroll Down" Badge Text', 'antonia-zanolli'),
		'description' => __('The word shown inside the scroll-down button at the bottom of the hero.', 'antonia-zanolli'),
		'section'     => 'antonia_hero',
		'type'        => 'text',
	]);

	// ── Section: Footer ───────────────────────────────────────────────────
	$wp_customize->add_section('antonia_footer', [
		'title'    => __('Footer', 'antonia-zanolli'),
		'priority' => 35,
	]);

	// Footer notice line
	$wp_customize->add_setting('antonia_footer_notice', [
		'default'           => 'The content on this site was not generated by AI.',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control('antonia_footer_notice', [
		'label'       => __('Footer Notice', 'antonia-zanolli'),
		'description' => __('Short statement shown at the top of the footer. Leave empty to hide it.', 'antonia-zanolli'),
		'section'     => 'antonia_footer',
		'type'        => 'text',
	]);

	// ── Section: Layout & Spacing ───────────────────────────────────────
	$wp_customize->add_section('antonia_layout', [
		'title'       => __('Layout & Spacing', 'antonia-zanolli'),
		'priority'    => 37,
		'description' => __('Adjust menu spacing and top spacing under the fixed header.', 'antonia-zanolli'),
	]);

	$wp_customize->add_setting('antonia_header_menu_width_percent', [
		'default'           => 30,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_int_range($value, 16, 45, 30);
		},
	]);
	$wp_customize->add_control('antonia_header_menu_width_percent', [
		'label'       => __('Header Menu Side Width (%)', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 1, 'min' => 16, 'max' => 45],
	]);

	$wp_customize->add_setting('antonia_header_menu_min_width_px', [
		'default'           => 300,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_int_range($value, 140, 520, 300);
		},
	]);
	$wp_customize->add_control('antonia_header_menu_min_width_px', [
		'label'       => __('Header Menu Min Width (px)', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 1, 'min' => 140, 'max' => 520],
	]);

	$wp_customize->add_setting('antonia_content_top_gap_rem', [
		'default'           => 3,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 1, 8, 3);
		},
	]);
	$wp_customize->add_control('antonia_content_top_gap_rem', [
		'label'       => __('Desktop Top Gap Under Menu (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 1, 'max' => 8],
	]);

	$wp_customize->add_setting('antonia_mobile_footer_clearance_rem', [
		'default'           => 8.5,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 6, 16, 8.5);
		},
	]);
	$wp_customize->add_control('antonia_mobile_footer_clearance_rem', [
		'label'       => __('Mobile Footer Clearance (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 6, 'max' => 16],
	]);

	// ── Section: Typography ─────────────────────────────────────────────
	$wp_customize->add_section('antonia_typography', [
		'title'       => __('Typography', 'antonia-zanolli'),
		'priority'    => 38,
		'description' => __('Adjust core type sizes used across the theme.', 'antonia-zanolli'),
	]);

	$wp_customize->add_setting('antonia_base_text_size_rem', [
		'default'           => 1.5,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 1, 2.4, 1.5);
		},
	]);
	$wp_customize->add_control('antonia_base_text_size_rem', [
		'label'       => __('Base Paragraph Size (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_typography',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.05, 'min' => 1, 'max' => 2.4],
	]);

	$wp_customize->add_setting('antonia_header_menu_font_size_rem', [
		'default'           => 1.5,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 1, 2.2, 1.5);
		},
	]);
	$wp_customize->add_control('antonia_header_menu_font_size_rem', [
		'label'       => __('Header Menu Font Size (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_typography',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.05, 'min' => 1, 'max' => 2.2],
	]);

	$wp_customize->add_setting('antonia_header_title_max_size_rem', [
		'default'           => 4,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 3, 6, 4);
		},
	]);
	$wp_customize->add_control('antonia_header_title_max_size_rem', [
		'label'       => __('Site Title Max Size (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_typography',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 3, 'max' => 6],
	]);

	// ── Section: Colors & Buttons ───────────────────────────────────────
	$wp_customize->add_section('antonia_colors_buttons', [
		'title'       => __('Colors & Buttons', 'antonia-zanolli'),
		'priority'    => 39,
		'description' => __('Customize footer colors and primary button styling.', 'antonia-zanolli'),
	]);

	$wp_customize->add_setting('antonia_footer_bg_color', [
		'default'           => '#0d0c0a',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_footer_bg_color', [
		'label'   => __('Footer Background Color', 'antonia-zanolli'),
		'section' => 'antonia_colors_buttons',
	]));

	$wp_customize->add_setting('antonia_footer_text_color', [
		'default'           => '#523717',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_footer_text_color', [
		'label'   => __('Footer Text Color', 'antonia-zanolli'),
		'section' => 'antonia_colors_buttons',
	]));

	$wp_customize->add_setting('antonia_footer_heading_color', [
		'default'           => '#3a352c',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_footer_heading_color', [
		'label'   => __('Footer Heading Color', 'antonia-zanolli'),
		'section' => 'antonia_colors_buttons',
	]));

	$wp_customize->add_setting('antonia_button_bg_color', [
		'default'           => '#c77e3f',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_button_bg_color', [
		'label'   => __('Primary Button Background', 'antonia-zanolli'),
		'section' => 'antonia_colors_buttons',
	]));

	$wp_customize->add_setting('antonia_button_text_color', [
		'default'           => '#1f1b15',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_button_text_color', [
		'label'   => __('Primary Button Text Color', 'antonia-zanolli'),
		'section' => 'antonia_colors_buttons',
	]));

	$wp_customize->add_setting('antonia_button_radius_rem', [
		'default'           => 0.25,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0, 2, 0.25);
		},
	]);
	$wp_customize->add_control('antonia_button_radius_rem', [
		'label'       => __('Primary Button Radius (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_colors_buttons',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.05, 'min' => 0, 'max' => 2],
	]);

	// ── Section: Books Styling ───────────────────────────────────────────
	$wp_customize->add_section('antonia_books_style', [
		'title'       => __('Books Styling', 'antonia-zanolli'),
		'priority'    => 40,
		'description' => __('Change colors and typography for series tags, subtitles, and book series headings.', 'antonia-zanolli'),
	]);

	$wp_customize->add_setting('antonia_series_tag_text_color', [
		'default'           => '#d9b992',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_series_tag_text_color', [
		'label'   => __('Series Tag Text Color', 'antonia-zanolli'),
		'section' => 'antonia_books_style',
	]));

	$wp_customize->add_setting('antonia_series_tag_border_color', [
		'default'           => '#d9b992',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_series_tag_border_color', [
		'label'   => __('Series Tag Border Color', 'antonia-zanolli'),
		'section' => 'antonia_books_style',
	]));

	$wp_customize->add_setting('antonia_series_tag_bg_color', [
		'default'           => '#3a2b1f',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_series_tag_bg_color', [
		'label'   => __('Series Tag Background', 'antonia-zanolli'),
		'section' => 'antonia_books_style',
	]));

	$wp_customize->add_setting('antonia_book_subtitle_color', [
		'default'           => '#d9b992',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_book_subtitle_color', [
		'label'   => __('Book Subtitle Color', 'antonia-zanolli'),
		'section' => 'antonia_books_style',
	]));

	$wp_customize->add_setting('antonia_series_heading_size', [
		'default'           => 2,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 1.2, 4, 2);
		},
	]);
	$wp_customize->add_control('antonia_series_heading_size', [
		'label'       => __('Series Title Size (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_books_style',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 1.2, 'max' => 4],
	]);

	$wp_customize->add_setting('antonia_series_heading_letterspacing', [
		'default'           => 0.15,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0, 0.5, 0.15);
		},
	]);
	$wp_customize->add_control('antonia_series_heading_letterspacing', [
		'label'       => __('Series Title Letter Spacing (em)', 'antonia-zanolli'),
		'section'     => 'antonia_books_style',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.01, 'min' => 0, 'max' => 0.5],
	]);

	$wp_customize->add_setting('antonia_series_description_size', [
		'default'           => 1,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0.8, 2, 1);
		},
	]);
	$wp_customize->add_control('antonia_series_description_size', [
		'label'       => __('Series Description Size (rem)', 'antonia-zanolli'),
		'section'     => 'antonia_books_style',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.05, 'min' => 0.8, 'max' => 2],
	]);

	$wp_customize->add_setting('antonia_series_description_lineheight', [
		'default'           => 1.6,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 1, 2.4, 1.6);
		},
	]);
	$wp_customize->add_control('antonia_series_description_lineheight', [
		'label'       => __('Series Description Line Height', 'antonia-zanolli'),
		'section'     => 'antonia_books_style',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 1, 'max' => 2.4],
	]);
}
add_action('customize_register', 'antonia_customize_register');

/**
 * Print CSS custom properties from Customizer values.
 */
function antonia_print_customizer_css()
{
	$series_tag_text_color      = get_theme_mod('antonia_series_tag_text_color', '#d9b992');
	$series_tag_border_color    = get_theme_mod('antonia_series_tag_border_color', '#d9b992');
	$series_tag_bg_color        = get_theme_mod('antonia_series_tag_bg_color', '#3a2b1f');
	$book_subtitle_color        = get_theme_mod('antonia_book_subtitle_color', '#d9b992');
	$header_menu_width_percent  = antonia_sanitize_int_range(get_theme_mod('antonia_header_menu_width_percent', 30), 16, 45, 30);
	$header_menu_min_width_px   = antonia_sanitize_int_range(get_theme_mod('antonia_header_menu_min_width_px', 300), 140, 520, 300);
	$content_top_gap_rem        = antonia_sanitize_float_range(get_theme_mod('antonia_content_top_gap_rem', 3), 1, 8, 3);
	$mobile_footer_clearance    = antonia_sanitize_float_range(get_theme_mod('antonia_mobile_footer_clearance_rem', 8.5), 6, 16, 8.5);
	$base_text_size             = antonia_sanitize_float_range(get_theme_mod('antonia_base_text_size_rem', 1.5), 1, 2.4, 1.5);
	$header_menu_font_size      = antonia_sanitize_float_range(get_theme_mod('antonia_header_menu_font_size_rem', 1.5), 1, 2.2, 1.5);
	$header_title_max_size      = antonia_sanitize_float_range(get_theme_mod('antonia_header_title_max_size_rem', 4), 3, 6, 4);
	$footer_bg_color            = get_theme_mod('antonia_footer_bg_color', '#0d0c0a');
	$footer_text_color          = get_theme_mod('antonia_footer_text_color', '#523717');
	$footer_heading_color       = get_theme_mod('antonia_footer_heading_color', '#3a352c');
	$button_bg_color            = get_theme_mod('antonia_button_bg_color', '#c77e3f');
	$button_text_color          = get_theme_mod('antonia_button_text_color', '#1f1b15');
	$button_radius_rem          = antonia_sanitize_float_range(get_theme_mod('antonia_button_radius_rem', 0.25), 0, 2, 0.25);
	$series_heading_size        = (float) get_theme_mod('antonia_series_heading_size', 2);
	$series_heading_letterspace = (float) get_theme_mod('antonia_series_heading_letterspacing', 0.15);
	$series_desc_size           = (float) get_theme_mod('antonia_series_description_size', 1);
	$series_desc_line_height    = (float) get_theme_mod('antonia_series_description_lineheight', 1.6);

	$series_heading_size        = max(1.2, min($series_heading_size, 4));
	$series_heading_letterspace = max(0, min($series_heading_letterspace, 0.5));
	$series_desc_size           = max(0.8, min($series_desc_size, 2));
	$series_desc_line_height    = max(1, min($series_desc_line_height, 2.4));
	$footer_bg_color            = sanitize_hex_color($footer_bg_color) ?: '#0d0c0a';
	$footer_text_color          = sanitize_hex_color($footer_text_color) ?: '#523717';
	$footer_heading_color       = sanitize_hex_color($footer_heading_color) ?: '#3a352c';
	$button_bg_color            = sanitize_hex_color($button_bg_color) ?: '#c77e3f';
	$button_text_color          = sanitize_hex_color($button_text_color) ?: '#1f1b15';

	echo '<style id="antonia-customizer-vars">:root{' .
		'--header-menu-width:' . esc_attr($header_menu_width_percent) . '%;' .
		'--header-menu-min-width:' . esc_attr($header_menu_min_width_px) . 'px;' .
		'--antonia-content-gap:' . esc_attr($content_top_gap_rem) . 'rem;' .
		'--mobile-footer-clearance:' . esc_attr($mobile_footer_clearance) . 'rem;' .
		'--antonia-base-text-size:' . esc_attr($base_text_size) . 'rem;' .
		'--antonia-header-menu-font-size:' . esc_attr($header_menu_font_size) . 'rem;' .
		'--antonia-header-title-max-size:' . esc_attr($header_title_max_size) . 'rem;' .
		'--color-footer-bg:' . esc_attr($footer_bg_color) . ';' .
		'--color-footer-text:' . esc_attr($footer_text_color) . ';' .
		'--color-footer-heading:' . esc_attr($footer_heading_color) . ';' .
		'--antonia-button-bg:' . esc_attr($button_bg_color) . ';' .
		'--antonia-button-text:' . esc_attr($button_text_color) . ';' .
		'--antonia-button-radius:' . esc_attr($button_radius_rem) . 'rem;' .
		'--antonia-series-tag-text:' . esc_attr($series_tag_text_color) . ';' .
		'--antonia-series-tag-border:' . esc_attr($series_tag_border_color) . ';' .
		'--antonia-series-tag-bg:' . esc_attr($series_tag_bg_color) . ';' .
		'--antonia-book-subtitle-color:' . esc_attr($book_subtitle_color) . ';' .
		'--antonia-series-heading-size:' . esc_attr($series_heading_size) . 'rem;' .
		'--antonia-series-heading-letterspacing:' . esc_attr($series_heading_letterspace) . 'em;' .
		'--antonia-series-description-size:' . esc_attr($series_desc_size) . 'rem;' .
		'--antonia-series-description-line-height:' . esc_attr($series_desc_line_height) . ';' .
		'}</style>';
}
add_action('wp_head', 'antonia_print_customizer_css');
