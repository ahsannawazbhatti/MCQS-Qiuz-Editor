<?php

/**
 * Plugin Name: MCQS Qiuz Editor
 * Description: Quiz Plugin made to conduct quizes.
 * Plugin URI: https://ibenic.com
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class WPQR {

    public function includes() {
        include 'class-wpqr-metaboxes.php';
        include 'class-wpqr-rest-api.php';
    }
    /**
     * Load everything
     * @return void 
     */
    public function load() {
        add_action( 'init', array( $this, 'load_cpts' ) );
        add_action( 'init', array( $this, 'create_table' ) );
        add_action('init', array( $this, 'create_quiz_category_taxonomy' ));
        

        add_action( 'rest_api_init', array( 'WPQR_REST_API', 'register_routes' ) );
        if ( is_admin() ) { 
            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
            add_action( 'save_post', array( $this, 'save_metaboxes' ), 20, 2 );
        }
        
        add_action( 'init', array( $this, 'fetch_demo' ) );
        add_filter( 'taxonomy_template', array( $this, 'quiz_category_template' ) ); 
        
        add_filter( 'single_template', array( $this, 'custom_cpt_template' ) ); 

        // Schedule this function to run as a one-time task
        // You can manually trigger this function once and then remove it
        // Schedule it in a way that it doesn't run automatically in the future
        add_action('init', array( $this, 'delete_duplicate_posts'));
        add_action( 'init', array( $this, 'wpqr_flush_rewrite_rules' ) );
    } 

    function quiz_category_template($template) {
        if (is_tax('quiz-category')) {
            $template =  plugin_dir_path(__FILE__) . 'custom-templates/taxonomy-quiz-category.php';
        }
        
        return $template;
    }
       
    function custom_cpt_template($template) {
    global $post;
    // Check if the post is of your Custom Post Type
    if (is_single() && $post->post_type == 'mcqs' ) {
        $template = plugin_dir_path(__FILE__) . 'custom-templates/single-mcqs.php';
    
    }

    return $template;
}


function create_quiz_category_taxonomy() {
    register_taxonomy(
        'quiz-category',
        'mcqs',  // Associate with the "mcqs" custom post type
        array(
            'label' => 'Questions Categories',
            'rewrite' => array('slug' => 'category'),
            'hierarchical' => true,
        )
    );
}


function fetch_demo() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_data';
    
    // Fetch only rows that have not been processed
    $sql_data = $wpdb->get_results("SELECT * FROM $table_name WHERE post_id is Null", ARRAY_A);

    if (!empty($sql_data)) {
        foreach ($sql_data as $row) {
            // Define the post data
            $post_data = array(
                'post_title'   => $row['question'],
                'post_content' => '',
                'post_status'  => 'publish', // or 'draft', 'pending', etc.
                'post_type'    => 'mcqs', // or your custom post type
                'page_template' => plugin_dir_path(__FILE__) . 'custom-template/single-mcqs.php',
            );

            // Insert or update the post
            $post_id = wp_insert_post($post_data);

            // Check for errors
            if (is_wp_error($post_id)) {
                // Handle the error
                echo 'Error: ' . $post_id->get_error_message();
            } else {
                
            wp_set_post_terms($post_id, $row['quiz_category_id'], 'quiz-category');
                // The post was created/updated successfully
                $data_update = array('post_id' => $post_id);
                $data_where = array('id' => $row['id']);
                $wpdb->update(
                    $table_name,
                    $data_update,
                    $data_where
                );
            }
        }
    }
}
    
    function wpqr_flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Register all metaboxes
     * @return void 
     */
    public function register_metaboxes() {
        add_meta_box( 'question-answers', __( 'Options', 'wpqr' ), array( 'WPQR_Metaboxes', 'answers' ), 'mcqs' );
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
            'label' => __( 'Question', 'wpqr' ),
            'description' => __( 'Questions for Quiz', 'wpqr' ),
            'labels' => $labels,
            'supports' => array( 'title', 'editor' ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-testimonial',
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => false,  // Dont allow Guternberg
            'can_export' => true,
            'has_archive' => false,  // Allow archiving
            'exclude_from_search' => false,  // Include in search
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );
        register_post_type( 'mcqs', $args );
    }


    function delete_duplicate_posts() {
        global $wpdb;
    
        // Replace 'mcqs' with your custom post type
        $post_type = 'mcqs';
    
        $query = "
            SELECT post_title, post_content, MAX(ID) as max_id
            FROM $wpdb->posts
            WHERE post_type = '$post_type'
            GROUP BY post_title, post_content
            HAVING COUNT(*) > 1
        ";
    
        $results = $wpdb->get_results($query);
    
        if ($results) {
            foreach ($results as $result) {
                $post_title = $result->post_title;
                $post_content = $result->post_content;
                $max_id = $result->max_id;
    
                // Delete all duplicate posts except the one with the highest ID
                $delete_query = $wpdb->prepare("
                    DELETE FROM $wpdb->posts
                    WHERE post_type = '$post_type'
                    AND post_title = %s
                    AND post_content = %s
                    AND ID < %d
                ", $post_title, $post_content, $max_id);
    
                $wpdb->query($delete_query);
    
                // Optionally, delete post meta and taxonomy relationships if needed
                // Uncomment and modify the following lines accordingly:
                // $wpdb->delete($wpdb->postmeta, array('post_id' => $max_id));
                // $wpdb->delete($wpdb->term_relationships, array('object_id' => $max_id));
                // $wpdb->delete($wpdb->term_taxonomy, array('term_taxonomy_id' => $term_taxonomy_id));
            }
        }
    }

    public function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'quiz_data';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                post_id  mediumint(9) NULL,
                question text NOT NULL,
                answer_1 text NOT NULL,
                answer_2 text NOT NULL,
                answer_3 text NOT NULL,
                answer_4 text NOT NULL,
                correct_answer text NOT NULL,
                point_1  mediumint(3)  NULL DEFAULT '0',
                point_2  mediumint(3)  NULL  DEFAULT '0',
                point_3  mediumint(3)  NULL  DEFAULT '0',
                point_4  mediumint(3)  NULL  DEFAULT '0',
                quiz_category_id mediumint(9) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

    }
}

add_action( 'plugins_loaded', 'wpqr_load' );

/**
 * Loading our plugin
 * @return void 
 */
function wpqr_load() {
    $plugin = new WPQR();
    $plugin->includes();
    $plugin->load();
}
// ... Rest of the code