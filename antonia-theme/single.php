<?php
/**
 * single.php
 *
 * Template for individual blog posts.
 */

get_header();
?>

<main>
	<div class="blog-list-container">

		<?php if ( have_posts() ) : the_post(); ?>

		<article <?php post_class(); ?>>
			<h2><?php the_title(); ?></h2>
			<p class="post-date"><?php echo esc_html( get_the_date() ); ?></p>

			<div class="post-content">
				<?php the_content(); ?>
			</div>

			<div class="wp-post-navigation">
				<p>
					<?php
					$posts_page_id = get_option( 'page_for_posts' );
					$blog_url = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/' );
					?>
					<a href="<?php echo esc_url( $blog_url ); ?>">
						← <?php esc_html_e( 'Back to Blog', 'antonia-zanolli' ); ?>
					</a>
				</p>
			</div>
		</article>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
