<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}
class WPQR_Metaboxes {

    // ... 

    /**
     * Save Method. 
     * @param  integer $post_id 
     * @param  WP_Post $post    
     * @return void
     */
    public static function save( $post_id, $post ) {
        if( 'question' !== get_post_type( $post ) ) {
            return;
        }

        if ( wp_is_post_autosave( $post ) ) {
            return;
        }

        if ( defined( 'WP_AJAX' ) && WP_AJAX ) {
            return;
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        if ( isset( $_POST['wpqr_answers'] ) && isset( $_POST['wpqr_points'] ) ) {
            $answers = array();
            
            // For each answer, get it's order (index) and the text
            foreach ( $_POST['wpqr_answers'] as $order => $answer) {
                $array = array( 'text' => $answer, 'points' => 0 );
                if ( isset( $_POST['wpqr_points'][ $order ] ) ) {
                    // If we have points inside with the same order (index), set it.
                    $array['points'] = floatval( $_POST['wpqr_points'][ $order ] );
                }
                $answers[ $order ] = $array;
            }
            
            update_post_meta( $post_id, '_wpqr_answers', $answers );
            
        } 
    }
}