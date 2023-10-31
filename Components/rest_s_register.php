<?php

if( ! defined( 'ABSPATH' ) ) {
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
        // ...

        register_rest_route( 'wpqr/v1', '/result', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'get_result' ),
        ) );
    }
  
    // ...

    /**
     * Get the result
     * @param  WP_REST_Request $request 
     * @return array         
     */
    public static function get_result( $request ) {
         $parameters = $request->get_body_params();
         if ( ! isset( $parameters['answers'] ) ) {
            return new WP_Error( 'no-answers', __( 'There are no answers. Please answer the questions.', 'wpqr' ) );
         }
        
         // Default variables to hold the score and the possible top score
         $score = 0;
         $top   = 0;
         $percentage = 0;
         foreach ( $parameters['answers'] as $question_id => $answer ) {
             $answers = get_post_meta( $question_id, '_wpqr_answers', true );
             if( $answers ) {
                // If there are answers, let's define the default top and score for the current question
                $question_top   = 0;
                $question_score = 0;
                foreach ( $answers as $order => $array ) {
                    // If the current answer's points are bigger than the current question's top
                    // replace it
                    if( $array['points'] > $question_top ) {
                        $question_top = $array['points'];
                    }
                    // If the current answer is the one selected from the user register it as the question score
                    if( absint( $order ) === absint( $answer ) ) {
                        $question_score = $array['points'];
                    }
                }
                // Add both the question defaults to the 'global' defaults.
                $top += $question_top;
                $score += $question_score;
             }
         }
         if( $top > 0 && $score > 0 ) {
            $percentage = floatval( $score / $top ) * 100;
         }
         return array( 'top' => $top, 'score' => $score, 'percentage' => $percentage );
    }
}