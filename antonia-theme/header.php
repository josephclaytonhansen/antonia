<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<!-- Google Fonts preconnect hints (the actual stylesheet is enqueued via functions.php) -->
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<header>
		<div class="header-left">
			<?php
			wp_nav_menu([
				'theme_location' => 'header-left',
				'container'      => false,
				'menu_class'     => 'header-menu',
				'depth'          => 2,
				'fallback_cb'    => false,
			]);
			?>
		</div>
		<h1><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></h1>
		<div class="header-right">
			<?php
			wp_nav_menu([
				'theme_location' => 'header-right',
				'container'      => false,
				'menu_class'     => 'header-menu',
				'depth'          => 2,
				'fallback_cb'    => false,
			]);
			?>
		</div>
		<div class="mobile-menu-icon">
			<button
				id="mobileMenuButton"
				class="mobile-menu-button"
				aria-controls="mobileDrawer"
				aria-expanded="false"
				aria-label="<?php esc_attr_e('Open menu', 'antonia-zanolli'); ?>">
				<img src="<?php echo esc_url(get_template_directory_uri() . '/hamburger.png'); ?>" alt="" width="30" />
			</button>
		</div>
	</header>

	<!-- Mobile drawer – content driven by the "Mobile Drawer" menu in WP Admin -->
	<aside id="mobileDrawer" class="mobile-drawer drawer--right" aria-hidden="true">
		<div class="drawer-header">
			<div class="drawer-title"><?php bloginfo('name'); ?></div>
			<button class="drawer-close" aria-label="<?php esc_attr_e('Close menu', 'antonia-zanolli'); ?>">×</button>
		</div>
		<nav class="drawer-content" role="navigation" aria-label="<?php esc_attr_e('Mobile navigation', 'antonia-zanolli'); ?>">
			<?php
			wp_nav_menu([
				'theme_location' => 'mobile',
				'container'      => false,
				'menu_class'     => 'drawer-menu',
				'depth'          => 3,
				'fallback_cb'    => false,
			]);
			?>
		</nav>
	</aside>
	<div id="drawerBackdrop" class="drawer-backdrop"></div>