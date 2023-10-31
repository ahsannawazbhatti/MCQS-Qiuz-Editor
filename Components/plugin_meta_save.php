<?php

// ...

class WPQR {

    // ...

    /**
     * Load everything
     * @return void 
     */
    public function load() {
        add_action( 'init', array( $this, 'load_cpts' ) );
        
        if ( is_admin() ) { 
            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
            add_action( 'save_post', array( $this, 'save_metaboxes' ), 20, 2 );
        }
    } 

    /**
     * Save all metaboxes if needed.
     * @param  integer $post_id 
     * @param  WP_Post $post    
     * @return void
     */
    public function save_metaboxes( $post_id, $post ) {
        WPQR_Metaboxes::save( $post_id, $post );
    }
  
    // ...
}

// ...