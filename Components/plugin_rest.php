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
        add_action( 'rest_api_init', array( 'WPQR_REST_API', 'register_routes' ) );
        
        // ...
    }
  
    // ...
}

// ...
