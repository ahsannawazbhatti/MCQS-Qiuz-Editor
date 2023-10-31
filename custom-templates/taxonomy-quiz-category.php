<?php get_header(); ?>

<section id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title"><?php single_term_title(); ?></h1>
        </header>

        <?php 
         global $wpdb;
         $table_name = $wpdb->prefix . 'quiz_data';

         // Define pagination variables
         $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
         $posts_per_page = 10; // Change this to the number of posts you want per page

         $args = array(
            'post_type' => 'mcqs', // Replace with your custom post type
            'posts_per_page' => $posts_per_page,
            'paged' => $paged
         );

         $custom_query = new WP_Query($args);

         if ($custom_query->have_posts()) : while ($custom_query->have_posts()) : $custom_query->the_post();
            $post_id = get_the_ID(); // Get the post ID
            // Get quiz data from the custom database table
            $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                </header>

                <div class="entry-content">
                    <h4>Options:</h4>
                    <ul>
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            $answer_key = 'answer_' . $i;
                            if (!empty($quiz_data[$answer_key])) {
                                echo '<li>' . esc_html($quiz_data[$answer_key]) . '</li>';
                            }
                        }
                        ?>
                    </ul>

                    <details class="wp-block-details">
                        <summary>View Answer</summary>
                        <?php 
                        echo '<p>' . esc_html($quiz_data['correct_answer']) . '</p>';
                        ?>
                    </details>
                    <?php 
                    the_excerpt();
                    ?>
                </div>
            </article>
        <?php endwhile; ?>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $custom_query->max_num_pages,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
            ));
            ?>
        </div>

        <?php 
        wp_reset_postdata();
        else: ?>
            <p><?php _e('No posts found', 'your-text-domain'); ?></p>
        <?php endif; ?>

    </main>
</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
