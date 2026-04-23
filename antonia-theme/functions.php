<?php
/**
 * Antonia Zanolli Theme – functions.php
 *
 * Theme setup, script/style enqueueing, and helper functions.
 */

// ─── Theme Setup ────────────────────────────────────────────────────────────

function antonia_setup() {
	// Let WordPress manage <title>
	add_theme_support( 'title-tag' );

	// Featured images on posts and pages
	add_theme_support( 'post-thumbnails' );

	// HTML5 markup support
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'navigation-widgets',
	] );

	// Navigation menus
	register_nav_menus( [
		'header-left'  => __( 'Header – Left',    'antonia-zanolli' ),
		'header-right' => __( 'Header – Right',   'antonia-zanolli' ),
		'mobile'       => __( 'Mobile Drawer',    'antonia-zanolli' ),
		'footer'       => __( 'Footer Links',     'antonia-zanolli' ),
	] );
}
add_action( 'after_setup_theme', 'antonia_setup' );

// ─── Enqueue Scripts & Styles ────────────────────────────────────────────────

function antonia_enqueue_scripts() {
	// Main theme stylesheet (style.css in theme root)
	wp_enqueue_style(
		'antonia-style',
		get_stylesheet_uri(),
		[],
		'1.0.0'
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
		'1.0.0',
		true  // footer
	);

	// Book carousel – front page only
	if ( is_front_page() ) {
		wp_enqueue_script(
			'antonia-carousel',
			get_template_directory_uri() . '/carousel.js',
			[],
			'1.0.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'antonia_enqueue_scripts' );

// ─── Excerpt Customisation ────────────────────────────────────────────────────

/**
 * Shorter excerpts for the homepage notepaper cards.
 */
function antonia_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'antonia_excerpt_length', 999 );

function antonia_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'antonia_excerpt_more' );

// ─── Add wp_body_open support for older WordPress versions ───────────────────

if ( ! function_exists( 'wp_body_open' ) ) {
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}

// ─── Custom Nav-Menu Walkers ──────────────────────────────────────────────────

/**
 * Header walker: outputs <a href="…"><h3>Label</h3></a> per item.
 * Used for the desktop header-left and header-right menus.
 */
class Antonia_Header_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( $depth > 0 ) return; // top-level items only

		$item = $data_object;

		$atts = [
			'href'   => ! empty( $item->url )    ? $item->url    : home_url( '/' ),
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn )    ? $item->xfn    : '',
		];

		if ( in_array( 'current-menu-item', (array) $item->classes, true ) ) {
			$atts['aria-current'] = 'page';
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( '' !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= " {$attr}=\"$value\"";
			}
		}

		$title   = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth );
		$output .= "<a{$attributes}><h3>" . esc_html( $title ) . "</h3></a>\n";
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}
}

/**
 * Plain-link walker: outputs <a href="…">Label</a> per item.
 * Used for the mobile drawer and footer menus.
 */
class Antonia_Plain_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( $depth > 0 ) return;

		$item = $data_object;

		$atts = [
			'href'   => ! empty( $item->url )    ? $item->url    : home_url( '/' ),
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn )    ? $item->xfn    : '',
		];

		if ( in_array( 'current-menu-item', (array) $item->classes, true ) ) {
			$atts['aria-current'] = 'page';
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( '' !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= " {$attr}=\"$value\"";
			}
		}

		$title   = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth );
		$output .= "<a{$attributes}>" . esc_html( $title ) . "</a>\n";
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}
}
