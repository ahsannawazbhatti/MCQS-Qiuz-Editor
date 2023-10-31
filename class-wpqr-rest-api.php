<?php

if (!defined('ABSPATH')) {
    return;
}

/**
 * Class used to define how rest api works.
 */
class WPQR_REST_API {

    /**
     * Registering routes
     * @return void 
     */
    public static function register_routes() {
        register_rest_route('wpqr/v1', '/mcqs', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array(__CLASS__, 'get_questions'),
        ));

        register_rest_route('wpqr/v1', '/result', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array(__CLASS__, 'get_result'),
        ));
    }

    /**
     * Get the result
     * @param  WP_REST_Request $request 
     * @return WP_REST_Response         
     */
    public static function get_result($request) {
        $parameters = $request->get_json_params();

        if (!isset($parameters['answers'])) {
            return new WP_REST_Response(array('error' => 'no-answers', 'message' => 'There are no answers. Please answer the questions.'), 400);
        }

        // Default variables to hold the score and the possible top score
        $score = 0;
        $top = 0;
        $percentage = 0;

        foreach ($parameters['answers'] as $question_id => $answer) {
            // Get the quiz data for the question
            $quiz_data = WPQR_Metaboxes::get_quiz_data($question_id);

            if (!$quiz_data) {
                continue;
            }

            $answers = array(
                $quiz_data['answer_1'],
                $quiz_data['answer_2'],
                $quiz_data['answer_3'],
                $quiz_data['answer_4']
            );

            // If the answer is within the valid range (1-4), calculate the score
            if ($answer >= 1 && $answer <= 4) {
                $selected_answer = $answers[$answer - 1];
                $correct_answer = $quiz_data['correct_answer'];

                // If the selected answer matches the correct answer, add points
                if ($selected_answer === $correct_answer) {
                    $score += $quiz_data['point_' . $answer];
                }

                // Calculate the top score
                $top += max($quiz_data['point_1'], $quiz_data['point_2'], $quiz_data['point_3'], $quiz_data['point_4']);
            }
        }

        if ($top > 0 && $score > 0) {
            $percentage = floatval($score / $top) * 100;
        }

        return new WP_REST_Response(array(
            'top' => $top,
            'score' => $score,
            'percentage' => $percentage
        ), 200);
    }

    /**
     * Get the Questions with answers for the REST API Route
     * @return WP_REST_Response
     */
    public static function get_questions() {
        $questions = get_transient('wpqr_rest_questions');

        if (false === $questions) {
            $questions = array();

            $args = array(
                'post_type' => 'mcqs',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'no_found_rows' => true,
                'cache_results' => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $q_array = array();
                    $q_array['id'] = get_the_id();
                    $q_array['title'] = get_the_title();
                    $q_array['content'] = get_the_content();

                    // Get the quiz data for the question
                    $quiz_data = WPQR_Metaboxes::get_quiz_data($q_array['id']);

                    if ($quiz_data) {
                        $q_array['answers'] = array(
                            $quiz_data['answer_1'],
                            $quiz_data['answer_2'],
                            $quiz_data['answer_3'],
                            $quiz_data['answer_4']
                        );
                    }

                    $questions[] = $q_array;
                }
                wp_reset_postdata();
            }

            set_transient('wpqr_rest_questions', $questions, 24 * HOUR_IN_SECONDS);
        }

        return new WP_REST_Response($questions, 200);
    }
}
