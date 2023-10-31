<?php
class WPQR_Metaboxes {

    public static function get_quiz_data($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'quiz_data';
        $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
        return $quiz_data;
    }

    // ...

    public static function answers($post) {
        $post_id = $post->ID;
        $post_title =$post->post_title;
        // Get our options

        $quiz_categories = get_terms(array(
            'taxonomy' => 'quiz-category',
            'hide_empty' => false,
        ));

        // Get quiz data from the custom database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'quiz_data';
        $quiz_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
        $quiz_category_idz = wp_get_post_terms($post_id, 'quiz-category', array('fields' => 'ids'));
       
        if (!$quiz_data) {
            $quiz_data = array(
                'post_id'           => $post_id,
                'question'          => $post_title,
                'answer_1'          => '',
                'answer_2'          => '',
                'answer_3'          => '',
                'answer_4'          => '',
                'correct_answer'    => 'Type here',
                'point_1'           => 0,
                'point_2'           => 0,
                'point_3'           => 0,
                'point_4'           => 0,
                'quiz_category_id'  => 'Select Category',
            );
        }

        $quiz_category_idz =$quiz_data['quiz_category_id'];

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
                for ($i = 1; $i < 5; $i++) {
                    ?>
                    <tr>
                        <td><input type="text" name="wpqr_answers[]"
                                value="<?php echo $quiz_data['answer_' . $i]; ?>"
                                class="widefat" /></td>
                        <td><input type="number" name="wpqr_points[]"
                                value="<?php echo $quiz_data['point_' . $i]; ?>" />
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
                    <td><input type="text" name="wpqr_correct[]"
                            value="<?php echo $quiz_data['correct_answer']; ?>" />
                    </td>
                </tr>
                <thead>
                    <tr>
                        <td><strong><?php esc_html_e('Quiz Category', 'wpqr'); ?></strong></td>
                    </tr>
                </thead>
               
            </tbody>
        </table>

        <div>
            <label for="quiz_category">Quiz Category:</label>
            <select name="quiz_category" id="quiz_category">
                <option value="">Select Category</option>
                <?php
                            foreach ($quiz_categories as $category) {
                                $selected = (in_array($category->term_id, (array)$quiz_category_idz)) ? 'selected' : '';
                                echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                            }
                            ?> 
            </select>
            <label for="quiz_Id">Category ID is:</label>
            <select name="quiz_Id" id="quiz_Id">
                <option value=""><?php echo isset($quiz_data['quiz_category_id']) ? $quiz_data['quiz_category_id'] : 'No Category is Select'; ?></option>
            </select>
        </div>
        <?php
    }


    /**
     * Save Method.
     * @param integer $post_id
     * @param WP_Post $post
     * @return void
     */
    public static function save($post_id, $post) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'quiz_data';

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

        if (isset($_POST['wpqr_answers']) && isset($_POST['wpqr_correct']) && isset($_POST['quiz_category'])) {
            $question = sanitize_text_field($post->post_title);
            $quiz_category_id = intval($_POST['quiz_category']);
            
            wp_set_post_terms($post_id, $quiz_category_id, 'quiz-category');

            $correct_answer = sanitize_text_field($_POST['wpqr_correct'][0]);
            $answers = array_map('sanitize_text_field', $_POST['wpqr_answers']);
            $points = array_map('intval', $_POST['wpqr_points']);
            $data_update = array(
                'post_id'           => $post_id,
                'question'          => $question,
                'answer_1'          => $answers[0],
                'answer_2'          => $answers[1],
                'answer_3'          => $answers[2],
                'answer_4'          => $answers[3],
                'correct_answer'    => $correct_answer,
                'point_1'           => $points[0],
                'point_2'           => $points[1],
                'point_3'           => $points[2],
                'point_4'           => $points[3],
                'quiz_category_id'  => $quiz_category_id,
            );
            

            $checkIfExists = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $table_name WHERE post_id = %d", $post_id));

            if ($checkIfExists == NULL) {
                $wpdb->insert(
                    $table_name,
                    $data_update
                );
            } else {
                $data_where = array('post_id' => $post_id);

                $wpdb->update(
                    $table_name,
                    $data_update,
                    $data_where
                );
            }
            

        }
    }
}
