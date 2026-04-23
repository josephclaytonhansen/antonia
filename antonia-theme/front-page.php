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

	<!-- ── Corner sketch decorations (homepage only) ─────────────────────── -->
	<div class="corner-sketches">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/1.webp' ); ?>" alt="" class="corner-sketch corner-sketch-top-left" />
		<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/1.webp' ); ?>" alt="" class="corner-sketch corner-sketch-bottom-left" />
		<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/3.webp' ); ?>" alt="" class="corner-sketch corner-sketch-top-right" />
		<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/3.webp' ); ?>" alt="" class="corner-sketch corner-sketch-bottom-right" />
	</div>

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->
	<div class="hero">
		<div class="hero-frame-container">
			<div class="hero-frame-left" id="frame1">
				<div class="hero-mask">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/dragon2.png' ); ?>" alt="Dragon" />
				</div>
				<img class="frame-overlay" src="<?php echo esc_url( get_template_directory_uri() . '/frames/oval.png' ); ?>" alt="" />
			</div>
			<div class="hero-frame-left" id="frame2">
				<div class="hero-mask-rect">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/dragon.png' ); ?>" alt="Dragon" />
				</div>
				<img class="frame-overlay" src="<?php echo esc_url( get_template_directory_uri() . '/frames/rectangle.png' ); ?>" alt="" />
			</div>
		</div>

		<!-- Book cover with hover rotation -->
		<div class="hero-book-cover">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/pictures/abra_01_publish.jpg' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> – book cover" />
		</div>

		<div class="scroll-down-container">
			<div class="books-label">
				<?php esc_html_e( 'books', 'antonia-zanolli' ); ?>
				<img
					src="<?php echo esc_url( get_template_directory_uri() . '/ScrollDown.png' ); ?>"
					class="scroll-down-icon"
					id="scrolldown"
					alt="<?php esc_attr_e( 'Scroll down', 'antonia-zanolli' ); ?>" />
			</div>
		</div>
	</div><!-- .hero -->

	<!-- ── Main content (carousel + blog excerpts) ───────────────────────── -->
	<div class="new-content-section" id="newContent">

		<!-- Book Carousel -->
		<div class="book-carousel-container">
			<div class="book-carousel">
				<div class="book-item" data-book-id="0">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/iceandfire_500px.webp' ); ?>"
					     alt="<?php esc_attr_e( 'Ice and Fire', 'antonia-zanolli' ); ?>"
					     class="book-cover" />
				</div>
				<div class="book-item active" data-book-id="1">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/heartlessprince_kindle_500px.webp' ); ?>"
					     alt="<?php esc_attr_e( 'The Heartless Prince', 'antonia-zanolli' ); ?>"
					     class="book-cover" />
				</div>
				<div class="book-item" data-book-id="2">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/pictures/heartlessprince_kindle_500px.webp' ); ?>"
					     alt="<?php esc_attr_e( 'The Heartless Prince II', 'antonia-zanolli' ); ?>"
					     class="book-cover" />
				</div>
			</div>

			<div class="book-info">
				<p class="book-description">
					This epic fantasy tale weaves together adventure, magic, and destiny in a world where
					nothing is as it seems. Follow the journey of heroes as they face impossible choices.
				</p>
				<a href="#" class="book-read-now" id="bookReadNowLink"><?php esc_html_e( 'Read Now', 'antonia-zanolli' ); ?></a>
			</div>
		</div><!-- .book-carousel-container -->

		<!-- Latest blog excerpts (notepaper cards) -->
		<div class="blog-excerpts">
			<div class="excerpt-list">
				<?php
				$recent_posts = get_posts( [
					'numberposts' => 3,
					'post_status' => 'publish',
					'orderby'     => 'date',
					'order'       => 'DESC',
				] );

				foreach ( $recent_posts as $post ) :
					setup_postdata( $post );
				?>
				<div class="excerpt">
					<h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h3>
					<?php the_excerpt(); ?>
				</div>
				<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</div><!-- .blog-excerpts -->

	</div><!-- .new-content-section -->

</main>

<?php get_footer(); ?>
