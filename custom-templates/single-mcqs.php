<?php
/* 
Template Name: Single-Mcqs
Currently customized for the Newsmatic theme.
*/
require plugin_dir_path(__FILE__) . 'theme-newsmatic-customization/template-tags.php';
get_header();
?>

<div id="theme-content">
    <?php
    /**
     * hook - newsmatic_before_main_content
     * 
     */
    do_action( 'newsmatic_before_main_content' );
    ?>

    <main id="primary" class="site-main">
        <div class="newsmatic-container">
            <div class="row">
                <div class="secondary-left-sidebar">
                    <?php
                    get_sidebar('left');
                    ?>
                </div>
                <div class="primary-content">
                    <?php
                    /**
                     * hook - newsmatic_before_inner_content
                     * 
                     */
                    do_action( 'newsmatic_before_inner_content' );
                    ?>
                    <div class="post-inner-wrapper">
                        <?php
                        while (have_posts()) :
                            the_post();
                            $post_id = get_the_ID();
                            $content = get_the_content();
                            $custom_taxonomyz ='question-category';
                            $question_category = wp_get_post_terms($post_id, $custom_taxonomyz, array('number' => absint($number)));
                            // Get quiz data from the custom database table
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'quiz_data';
                            $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
                            ?>
         <div class="post-inner">

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                             <?php

                            echo '<ul class="post-categories">';
                            foreach ($question_category as $n_term) :
                            echo '<li class="cat-item cat-1 ' . esc_attr('cat-' . $n_term->term_id) . '"><a href="' . esc_url(get_term_link($n_term, $custom_taxonomyz)) . '" rel="category">' . esc_html($n_term->name) . '</a></li>';
                           endforeach;
                            echo '</ul>';
                            ?>
                        <div class="entry-content">
                                   <header class="entry-header">
                                    <h1 class="entry-title"><?php echo esc_html($quiz_data['question']); ?></h1>
                                    </header>
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
                                    
                                    ?>
                         </div>
                         
                 <div <?php newsmatic_schema_article_body_attributes(); ?> class="entry-content">
                        <?php
                           if (!empty($content)) :
                            echo '<details class="wp-block-details">';
                            echo '<summary> Check the details.</summary>';
                            the_content(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'newsmatic' ),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    wp_kses_post( get_the_title() )
                                )
                            );
                            echo '</details>';
                          endif;

                            wp_link_pages(
                                array(
                                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'newsmatic' ),
                                    'after'  => '</div>',
                                )
                            );
                        ?>
                             <footer class="entry-footer">
                                    <?php newsmatic_tags_list_mcqs(); ?>
                                    <?php newsmatic_categories_list_mcqs(); ?>

                                    <?php newsmatic_entry_footer(); ?>
                                    <?php newsmatic_tags_list(); ?>
                        </footer>
                    </div><!-- .entry-content -->

                    <?php
                         the_post_navigation(
                                        array(
                                            
                                            'prev_text' => '<span class="nav-subtitle"><i class="fas fa-angle-double-left"></i>' . esc_html__( 'Previous:', 'newsmatic' ) . '</span> <span class="nav-title">%title</span>',
                                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'newsmatic' ) . '<i class="fas fa-angle-double-right"></i></span> <span class="nav-title">%title</span>',
                                            'taxonomy'					 => __( 'question-category' ),
                                            'screen_reader_text' => __( 'Continue Reading' ),
                                        )
                                    );
                             ?>
                    
                 </article>
             </div>


                        <?php
                        

                             // If comments are open or we have at least one comment, load up the comment template.
                                 if ( comments_open() || get_comments_number() ) :
                                         comments_template();
                                 endif;
                        endwhile;

                        
                        ?>
                    </div>
                </div>
                <div class="secondary-sidebar">
                    <?php get_sidebar(); ?>
                </div>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #theme-content -->

<?php
/**
 * hook - newsmatic_single_post_append_hook
 * 
 */
do_action( 'newsmatic_single_post_append_hook' );

get_footer();
?>
