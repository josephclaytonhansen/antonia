<?php

/**
 * home.php
 *
 * Blog posts index – used when "Your homepage displays" is set to
 * "A static page" and a Posts page is assigned, or when no front-page
 * override exists.
 */

get_header();
?>

<main>
	<div class="blog-list-container">

		<?php if (have_posts()) : ?>

			<?php while (have_posts()) : the_post(); ?>
				<div class="post-excerpt">
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p class="post-date"><?php echo esc_html(get_the_date()); ?></p>
					<div class="post-preview-content">
						<?php echo antonia_get_post_preview_html(get_the_ID(), 3, 55); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					</div>
					<p><a href="<?php the_permalink(); ?>"><?php esc_html_e('Read More', 'antonia-zanolli'); ?></a></p>
				</div>
			<?php endwhile; ?>

			<div class="pagination">
				<?php
				the_posts_pagination([
					'prev_text' => __('← Older', 'antonia-zanolli'),
					'next_text' => __('Newer →', 'antonia-zanolli'),
				]);
				?>
			</div>

		<?php else : ?>
			<p><?php esc_html_e('No posts found.', 'antonia-zanolli'); ?></p>
		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>