<?php

/**
 * front-page.php
 *
 * Used when a static front page is set in Settings › Reading.
 * Displays the hero section, book carousel, and the three most recent posts.
 */

get_header();
?>

<main>

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->
	<div class="hero">

		<!-- Corner sketch decorations – inside .hero so they scroll with the page
		     and don't jump on mobile (position:absolute relative to .hero). -->
		<?php if (get_theme_mod('antonia_show_corner_sketches', true)) : ?>
			<div class="corner-sketches">
				<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/1.webp'); ?>" alt="" class="corner-sketch corner-sketch-top-left" />
				<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/1.webp'); ?>" alt="" class="corner-sketch corner-sketch-bottom-left" />
				<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/3.webp'); ?>" alt="" class="corner-sketch corner-sketch-top-right" />
				<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/3.webp'); ?>" alt="" class="corner-sketch corner-sketch-bottom-right" />
			</div>
		<?php endif; ?>

		<div class="hero-frame-container">
			<div class="hero-frame-left" id="frame1">
				<div class="hero-mask">
					<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/dragon2.png'); ?>" alt="Dragon" />
				</div>
				<img class="frame-overlay" src="<?php echo esc_url(get_template_directory_uri() . '/frames/oval.png'); ?>" alt="" />
			</div>
			<div class="hero-frame-left" id="frame2">
				<div class="hero-mask-rect">
					<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/dragon.png'); ?>" alt="Dragon" />
				</div>
				<img class="frame-overlay" src="<?php echo esc_url(get_template_directory_uri() . '/frames/rectangle.png'); ?>" alt="" />
			</div>
		</div>

		<!-- Book cover with hover rotation – links to the Books page -->
		<?php
		$_hero_img = get_theme_mod('antonia_hero_book_image', '');
		if (! $_hero_img) {
			$_hero_img = get_template_directory_uri() . '/pictures/abra_01_publish.jpg';
		}
		$_hero_url = get_theme_mod('antonia_hero_book_url', '');
		if (! $_hero_url) {
			$_hero_url = get_post_type_archive_link('book') ?: home_url('/books');
		}
		?>
		<a href="<?php echo esc_url($_hero_url); ?>" class="hero-book-cover" aria-label="<?php esc_attr_e('View all books', 'antonia-zanolli'); ?>">
			<img
				src="<?php echo esc_url($_hero_img); ?>"
				alt="<?php echo esc_attr(get_bloginfo('name')); ?> – book cover" />
		</a>

		<!-- Extra decorative props near the book cover (position fine-tuned via Customizer) -->
		<?php if (get_theme_mod('antonia_show_hero_props', false)) : ?>
			<?php $_hero_prop_left         = get_theme_mod('antonia_hero_prop_left_image', ''); ?>
			<?php $_hero_prop_right        = get_theme_mod('antonia_hero_prop_right_image', ''); ?>
			<?php $_hero_prop_top_left     = get_theme_mod('antonia_hero_prop_top_left_image', ''); ?>
			<?php $_hero_prop_top_right    = get_theme_mod('antonia_hero_prop_top_right_image', ''); ?>
			<?php $_hero_prop_bottom_left  = get_theme_mod('antonia_hero_prop_bottom_left_image', ''); ?>
			<?php $_hero_prop_bottom_right = get_theme_mod('antonia_hero_prop_bottom_right_image', ''); ?>
			<?php if ($_hero_prop_left) : ?>
				<div class="hero-prop hero-prop--left">
					<img src="<?php echo esc_url($_hero_prop_left); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ($_hero_prop_right) : ?>
				<div class="hero-prop hero-prop--right">
					<img src="<?php echo esc_url($_hero_prop_right); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ($_hero_prop_top_left) : ?>
				<div class="hero-prop hero-prop--top-left">
					<img src="<?php echo esc_url($_hero_prop_top_left); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ($_hero_prop_top_right) : ?>
				<div class="hero-prop hero-prop--top-right">
					<img src="<?php echo esc_url($_hero_prop_top_right); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ($_hero_prop_bottom_left) : ?>
				<div class="hero-prop hero-prop--bottom-left">
					<img src="<?php echo esc_url($_hero_prop_bottom_left); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ($_hero_prop_bottom_right) : ?>
				<div class="hero-prop hero-prop--bottom-right">
					<img src="<?php echo esc_url($_hero_prop_bottom_right); ?>" alt="" />
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="scroll-down-container">
			<div class="books-label">
				<?php echo esc_html(get_theme_mod('antonia_scroll_label', __('books', 'antonia-zanolli'))); ?>
				<img
					src="<?php echo esc_url(get_template_directory_uri() . '/ScrollDown.png'); ?>"
					class="scroll-down-icon"
					id="scrolldown"
					alt="<?php esc_attr_e('Scroll down', 'antonia-zanolli'); ?>" />
			</div>
		</div>
	</div><!-- .hero -->

	<!-- ── Main content (carousel + blog excerpts) ───────────────────────── -->
	<div class="new-content-section" id="newContent">

		<!-- Book Carousel – add / reorder books via WP Admin › Books -->
		<div class="book-carousel-container">
			<div class="book-carousel">
				<?php
				$_carousel = get_posts([
					'post_type'      => 'book',
					'posts_per_page' => 6,
					'post_status'    => 'publish',
					'meta_key'       => '_book_carousel_priority',
					'orderby'        => [
						'meta_value_num' => 'DESC',
						'menu_order'     => 'ASC',
					],
				]);
				if ($_carousel) :
					$_active_i = (int) floor(count($_carousel) / 2);
					foreach ($_carousel as $_ci => $_cbook) :
						$_thumb = get_the_post_thumbnail_url($_cbook, 'medium')
							?: get_template_directory_uri() . '/pictures/heartlessprince_kindle_500px.webp';
				?>
						<div class="book-item<?php echo ($_ci === $_active_i) ? ' active' : ''; ?>" data-book-id="<?php echo $_ci; ?>">
							<img src="<?php echo esc_url($_thumb); ?>"
								alt="<?php echo esc_attr(get_the_title($_cbook)); ?>"
								class="book-cover" />
						</div>
					<?php
					endforeach;
				else :
				?>
					<div class="book-item active" data-book-id="0">
						<img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/heartlessprince_kindle_500px.webp'); ?>"
							alt="" class="book-cover" />
					</div>
				<?php endif; ?>
			</div>

			<div class="book-info">
				<p class="book-description">
					This epic fantasy tale weaves together adventure, magic, and destiny in a world where
					nothing is as it seems. Follow the journey of heroes as they face impossible choices.
				</p>
				<a href="#" class="book-read-now" id="bookReadNowLink"><?php esc_html_e('Read Now', 'antonia-zanolli'); ?></a>
			</div>
		</div><!-- .book-carousel-container -->

		<!-- Latest blog excerpts (notepaper cards) -->
		<div class="blog-excerpts">
			<div class="excerpt-list">
				<?php
				$recent_posts = get_posts([
					'numberposts' => 3,
					'post_status' => 'publish',
					'orderby'     => 'date',
					'order'       => 'DESC',
				]);

				foreach ($recent_posts as $post) :
					setup_postdata($post);
				?>
					<div class="excerpt">
						<h3><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>
						<div class="post-preview-content">
							<?php echo antonia_get_post_preview_html(get_the_ID(), 2, 40); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>
					</div>
				<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
			<?php
			$posts_page_id = get_option('page_for_posts');
			$blog_url      = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
			?>
			<p class="blog-excerpts-cta-wrap">
				<a class="book-read-now blog-excerpts-cta" href="<?php echo esc_url($blog_url); ?>">
					<?php esc_html_e('Read the Blog', 'antonia-zanolli'); ?>
				</a>
			</p>
		</div><!-- .blog-excerpts -->

	</div><!-- .new-content-section -->

</main>

<?php get_footer(); ?>
