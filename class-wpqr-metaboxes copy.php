<?php
if (!defined('ABSPATH')) {
    return;
}

class WPQR_Metaboxes {

    /**
     * Renders the metabox for answers.
     * We will display saved answers and have the form to add new or delete old.
     *
     * @param  WP_Post $post
     * @return void
     */
    public static function answers($post) {
        $post_id = $post->ID;
        // Get our options
        $answers = get_post_meta($post_id, '_wpqr_answers', true);
        $question_category = wp_get_post_terms($post_id, 'question-category', array('fields' => 'ids'));

        ?>
        <table class="wpqr-answers form-table">
            <thead>
                <tr>
                    <td><strong><?php esc_html_e('Options', 'wpqr'); ?></strong></td>
                    <td><strong><?php esc_html_e('Points', 'wpqr'); ?></strong></td>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < 4; $i++) {
                    ?>
                    <tr>
                        <td><input type="text" name="wpqr_answers[]" value="<?php echo isset($answers[$i]['text']) ? esc_attr($answers[$i]['text']) : ''; ?>"
                                class="widefat" /></td>
                        <td><input type="number" name="wpqr_points[]" value="<?php echo isset($answers[$i]['points']) ? esc_attr($answers[$i]['points']) : 0; ?>" />
                        </td>
                    </tr>

                <?php
                }
                ?>
                <thead>
                    <tr>
                        <td><strong><?php esc_html_e('Correct Answer', 'wpqr'); ?></strong></td>

                    </tr>
                </thead>
                <tr>
                    <td><input type="number" name="wpqr_correct" value="<?php echo isset($answers['correct']) ? intval($answers['correct']) : 0; ?>" /></td>
                </tr>
            </tbody>
        </table>
        
        <div>
            <label for="question-category">Quiz Category:</label>
            <select name="question-category" id="question-category">
                <option value="">Select Category</option>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'question-category',
                    'hide_empty' => false,
                ));

                foreach ($categories as $category) {
                    $selected = in_array($category->term_id, $question_category) ? 'selected' : '';
                    echo '<option value="' . $category->term_id . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                }
                ?>
            </select>
        </div>
    <?php
    }

    /**
     * Save Method.
     * @param  integer $post_id
     * @param  WP_Post $post
     * @return void
     */
    public static function save($post_id, $post) {
        if ('mcqs' !== get_post_type($post)) {
            return;
        }

        if (wp_is_post_autosave($post)) {
            return;
        }
        

        if (defined('WP_AJAX') && WP_AJAX) {
            return;
        }

        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['wpqr_answers']) && isset($_POST['wpqr_correct']) && isset($_POST['question-category'])) {
            $answers = array();

            // For each answer, get its order (index) and the text
            foreach ($_POST['wpqr_answers'] as $order => $answer) {
                $array = array('text' => sanitize_text_field($answer), 'points' => 0);
                if (isset($_POST['wpqr_points'][$order])) {
                    // If we have points inside with the same order (index), set it.
                    $array['points'] = floatval($_POST['wpqr_points'][$order]);
                }
                $answers[$order] = $array;
            }

            $correct_answer = intval($_POST['wpqr_correct']);
            update_post_meta($post_id, '_wpqr_answers', $answers);

            $question_category = intval($_POST['question-category']);
            wp_set_post_terms($post_id, $question_category, 'question-category');
        }
    }
}
