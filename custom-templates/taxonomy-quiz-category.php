<?php
/**
 * The template for displaying taxonomy archives for the custom taxonomy 'question-category'
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Newsmatic
 */

use Newsmatic\CustomizerDefault as ND;
require plugin_dir_path(__FILE__) . '/theme-newsmatic-customization/template-tags.php';

get_header();

if (class_exists('Nekit_Render_Templates_Html')) :
    $Nekit_render_templates_html = new Nekit_Render_Templates_Html();
    if ($Nekit_render_templates_html->is_template_available('archive')) {
        $archive_rendered = true;
        echo $Nekit_render_templates_html->current_builder_template();
    } else {
        $archive_rendered = false;
    }
else :
    $archive_rendered = false;
endif;

if (!$archive_rendered) :
    ?>
    <div id="theme-content">
        <?php
        /**
         * hook - newsmatic_before_main_content
         */
        do_action('newsmatic_before_main_content');
        $term_id = get_queried_object_id();
        $image_url = get_term_meta($term_id, 'custom-category-image-url', true);
        $tax_content = get_term_meta($term_id, 'custom-category-description', true);
        
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
                         */
                        do_action('newsmatic_before_inner_content');
                        ?>
                        <header class="page-header">
                        <h1 class="entry-title" ><?php single_term_title(); ?></h1>
                            <?php if ($image_url) :?>
                                <div class="post-thumbnail">
				                    <img width="1000" height="750" src="<?php echo $image_url ?>" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" decoding="async" fetchpriority="high" sizes="(max-width: 1000px) 100vw, 1000px">
                                </div>
                                    <?php endif?>
                                    <?php if ($tax_content){ echo $tax_content; } ?>
                        </header>

                        <?php
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'quiz_data';


                        if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <?php
                        $post_id = get_the_ID(); // Get the post ID
                        // Get quiz data from the custom database table
                        $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div  class="post-element">
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
                        </div>
                     </article>
                    <?php endwhile;
                         /**
                         * hook - newsmatic_pagination_link_hook
                         * 
                         * @package Newsmatic
                         * @since 1.0.0
                         */
							do_action( 'newsmatic_pagination_link_hook' );
                            wp_reset_postdata();
                        else : ?>
                            <p><?php _e('No posts found', 'QuizLand'); ?></p>
                        <?php endif; ?>

                    </div>
                    <div class="secondary-sidebar">
                        <?php
                        get_sidebar();
                        ?>
                    </div>
                </div>
            </div>
        </main><!-- #main -->
    </div><!-- #theme-content -->
<?php
endif;

get_footer();
?>
