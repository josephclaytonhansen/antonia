<?php

/**
 * Antonia Zanolli Theme – functions.php
 *
 * Theme setup, script/style enqueueing, and helper functions.
 */

// ─── Theme Setup ────────────────────────────────────────────────────────────

function antonia_setup()
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'navigation-widgets',
	]);

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

	wp_enqueue_style(
		'antonia-style',
		get_stylesheet_uri(),
		[],
		$theme_version
	);

	wp_enqueue_style(
		'antonia-google-fonts',
		'https://fonts.googleapis.com/css2?family=Caudex:ital,wght@0,400;0,700;1,400;1,700&family=Germania+One&family=Grenze+Gotisch:wght@100..900&family=Grenze:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap',
		[],
		null
	);

	wp_enqueue_script(
		'antonia-mobile-menu',
		get_template_directory_uri() . '/mobile-menu.js',
		[],
		$theme_version,
		true
	);

	if (is_front_page()) {
		wp_enqueue_script(
			'antonia-carousel',
			get_template_directory_uri() . '/carousel.js',
			[],
			$theme_version,
			true
		);

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

		wp_localize_script('antonia-carousel', 'antoniaCarouselBooks', $_carousel_data);
	}

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
 */
function antonia_get_series_landing_url($term)
{
	if (! $term || is_wp_error($term)) {
		return '';
	}

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
 * Sanitize signed integer customizer fields (allow negative values for offsets).
 */
function antonia_sanitize_signed_int($value, $min, $max, $default)
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
 * Sanitize checkbox-style settings.
 */
function antonia_sanitize_checkbox($value)
{
	return ! empty($value);
}

/**
 * Sanitize a select control against an allowed set of keys.
 */
function antonia_sanitize_select($value, $choices, $default)
{
	$value = sanitize_key((string) $value);
	return array_key_exists($value, $choices) ? $value : $default;
}

/**
 * Build display-ready buy-link buttons for a book.
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

// ─── wp_body_open support ───────────────────────────────────────────────────

if (! function_exists('wp_body_open')) {
	function wp_body_open()
	{
		do_action('wp_body_open');
	}
}

// ─── Custom Nav-Menu Walkers ──────────────────────────────────────────────────

class Antonia_Header_Walker extends Walker_Nav_Menu
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
		$output .= "<a{$attributes}><h3>" . esc_html($title) . "</h3></a>\n";
	}

	public function end_el(&$output, $data_object, $depth = 0, $args = null) {}
}

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

function antonia_register_books()
{
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
		'hierarchical'       => true,
		'public'             => true,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'rewrite'            => ['slug' => 'book-series'],
	]);

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
		'has_archive'         => 'books',
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
	$subtitle          = get_post_meta($post->ID, '_book_subtitle', true);
	$buy_link          = get_post_meta($post->ID, '_book_buy_link', true);
	$buy_links         = get_post_meta($post->ID, '_book_buy_links', true);
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
			if ($_line === '') continue;

			$_parts = array_map('trim', explode('|', $_line, 2));
			if (count($_parts) !== 2) continue;

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

function antonia_customize_register($wp_customize)
{

	// ── Section: Homepage Hero ────────────────────────────────────────────
	$wp_customize->add_section('antonia_hero', [
		'title'    => __('Homepage Hero', 'antonia-zanolli'),
		'priority' => 30,
	]);

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

	$wp_customize->add_setting('antonia_scroll_badge_bg_color', [
		'default'           => '#3d1414',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_scroll_badge_bg_color', [
		'label'   => __('Hero Scroll Badge Background', 'antonia-zanolli'),
		'section' => 'antonia_hero',
	]));

	$wp_customize->add_setting('antonia_scroll_badge_text_color', [
		'default'           => '#d4c5b0',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_scroll_badge_text_color', [
		'label'   => __('Hero Scroll Badge Text', 'antonia-zanolli'),
		'section' => 'antonia_hero',
	]));

	// ── Section: Footer ───────────────────────────────────────────────────
	$wp_customize->add_section('antonia_footer', [
		'title'    => __('Footer', 'antonia-zanolli'),
		'priority' => 35,
	]);

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
		'description' => __('How much space each side menu group takes on desktop (16–45). Default: 30.', 'antonia-zanolli'),
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
		'description' => __('Minimum width for each side menu on desktop (140–520 px). Default: 300.', 'antonia-zanolli'),
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
		'label'       => __('Desktop: Gap Below Menu (rem)', 'antonia-zanolli'),
		'description' => __('Space from the fixed header down to page content on desktop (1–8 rem). Default: 3. Increase to push content lower; decrease to bring it closer.', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 1, 'max' => 8],
	]);

	$wp_customize->add_setting('antonia_content_top_gap_mobile_rem', [
		'default'           => 1.5,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0, 6, 1.5);
		},
	]);
	$wp_customize->add_control('antonia_content_top_gap_mobile_rem', [
		'label'       => __('Mobile: Gap Below Menu (rem)', 'antonia-zanolli'),
		'description' => __('Space from the fixed header down to page content on mobile (0–6 rem). Default: 1.5. Increase to push content lower; decrease to bring it closer.', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 0, 'max' => 6],
	]);

	$wp_customize->add_setting('antonia_menu_title_gap_rem', [
		'default'           => 0,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0, 4, 0);
		},
	]);
	$wp_customize->add_control('antonia_menu_title_gap_rem', [
		'label'       => __('Desktop: Gap Between Menu Items & Site Title (rem)', 'antonia-zanolli'),
		'description' => __('Adds horizontal padding between the left/right menus and the site title in the centre on desktop (0–4 rem). Default: 0. Increase to push the menu items outward.', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 0, 'max' => 4],
	]);

	$wp_customize->add_setting('antonia_mobile_footer_clearance_rem', [
		'default'           => 1,
		'sanitize_callback' => function ($value) {
			return antonia_sanitize_float_range($value, 0, 4, 1);
		},
	]);
	$wp_customize->add_control('antonia_mobile_footer_clearance_rem', [
		'label'       => __('Mobile Footer Extra Bottom Padding (rem)', 'antonia-zanolli'),
		'description' => __('Extra bottom padding inside the mobile footer (0–4 rem). Default: 1.', 'antonia-zanolli'),
		'section'     => 'antonia_layout',
		'type'        => 'number',
		'input_attrs' => ['step' => 0.1, 'min' => 0, 'max' => 4],
	]);

	// ── Section: Theme Colors ───────────────────────────────────────────
	$wp_customize->add_section('antonia_theme_colors', [
		'title'       => __('Theme Colors', 'antonia-zanolli'),
		'priority'    => 37,
		'description' => __('General site colors, background pattern toggle, and title hover color.', 'antonia-zanolli'),
	]);

	$wp_customize->add_setting('antonia_site_bg_color', [
		'default'           => '#1f1b15',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_site_bg_color', [
		'label'   => __('Site Background Color', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_content_bg_color', [
		'default'           => '#241C15',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_content_bg_color', [
		'label'       => __('Content & Gradient Background Color', 'antonia-zanolli'),
		'description' => __('The colour used for the gradient that fades in from the hero, the content area background, and any gaps between the header and content. Default: #241C15.', 'antonia-zanolli'),
		'section'     => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_bg_tint_color', [
		'default'           => '#362316',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_bg_tint_color', [
		'label'       => __('Background Tint Color', 'antonia-zanolli'),
		'description' => __('The colour overlaid on top of the background pattern. Use "Show Background Tint Overlay" below to hide it entirely.', 'antonia-zanolli'),
		'section'     => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_show_background_tint', [
		'default'           => true,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_show_background_tint', [
		'label'       => __('Show Background Tint Overlay', 'antonia-zanolli'),
		'description' => __('Uncheck to remove the tint and show the background colour / pattern without any overlay.', 'antonia-zanolli'),
		'section'     => 'antonia_theme_colors',
		'type'        => 'checkbox',
	]);

	$wp_customize->add_setting('antonia_main_text_color', [
		'default'           => '#fcecd8',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_main_text_color', [
		'label'   => __('Main Text Color', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_hover_accent_color', [
		'default'           => '#c77e3f',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_hover_accent_color', [
		'label'   => __('Hover Accent Color', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_site_title_hover_color', [
		'default'           => '#d9b992',
		'sanitize_callback' => 'sanitize_hex_color',
	]);
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'antonia_site_title_hover_color', [
		'label'   => __('Site Title Hover Color', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
	]));

	$wp_customize->add_setting('antonia_show_background_pattern', [
		'default'           => true,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_show_background_pattern', [
		'label'   => __('Show Background Pattern', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
		'type'    => 'checkbox',
	]);

	$wp_customize->add_setting('antonia_hide_page_titles', [
		'default'           => false,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_hide_page_titles', [
		'label'   => __('Hide Page Titles', 'antonia-zanolli'),
		'section' => 'antonia_theme_colors',
		'type'    => 'checkbox',
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

	// ── Section: Hero Decorations ───────────────────────────────────────
	$wp_customize->add_section('antonia_hero_decor', [
		'title'       => __('Hero Decorations', 'antonia-zanolli'),
		'priority'    => 36,
		'description' => __('Optional decorative images beside and around the homepage hero book cover. Use the offset controls to fine-tune each image\'s position.', 'antonia-zanolli'),
	]);

	// ── Framed paintings (oval + rectangle dragon frames) ──
	$wp_customize->add_setting('antonia_show_hero_frames', [
		'default'           => true,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_show_hero_frames', [
		'label'       => __('Show Framed Paintings', 'antonia-zanolli'),
		'description' => __('Toggle the oval and rectangle framed paintings visible in the hero section.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'checkbox',
	]);

	// ── Corner sketches toggle ──
	$wp_customize->add_setting('antonia_show_corner_sketches', [
		'default'           => true,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_show_corner_sketches', [
		'label'       => __('Show Framed Paintings (Corner Sketches)', 'antonia-zanolli'),
		'description' => __('Toggle the four decorative framed paintings in the corners of the homepage hero.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'checkbox',
	]);

	// ── Hero props (the extra images around the book cover) ──
	$wp_customize->add_setting('antonia_show_hero_props', [
		'default'           => false,
		'sanitize_callback' => 'antonia_sanitize_checkbox',
	]);
	$wp_customize->add_control('antonia_show_hero_props', [
		'label'       => __('Show Additional Hero Images', 'antonia-zanolli'),
		'description' => __('Toggle the extra decorative images placed around the hero book cover.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'checkbox',
	]);

	// ── Left prop ──
	$wp_customize->add_setting('antonia_hero_prop_left_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_left_image', [
		'label'   => __('Left Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_left_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_left_offset_x', [
		'label'       => __('Left Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'description' => __('Negative = left, positive = right.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_left_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_left_offset_y', [
		'label'       => __('Left Image – Vertical Offset (px)', 'antonia-zanolli'),
		'description' => __('Negative = up, positive = down.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);

	// ── Right prop ──
	$wp_customize->add_setting('antonia_hero_prop_right_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_right_image', [
		'label'   => __('Right Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_right_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_right_offset_x', [
		'label'       => __('Right Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'description' => __('Negative = left, positive = right.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_right_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_right_offset_y', [
		'label'       => __('Right Image – Vertical Offset (px)', 'antonia-zanolli'),
		'description' => __('Negative = up, positive = down.', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);

	// ── Top-left prop ──
	$wp_customize->add_setting('antonia_hero_prop_top_left_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_top_left_image', [
		'label'   => __('Top-Left Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_top_left_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_top_left_offset_x', [
		'label'       => __('Top-Left Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_top_left_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_top_left_offset_y', [
		'label'       => __('Top-Left Image – Vertical Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);

	// ── Top-right prop ──
	$wp_customize->add_setting('antonia_hero_prop_top_right_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_top_right_image', [
		'label'   => __('Top-Right Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_top_right_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_top_right_offset_x', [
		'label'       => __('Top-Right Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_top_right_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_top_right_offset_y', [
		'label'       => __('Top-Right Image – Vertical Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);

	// ── Bottom-left prop ──
	$wp_customize->add_setting('antonia_hero_prop_bottom_left_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_bottom_left_image', [
		'label'   => __('Bottom-Left Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_bottom_left_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_bottom_left_offset_x', [
		'label'       => __('Bottom-Left Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_bottom_left_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_bottom_left_offset_y', [
		'label'       => __('Bottom-Left Image – Vertical Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);

	// ── Bottom-right prop ──
	$wp_customize->add_setting('antonia_hero_prop_bottom_right_image', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	]);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'antonia_hero_prop_bottom_right_image', [
		'label'   => __('Bottom-Right Hero Image', 'antonia-zanolli'),
		'section' => 'antonia_hero_decor',
	]));
	$wp_customize->add_setting('antonia_hero_prop_bottom_right_offset_x', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_bottom_right_offset_x', [
		'label'       => __('Bottom-Right Image – Horizontal Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
	]);
	$wp_customize->add_setting('antonia_hero_prop_bottom_right_offset_y', [
		'default'           => 0,
		'sanitize_callback' => function ($v) { return antonia_sanitize_signed_int($v, -400, 400, 0); },
	]);
	$wp_customize->add_control('antonia_hero_prop_bottom_right_offset_y', [
		'label'       => __('Bottom-Right Image – Vertical Offset (px)', 'antonia-zanolli'),
		'section'     => 'antonia_hero_decor',
		'type'        => 'number',
		'input_attrs' => ['step' => 4, 'min' => -400, 'max' => 400],
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

	$wp_customize->add_setting('antonia_series_title_transform', [
		'default'           => 'uppercase',
		'sanitize_callback' => function ($value) {
			$choices = [
				'uppercase'  => __('Uppercase', 'antonia-zanolli'),
				'lowercase'  => __('Lowercase', 'antonia-zanolli'),
				'capitalize' => __('Capitalize', 'antonia-zanolli'),
				'none'       => __('None', 'antonia-zanolli'),
			];
			return antonia_sanitize_select($value, $choices, 'uppercase');
		},
	]);
	$wp_customize->add_control('antonia_series_title_transform', [
		'label'   => __('Series Title Case', 'antonia-zanolli'),
		'section' => 'antonia_books_style',
		'type'    => 'select',
		'choices' => [
			'uppercase'  => __('Uppercase', 'antonia-zanolli'),
			'lowercase'  => __('Lowercase', 'antonia-zanolli'),
			'capitalize' => __('Capitalize', 'antonia-zanolli'),
			'none'       => __('None', 'antonia-zanolli'),
		],
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
	$site_bg_color              = sanitize_hex_color(get_theme_mod('antonia_site_bg_color', '#1f1b15')) ?: '#1f1b15';
	$content_bg_color           = sanitize_hex_color(get_theme_mod('antonia_content_bg_color', '#241C15')) ?: '#241C15';
	$bg_tint_color              = sanitize_hex_color(get_theme_mod('antonia_bg_tint_color', '#362316')) ?: '#362316';
	$main_text_color            = sanitize_hex_color(get_theme_mod('antonia_main_text_color', '#fcecd8')) ?: '#fcecd8';
	$hover_accent_color         = sanitize_hex_color(get_theme_mod('antonia_hover_accent_color', '#c77e3f')) ?: '#c77e3f';
	$site_title_hover_color     = sanitize_hex_color(get_theme_mod('antonia_site_title_hover_color', '#d9b992')) ?: '#d9b992';
	$header_menu_width_percent  = antonia_sanitize_int_range(get_theme_mod('antonia_header_menu_width_percent', 30), 16, 45, 30);
	$header_menu_min_width_px   = antonia_sanitize_int_range(get_theme_mod('antonia_header_menu_min_width_px', 300), 140, 520, 300);
	$content_top_gap_rem        = antonia_sanitize_float_range(get_theme_mod('antonia_content_top_gap_rem', 3), 1, 8, 3);
	$content_top_gap_mobile_rem = antonia_sanitize_float_range(get_theme_mod('antonia_content_top_gap_mobile_rem', 1.5), 0, 6, 1.5);
	$menu_title_gap_rem         = antonia_sanitize_float_range(get_theme_mod('antonia_menu_title_gap_rem', 0), 0, 4, 0);
	$mobile_footer_clearance    = antonia_sanitize_float_range(get_theme_mod('antonia_mobile_footer_clearance_rem', 1), 0, 4, 1);
	$base_text_size             = antonia_sanitize_float_range(get_theme_mod('antonia_base_text_size_rem', 1.5), 1, 2.4, 1.5);
	$header_menu_font_size      = antonia_sanitize_float_range(get_theme_mod('antonia_header_menu_font_size_rem', 1.5), 1, 2.2, 1.5);
	$header_title_max_size      = antonia_sanitize_float_range(get_theme_mod('antonia_header_title_max_size_rem', 4), 3, 6, 4);
	$scroll_badge_bg            = sanitize_hex_color(get_theme_mod('antonia_scroll_badge_bg_color', '#3d1414')) ?: '#3d1414';
	$scroll_badge_text          = sanitize_hex_color(get_theme_mod('antonia_scroll_badge_text_color', '#d4c5b0')) ?: '#d4c5b0';
	$footer_bg_color            = sanitize_hex_color(get_theme_mod('antonia_footer_bg_color', '#0d0c0a')) ?: '#0d0c0a';
	$footer_text_color          = sanitize_hex_color(get_theme_mod('antonia_footer_text_color', '#523717')) ?: '#523717';
	$footer_heading_color       = sanitize_hex_color(get_theme_mod('antonia_footer_heading_color', '#3a352c')) ?: '#3a352c';
	$button_bg_color            = sanitize_hex_color(get_theme_mod('antonia_button_bg_color', '#c77e3f')) ?: '#c77e3f';
	$button_text_color          = sanitize_hex_color(get_theme_mod('antonia_button_text_color', '#1f1b15')) ?: '#1f1b15';
	$button_radius_rem          = antonia_sanitize_float_range(get_theme_mod('antonia_button_radius_rem', 0.25), 0, 2, 0.25);
	$series_heading_size        = antonia_sanitize_float_range(get_theme_mod('antonia_series_heading_size', 2), 1.2, 4, 2);
	$series_heading_letterspace = antonia_sanitize_float_range(get_theme_mod('antonia_series_heading_letterspacing', 0.15), 0, 0.5, 0.15);
	$series_desc_size           = antonia_sanitize_float_range(get_theme_mod('antonia_series_description_size', 1), 0.8, 2, 1);
	$series_desc_line_height    = antonia_sanitize_float_range(get_theme_mod('antonia_series_description_lineheight', 1.6), 1, 2.4, 1.6);
	$series_title_transform     = antonia_sanitize_select(get_theme_mod('antonia_series_title_transform', 'uppercase'), [
		'uppercase'  => 'uppercase',
		'lowercase'  => 'lowercase',
		'capitalize' => 'capitalize',
		'none'       => 'none',
	], 'uppercase');

	// Hero prop offsets
	$prop_left_x        = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_left_offset_x', 0), -400, 400, 0);
	$prop_left_y        = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_left_offset_y', 0), -400, 400, 0);
	$prop_right_x       = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_right_offset_x', 0), -400, 400, 0);
	$prop_right_y       = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_right_offset_y', 0), -400, 400, 0);
	$prop_tl_x          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_top_left_offset_x', 0), -400, 400, 0);
	$prop_tl_y          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_top_left_offset_y', 0), -400, 400, 0);
	$prop_tr_x          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_top_right_offset_x', 0), -400, 400, 0);
	$prop_tr_y          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_top_right_offset_y', 0), -400, 400, 0);
	$prop_bl_x          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_bottom_left_offset_x', 0), -400, 400, 0);
	$prop_bl_y          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_bottom_left_offset_y', 0), -400, 400, 0);
	$prop_br_x          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_bottom_right_offset_x', 0), -400, 400, 0);
	$prop_br_y          = antonia_sanitize_signed_int(get_theme_mod('antonia_hero_prop_bottom_right_offset_y', 0), -400, 400, 0);

	echo '<style id="antonia-customizer-vars">:root{' .
		'--bg-tint:' . esc_attr($bg_tint_color) . ';' .
		'--color-dark-bg:' . esc_attr($site_bg_color) . ';' .
		'--antonia-gradient-color:' . esc_attr($content_bg_color) . ';' .
		'--color-light-text:' . esc_attr($main_text_color) . ';' .
		'--color-hover-accent:' . esc_attr($hover_accent_color) . ';' .
		'--color-site-title-hover:' . esc_attr($site_title_hover_color) . ';' .
		'--header-menu-width:' . esc_attr($header_menu_width_percent) . '%;' .
		'--header-menu-min-width:' . esc_attr($header_menu_min_width_px) . 'px;' .
		'--antonia-menu-title-gap:' . esc_attr($menu_title_gap_rem) . 'rem;' .
		'--antonia-content-gap:' . esc_attr($content_top_gap_rem) . 'rem;' .
		'--antonia-content-gap-mobile:' . esc_attr($content_top_gap_mobile_rem) . 'rem;' .
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
		'--antonia-scroll-badge-bg:' . esc_attr($scroll_badge_bg) . ';' .
		'--antonia-scroll-badge-text:' . esc_attr($scroll_badge_text) . ';' .
		'--antonia-series-tag-text:' . esc_attr($series_tag_text_color) . ';' .
		'--antonia-series-tag-border:' . esc_attr($series_tag_border_color) . ';' .
		'--antonia-series-tag-bg:' . esc_attr($series_tag_bg_color) . ';' .
		'--antonia-book-subtitle-color:' . esc_attr($book_subtitle_color) . ';' .
		'--antonia-series-heading-size:' . esc_attr($series_heading_size) . 'rem;' .
		'--antonia-series-heading-letterspacing:' . esc_attr($series_heading_letterspace) . 'em;' .
		'--antonia-series-description-size:' . esc_attr($series_desc_size) . 'rem;' .
		'--antonia-series-title-transform:' . esc_attr($series_title_transform) . ';' .
		'--antonia-series-description-line-height:' . esc_attr($series_desc_line_height) . ';' .
		'--hero-prop-left-x:' . esc_attr($prop_left_x) . 'px;' .
		'--hero-prop-left-y:' . esc_attr($prop_left_y) . 'px;' .
		'--hero-prop-right-x:' . esc_attr($prop_right_x) . 'px;' .
		'--hero-prop-right-y:' . esc_attr($prop_right_y) . 'px;' .
		'--hero-prop-top-left-x:' . esc_attr($prop_tl_x) . 'px;' .
		'--hero-prop-top-left-y:' . esc_attr($prop_tl_y) . 'px;' .
		'--hero-prop-top-right-x:' . esc_attr($prop_tr_x) . 'px;' .
		'--hero-prop-top-right-y:' . esc_attr($prop_tr_y) . 'px;' .
		'--hero-prop-bottom-left-x:' . esc_attr($prop_bl_x) . 'px;' .
		'--hero-prop-bottom-left-y:' . esc_attr($prop_bl_y) . 'px;' .
		'--hero-prop-bottom-right-x:' . esc_attr($prop_br_x) . 'px;' .
		'--hero-prop-bottom-right-y:' . esc_attr($prop_br_y) . 'px;' .
		'}</style>';
}
add_action('wp_head', 'antonia_print_customizer_css');

/**
 * Add theme state classes to the body element.
 */
function antonia_body_classes($classes)
{
	if (! get_theme_mod('antonia_show_background_pattern', true)) {
		$classes[] = 'antonia-no-background-pattern';
	}

	if (! get_theme_mod('antonia_show_background_tint', true)) {
		$classes[] = 'antonia-no-background-tint';
	}

	if (get_theme_mod('antonia_hide_page_titles', false)) {
		$classes[] = 'antonia-hide-page-titles';
	}

	// Hide framed paintings (oval + rectangle frames in hero)
	if (! get_theme_mod('antonia_show_hero_frames', true)) {
		$classes[] = 'antonia-hide-hero-frames';
	}

	// Hide corner sketches
	if (! get_theme_mod('antonia_show_corner_sketches', true)) {
		$classes[] = 'antonia-hide-corner-sketches';
	}

	return $classes;
}
add_filter('body_class', 'antonia_body_classes');
