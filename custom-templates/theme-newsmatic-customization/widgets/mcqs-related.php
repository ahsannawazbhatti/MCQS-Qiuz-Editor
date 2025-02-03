<?php

/**
 * Adds Newsmatic_Mcqs_Related_Widget widget for 'mcqs' custom post type.
 *
 * 
 * 
 */
class Newsmatic_Mcqs_Related_Widget extends WP_Widget {
   
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'newsmatic_mcqs_related_widget',
            esc_html__('Newsmatic : Related Mcqs', 'newsmatic'),
            array('description' => __('A collection of mcqs from a specific quiz category displayed in a grid layout.', 'newsmatic'))
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from the database.
     */
    public function widget($args, $instance) {
        extract($args);

        $widget_title = isset($instance['widget_title']) ? $instance['widget_title'] : '';
        $quiz_category = isset($instance['quiz_category']) ? $instance['quiz_category'] : '';
        $mcqs_count = isset($instance['mcqs_count']) ? $instance['mcqs_count'] : 3;

        // Get the current post's categories
        $current_categories = get_the_terms(get_the_ID(), 'question-category');
        $category_slugs = array();
        if ($current_categories && is_array($current_categories)) {
            foreach ($current_categories as $current_category) {
                $category_slugs[] = $current_category->slug;
            }
        }

        echo wp_kses_post($before_widget);

        if (isset($args['widget_id'])) :
            ?>
            <style id="<?php echo esc_attr($args['widget_id']); ?>">
                <?php
                // Add your custom styles here
                ?>
            </style>
        <?php endif;

        if (!empty($widget_title)) {
            echo $before_title . esc_html($widget_title) . $after_title;
        }
        ?>
        <div class="mcqs-wrap mcqs-grid-wrap">
            <?php
            $mcqs_args = array(
                'post_type' => 'mcqs',
                'posts_per_page' => absint($mcqs_count),
            );

            // Add the category filter if one or more categories are found
            if (!empty($category_slugs)) {
                $mcqs_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'question-category',
                        'field' => 'slug',
                        'terms' => $category_slugs,
                    ),
                );
            }

            $mcqs_query = new WP_Query($mcqs_args);

            echo '<ul class="wp-block-latest-posts__list">';
            if ($mcqs_query->have_posts()) :
                while ($mcqs_query->have_posts()) : $mcqs_query->the_post();
                    // Display mcq content as needed
                    ?>
                    <div class="post-content-wrap card__content">
                        <div class="newsmatic-post-title card__content-title post-title">
                            <li> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </li>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            echo '</ul>';
            ?>
        </div>
        <?php
        echo wp_kses_post($after_widget);
    }

    /**
     * Widgets fields
     *
     */
    public function widget_fields() {
        $quiz_categories = get_terms(array(
            'taxonomy' => 'question-category',
            'hide_empty' => false,
        ));

        $categories_options = array();
        $categories_options[''] = esc_html__('Select quiz category', 'newsmatic');
        if (!empty($quiz_categories)) {
            foreach ($quiz_categories as $category) {
                $categories_options[$category->slug] = $category->name . ' (' . $category->count . ')';
            }
        }

        return array(
            array(
                'name' => 'widget_title',
                'type' => 'text',
                'title' => esc_html__('Widget Title', 'newsmatic'),
                'description' => esc_html__('Add the widget title here', 'newsmatic'),
                'default' => esc_html__('Related MCQs', 'newsmatic'),
            ),
            array(
                'name' => 'quiz_category',
                'type' => 'select',
                'title' => esc_html__('Quiz Category', 'newsmatic'),
                'description' => esc_html__('Choose the quiz category to display MCQs', 'newsmatic'),
                'options' => $categories_options,
            ),
            array(
                'name' => 'mcqs_count',
                'type' => 'number',
                'title' => esc_html__('Number of MCQs to show', 'newsmatic'),
                'default' => 3,
            ),
        );
    }


    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from the database.
     */

    public function form( $instance ) {
        $widget_fields = $this->widget_fields();
        $post_type = get_post_type();
        $selected_category = ''; // Default category, empty to allow user selection.

        // If the post type is 'mcqs', auto-select the category.
        if ($post_type == 'mcqs') {
            // Get the category assigned to the current 'mcqs' post.
            $categories = get_the_terms(get_the_ID(), 'question-category');
            if ($categories && is_array($categories)) {
                $selected_category = reset($categories)->slug;
            }
        }

        foreach ($widget_fields as $widget_field) {
            if (isset($instance[$widget_field['name']])) {
                $field_value = $instance[$widget_field['name']];
            } else if (isset($widget_field['default'])) {
                $field_value = $widget_field['default'];
            } else {
                $field_value = '';
            }

            if ($widget_field['name'] === 'posts_category') {
                // Set the selected category based on post type.
                $field_value = $selected_category;
            }

            newsmatic_widget_fields($this, $widget_field, $field_value);
        }
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from the database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $widget_fields = $this->widget_fields();
        if (!is_array($widget_fields)) {
            return $instance;
        }
        foreach ($widget_fields as $widget_field) {
            $instance[$widget_field['name']] = newsmatic_sanitize_widget_fields($widget_field, $new_instance);
        }
        return $instance;
    }

}

