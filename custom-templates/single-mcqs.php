<?php
/* 
Template Name: Single-Mcqs
*/

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while (have_posts()) :
            the_post();
            $post_id = get_the_ID();
            $content = get_the_content();

            // Get quiz data from the custom database table
            global $wpdb;
            $table_name = $wpdb->prefix . 'quiz_data';
            $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);

            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php echo esc_html($quiz_data['question']); ?></h1>
                </header>
                <div class="entry-content">
                    <?php
                    // Display the custom meta data (answers)
                    echo '<h4>Options:</h4>';
                    echo '<ul>';
                    for ($i = 1; $i <= 4; $i++) {
                        $answer_key = 'answer_' . $i;
                        if (!empty($quiz_data[$answer_key])) {
                            echo '<li>' . esc_html($quiz_data[$answer_key]) . '</li>';
                        }
                    }
                    echo '</ul>';

                    echo '<details class="wp-block-details">';
                    echo '<summary>View Answer</summary>';
                    echo '<p>' . esc_html($quiz_data['correct_answer']) . '</p>';
                    echo '</details>';
                    echo   $content;
                    ?>
                    
                </div>
            </article>
        <?php
        endwhile;
        ?>
    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer();
