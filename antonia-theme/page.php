<?php
/**
 * page.php
 *
 * Generic page template – used for Imprint, Privacy Policy, and any other
 * static WordPress pages.
 */

get_header();
?>

<main>
	<div class="new-content-section">

		<?php if ( have_posts() ) : the_post(); ?>

		<article <?php post_class(); ?>>
			<h2><?php the_title(); ?></h2>
			<div class="post-content">
				<?php the_content(); ?>
			</div>
		</article>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
