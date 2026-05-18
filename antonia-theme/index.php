<?php

/**
 * index.php
 *
 * Required WordPress fallback template. Used when no more-specific
 * template (front-page.php, home.php, single.php, page.php, etc.) applies.
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
				<?php the_posts_pagination(); ?>
			</div>

		<?php else : ?>
			<p><?php esc_html_e('Nothing here yet.', 'antonia-zanolli'); ?></p>
		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>