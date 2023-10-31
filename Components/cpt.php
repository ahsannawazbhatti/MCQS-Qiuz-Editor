<?php

// ... Rest of the code

class WPQR {

    // ... Rest of the code

    /**
     * Load everything
     * @return void 
     */
    public function load() {
        add_action( 'init', array( $this, 'load_cpts' ) );
    } 

    /**
     * Load all CPTs
     * @return void 
     */
    public function load_cpts() {
        $labels = array(
            'name'                  => _x( 'Questions', 'Post Type General Name', 'wpqr' ),
            'singular_name'         => _x( 'Question', 'Post Type Singular Name', 'wpqr' ),
            'menu_name'             => __( 'Questions', 'wpqr' ),
            'name_admin_bar'        => __( 'Question', 'wpqr' ),
            'archives'              => __( 'Item Archives', 'wpqr' ),
            'attributes'            => __( 'Item Attributes', 'wpqr' ),
            'parent_item_colon'     => __( 'Parent Item:', 'wpqr' ),
            'all_items'             => __( 'All Items', 'wpqr' ),
            'add_new_item'          => __( 'Add New Item', 'wpqr' ),
            'add_new'               => __( 'Add New', 'wpqr' ),
            'new_item'              => __( 'New Item', 'wpqr' ),
            'edit_item'             => __( 'Edit Item', 'wpqr' ),
            'update_item'           => __( 'Update Item', 'wpqr' ),
            'view_item'             => __( 'View Item', 'wpqr' ),
            'view_items'            => __( 'View Items', 'wpqr' ),
            'search_items'          => __( 'Search Item', 'wpqr' ),
            'not_found'             => __( 'Not found', 'wpqr' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wpqr' ),
            'featured_image'        => __( 'Featured Image', 'wpqr' ),
            'set_featured_image'    => __( 'Set featured image', 'wpqr' ),
            'remove_featured_image' => __( 'Remove featured image', 'wpqr' ),
            'use_featured_image'    => __( 'Use as featured image', 'wpqr' ),
            'insert_into_item'      => __( 'Insert into item', 'wpqr' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'wpqr' ),
            'items_list'            => __( 'Items list', 'wpqr' ),
            'items_list_navigation' => __( 'Items list navigation', 'wpqr' ),
            'filter_items_list'     => __( 'Filter items list', 'wpqr' ),
        );
        $args = array(
            'label'                 => __( 'Question', 'wpqr' ),
            'description'           => __( 'Questions for Quiz', 'wpqr' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor' ),
            'hierarchical'          => true,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_icon'             => 'dashicons-testimonial',
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'show_in_rest'          => false,// Don't support Gutenberg
            'can_export'            => true, 
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );
        register_post_type( 'question', $args );
    }
}

// ... Rest of the code