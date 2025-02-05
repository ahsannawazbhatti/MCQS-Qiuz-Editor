<?php

/**
 * Adds Newsmatic_Posts_Grid_Widget widget for 'mcqs' custom post type.
 *
 * @package Newsmatic
 * @since 1.0.0
 */
class Newsmatic_Posts_Grid_Mcqs_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'newsmatic_posts_grid_mcqs_widget',
            esc_html__('Newsmatic : Popular Mcqs  ', 'newsmatic'),
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
        $question_category = isset($instance['question-category']) ? $instance['question-category'] : '';
        $mcqs_count = isset($instance['mcqs_count']) ? $instance['mcqs_count'] : 3;

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
                'tax_query' => array(
                    array(
                        'taxonomy' => 'question-category',
                        'field' => 'slug',
                        'terms' => $question_category,
                    ),
                ),
                'posts_per_page' => absint($mcqs_count),
            );

            $mcqs_query = new WP_Query($mcqs_args);
          echo' <ul class="wp-block-latest-posts__list" >';
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
            echo' </ul>';
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
                'default' => esc_html__('Popular MCQs', 'newsmatic'),
            ),
            array(
                'name' => 'question-category',
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

    public function form($instance) {
        $widget_fields = $this->widget_fields();
        foreach ($widget_fields as $widget_field) {
            if (isset($instance[$widget_field['name']])) {
                $field_value = $instance[$widget_field['name']];
            } else if (isset($widget_field['default'])) {
                $field_value = $widget_field['default'];
            } else {
                $field_value = '';
            }
            newsmatic_widget_fields($this, $widget_field, $field_value);
        }
    }
   


    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from the database.
     */

    

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
