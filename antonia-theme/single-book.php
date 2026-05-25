<?php

/**
 * single-book.php
 *
 * Individual book page – displayed at /books/book-slug/.
 *
 * Displays:
 *  – Featured image as the book cover (left column)
 *  – Title, subtitle, series link, description (editor content), buy button (right)
 */

get_header();
?>

<main>
    <div class="single-book-container">
        <div class="wp-post-navigation single-book-nav-top">
            <a href="<?php echo esc_url(get_post_type_archive_link('book')); ?>">
                ← <?php esc_html_e('All Books', 'antonia-zanolli'); ?>
            </a>
        </div>

        <?php if (have_posts()) : the_post(); ?>

            <?php
            $subtitle = get_post_meta(get_the_ID(), '_book_subtitle', true);
            $buy_links = antonia_get_book_buy_links(get_the_ID());
            $series   = get_the_terms(get_the_ID(), 'book_series');
            ?>

            <article <?php post_class('single-book-inner'); ?>>

                <!-- Cover image -->
                <div class="single-book-cover">
                    <?php if (has_post_thumbnail()) :
                        the_post_thumbnail('large', ['alt' => get_the_title()]);
                    else : ?>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/abra_01_publish.jpg'); ?>"
                            alt="<?php echo esc_attr(get_the_title()); ?>" />
                    <?php endif; ?>

                    <?php if (! empty($buy_links)) : ?>
                        <div class="single-book-buy-group" aria-label="<?php esc_attr_e('Buy links', 'antonia-zanolli'); ?>">
                            <?php foreach ($buy_links as $buy_item) : ?>
                                <a href="<?php echo esc_url($buy_item['url']); ?>"
                                    class="single-book-buy"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <?php echo esc_html($buy_item['label']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Book info -->
                <div class="single-book-meta">

                    <?php if ($series && ! is_wp_error($series)) :
                        $term = $series[0];
                        $series_link = antonia_get_series_landing_url($term);
                    ?>
                        <a href="<?php echo esc_url($series_link); ?>" class="single-book-series-link">
                            <?php echo esc_html($term->name); ?>
                        </a>
                    <?php endif; ?>

                    <h1><?php the_title(); ?></h1>

                    <?php if ($subtitle) : ?>
                        <p class="single-book-subtitle"><?php echo esc_html($subtitle); ?></p>
                    <?php endif; ?>

                    <div class="single-book-description post-content">
                        <?php the_content(); ?>
                    </div>

                    <div class="wp-post-navigation" style="margin-top:2rem">
                        <a href="<?php echo esc_url(get_post_type_archive_link('book')); ?>">
                            ← <?php esc_html_e('All Books', 'antonia-zanolli'); ?>
                        </a>
                    </div>

                </div><!-- .single-book-meta -->

            </article>

        <?php endif; ?>

    </div><!-- .single-book-container -->
</main>

<?php get_footer(); ?>