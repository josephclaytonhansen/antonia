<?php
/**
 * 404.php
 *
 * "Page not found" template.
 */

get_header();
?>

<main>
	<div class="new-content-section">
		<article>
			<h2><?php esc_html_e( '404 – Page Not Found', 'antonia-zanolli' ); ?></h2>
			<p><?php esc_html_e( 'The page you are looking for does not exist.', 'antonia-zanolli' ); ?></p>
			<p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					← <?php esc_html_e( 'Return Home', 'antonia-zanolli' ); ?>
				</a>
			</p>
		</article>
	</div>
</main>

<?php get_footer(); ?>
