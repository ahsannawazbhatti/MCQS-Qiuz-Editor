<?php
// ... rest of the code 

class WPQR {

    // ... rest of the code

    /**
     * Load everything
     * @return void 
     */
    public function load() {
        add_action( 'init', array( $this, 'load_cpts' ) );
        
        if ( is_admin() ) { 
            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
        }
    } 

    /**
     * Register all metaboxes
     * @return void 
     */
    public function register_metaboxes() {
        add_meta_box( 'question-answers', __( 'Answers', 'wpqr' ), array( 'WPQR_Metaboxes', 'answers' ), 'question' );
    }
  
    // ... rest of the code
}

// ... rest of the code