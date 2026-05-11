<?php

/**
 * archive-book.php
 *
 * Books overview page – displayed at /books/.
 * Books are grouped by their "Book Series" taxonomy term, then shown as
 * circular cover thumbnails (Sanderson-style overview grid).
 *
 * To set up:
 *  1. Go to WP Admin › Books › Add New to create book entries.
 *  2. Assign each book to a "Book Series" term (Books › Book Series).
 *  3. Set a Featured Image for each book (used as the circular thumbnail).
 *  4. Visit Settings › Permalinks and click Save to flush rewrite rules.
 */

get_header();
?>

<main>
    <div class="books-archive">

        <h1><?php esc_html_e('Books', 'antonia-zanolli'); ?></h1>

        <?php
        // ── Fetch every published book ──────────────────────────────────────
        $all_books = get_posts([
            'post_type'      => 'book',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if (! $all_books) : ?>
            <p style="text-align:center;color:var(--color-light-text)">
                <?php esc_html_e('No books found yet. Add your first book in WP Admin › Books.', 'antonia-zanolli'); ?>
            </p>
            <?php
        else :
            // ── Group by series ─────────────────────────────────────────────
            $series_groups = [];        // [ term_id => [ 'term' => $term, 'books' => [] ] ]
            $unsorted      = [];        // Books with no series assigned

            foreach ($all_books as $book) {
                $terms = get_the_terms($book->ID, 'book_series');
                if ($terms && ! is_wp_error($terms)) {
                    // A book can belong to multiple series; assign to the first one
                    $term = $terms[0];
                    if (! isset($series_groups[$term->term_id])) {
                        $series_groups[$term->term_id] = [
                            'term'  => $term,
                            'books' => [],
                        ];
                    }
                    $series_groups[$term->term_id]['books'][] = $book;
                } else {
                    $unsorted[] = $book;
                }
            }

            // Sort series alphabetically by name
            usort($series_groups, function ($a, $b) {
                return strcmp($a['term']->name, $b['term']->name);
            });

            // ── Output each series group ─────────────────────────────────────
            foreach ($series_groups as $group) :
                $term        = $group['term'];
                $description = term_description($term->term_id, 'book_series');
            ?>
                <section class="books-series-group">
                    <h2><?php echo esc_html(strtoupper($term->name)); ?></h2>
                    <?php if ($description) : ?>
                        <p class="books-series-subtitle"><?php echo wp_kses_post($description); ?></p>
                    <?php endif; ?>

                    <div class="books-grid">
                        <?php foreach ($group['books'] as $book) :
                            $subtitle = get_post_meta($book->ID, '_book_subtitle', true);
                        ?>
                            <a href="<?php echo esc_url(get_permalink($book->ID)); ?>" class="book-card">
                                <div class="book-card-cover-wrap">
                                    <?php if (has_post_thumbnail($book->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($book->ID, 'medium', ['alt' => esc_attr($book->post_title)]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/abra_01_publish.jpg'); ?>"
                                            alt="<?php echo esc_attr($book->post_title); ?>" />
                                    <?php endif; ?>
                                </div>
                                <span class="book-card-title"><?php echo esc_html($book->post_title); ?></span>
                                <?php if ($subtitle) : ?>
                                    <span class="book-card-subtitle"><?php echo esc_html($subtitle); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <?php if ($unsorted) : ?>
                <section class="books-series-group">
                    <h2><?php esc_html_e('STANDALONES', 'antonia-zanolli'); ?></h2>
                    <div class="books-grid">
                        <?php foreach ($unsorted as $book) :
                            $subtitle = get_post_meta($book->ID, '_book_subtitle', true);
                        ?>
                            <a href="<?php echo esc_url(get_permalink($book->ID)); ?>" class="book-card">
                                <div class="book-card-cover-wrap">
                                    <?php if (has_post_thumbnail($book->ID)) : ?>
                                        <?php echo get_the_post_thumbnail($book->ID, 'medium', ['alt' => esc_attr($book->post_title)]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/pictures/abra_01_publish.jpg'); ?>"
                                            alt="<?php echo esc_attr($book->post_title); ?>" />
                                    <?php endif; ?>
                                </div>
                                <span class="book-card-title"><?php echo esc_html($book->post_title); ?></span>
                                <?php if ($subtitle) : ?>
                                    <span class="book-card-subtitle"><?php echo esc_html($subtitle); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php endif; ?>

    </div><!-- .books-archive -->
</main>

<?php get_footer(); ?>