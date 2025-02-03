<?php

/**
 * Plugin Name: MCQS-Qiuz-Editor
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
        include 'custom-templates/theme-newsmatic-customization/widgets/widgets.php';
    }
    /**
     * Load everything
     * @return void 
     */
    public function load() {
        add_action( 'init', array( $this, 'load_cpts' ) );
        add_action( 'init', array( $this, 'create_table' ) );
        add_action('init', array( $this, 'create_quiz_category_taxonomy' )); 
        add_action('update_option', array( $this,'turn_on_comments'));

        add_action( 'rest_api_init', array( 'WPQR_REST_API', 'register_routes' ) );
        if ( is_admin() ) { 
            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
            add_action( 'save_post', array( $this, 'save_metaboxes' ), 20, 2 );
        }
        
        //add_action( 'admin_menu', array( $this, 'fetch_demo' ) );
        add_filter( 'taxonomy_template', array( $this, 'quiz_category_template' ) ); 
        
        add_filter( 'single_template', array( $this, 'custom_cpt_template' ) ); 

        // Schedule this function to run as a one-time task
        // You can manually trigger this function once and then remove it
        // Schedule it in a way that it doesn't run automatically in the future

        //add_action('init', array( $this, 'delete_duplicate_posts'));

        add_action( 'init', array( $this, 'wpqr_flush_rewrite_rules' ) );
        
        // Hook into the 'newsmatic_single_post_append_hook' action for the 'mcqs' custom post type
        add_action('newsmatic_single_post_append_hook', array( $this,'add_related_posts_to_mcqs') );

        add_action("question-category_add_form_fields", array( $this,'custom_taxonomy_image_description_field') ); 
        add_action("question-category_edit_form_fields", array( $this,'custom_taxonomy_image_description_field') );
        add_action('created_question-category',  array( $this,'save_custom_taxonomy_fields'));
        add_action('edited_question-category',  array( $this,'save_custom_taxonomy_fields'));
        
        add_action('admin_menu', array( $this,'add_plugin_menu') );

        add_action('widgets_init', 'newsmatic_custom_widgets_init');


    } 

    function add_plugin_menu() {
        add_menu_page(
            'Fetch Demo Data', // Page Title
            'Fetch Demo Data', // Menu Title
            'manage_options', // Capability required to access the menu
            'fetch-demo-data', // Menu Slug
            array( $this,'display_fetch_demo_page') // Callback function to display the page content
        );
    }

    function display_fetch_demo_page() {
        ?>
        <div class="wrap">
            <h2>Fetch Demo Data</h2>
            <p>Click the button below to fetch demo data:</p>
            <form method="post" action="">
                <?php
                if (isset($_POST['fetch_demo_button'])) {
                    // If the button is clicked, call the fetch_demo function
                    $this->fetch_demo();
                    echo '<div class="updated"><p>Process is completed successfully!</p></div>';
                }
                ?>
                <input type="submit" name="fetch_demo_button" class="button button-primary" value="Fetch Data">
            </form>
        </div>
        <?php
    }

// Add a custom field for the taxonomy
function custom_taxonomy_image_description_field() {
    $taxonomy = 'question-category'; 
    $term_id = get_queried_object_id();
    $image_url = get_term_meta($term_id, 'custom-category-image-url', true);
    $tax_content = get_term_meta($term_id, 'custom-category-description', true);
    ?>
    <div class="form-field">
        <label for="custom-category-image-url">Image URL</label>
        <input type="text" name="custom-category-image-url" id="custom-category-image-url" value="<?php echo isset($image_url) ? $image_url : ''; ?>">
    </div>
    <div class="form-field">
        <label for="custom-category-description">Content</label>
        <textarea name="custom-category-description" id="custom-category-description" rows="5"><?php echo isset($tax_content) ? $tax_content : ''; ?></textarea>
    </div>
    <?php
}



function save_custom_taxonomy_fields($term_id) {
    $image_url =  $_POST['custom-category-image-url'];
    $description = filter_input(INPUT_POST, 'custom-category-description');

    update_term_meta($term_id, 'custom-category-image-url', $image_url);
    update_term_meta($term_id, 'custom-category-description', $description);
}




function addTitleFieldToCat(){
    $cat_title = get_term_meta($_POST['tag_ID'], '_pagetitle', true);
    ?> 
    <tr class="form-field">
        <th scope="row" valign="top"><label for="cat_page_title"><?php _e('Category Page Title'); ?></label></th>
        <td>
        <input type="text" name="cat_title" id="cat_title" value="<?php echo $cat_title ?>"><br />
            <span class="description"><?php _e('Title for the Category '); ?></span>
        </td>
    </tr>
    <?php

}


    // Function to include related posts for 'mcqs' custom post type
    function add_related_posts_to_mcqs() {
        // Check if we are on a single 'mcqs' post
        if (is_single() && get_post_type() == 'mcqs') {
            // Add the related posts section
            newsmatic_single_related_posts();
        }
    }



    function quiz_category_template($template) {
        if (is_tax('question-category')) {
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
        'question-category',
        'mcqs',  // Associate with the "mcqs" custom post type
        array(
            'label' => 'Questions Category',
            'rewrite' => array('slug' => 'important-questions'),
            'hierarchical' => true,
        )
    );
}


function fetch_demo() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_data';
    
    // Corrected query syntax: use `$table_name` and backticks
    $sql_data = $wpdb->get_results("SELECT * FROM `$table_name` WHERE post_id = 0", ARRAY_A);

    if ($wpdb->last_error) {
        echo '<div class="error"><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
        return;
    }

    if (!empty($sql_data)) {
        foreach ($sql_data as $row) {
            $post_data = array(
                'post_title'   => $row['question'],
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'mcqs',
                'page_template' => plugin_dir_path(__FILE__) . 'custom-template/single-mcqs.php',
            );
            
            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                echo '<div class="error"><p>Error: ' . $post_id->get_error_message() . '</p></div>';
            } else {
                $data_update = array('post_id' => $post_id);
                $data_where = array('id' => $row['id']);
                
                $wpdb->update(
                    $table_name,
                    $data_update,
                    $data_where
                );

                if ($wpdb->last_error) {
                    echo '<div class="error"><p>Error updating database: ' . esc_html($wpdb->last_error) . '</p></div>';
                }
            }
        }
    } else {
        echo '<div class="error"><p>No results found or failed to fetch results from the database!</p></div>';
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
            'archives'              => __( 'Question Archives', 'wpqr' ),
            'attributes'            => __( 'Question Attributes', 'wpqr' ),
            'parent_item_colon'     => __( 'Parent Question:', 'wpqr' ),
            'all_items'             => __( 'All Questions', 'wpqr' ),
            'add_new_item'          => __( 'Add New Question', 'wpqr' ),
            'add_new'               => __( 'Add New', 'wpqr' ),
            'new_item'              => __( 'New Question', 'wpqr' ),
            'edit_item'             => __( 'Edit Question', 'wpqr' ),
            'update_item'           => __( 'Update Question', 'wpqr' ),
            'view_item'             => __( 'View Question', 'wpqr' ),
            'view_items'            => __( 'View Questions', 'wpqr' ),
            'search_items'          => __( 'Search Question', 'wpqr' ),
            'not_found'             => __( 'Not found', 'wpqr' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wpqr' ),
            'featured_image'        => __( 'Featured Image', 'wpqr' ),
            'set_featured_image'    => __( 'Set featured image', 'wpqr' ),
            'remove_featured_image' => __( 'Remove featured image', 'wpqr' ),
            'use_featured_image'    => __( 'Use as featured image', 'wpqr' ),
            'insert_into_item'      => __( 'Insert into item', 'wpqr' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'wpqr' ),
            'items_list'            => __( 'Questions list', 'wpqr' ),
            'items_list_navigation' => __( 'Questions list navigation', 'wpqr' ),
            'filter_items_list'     => __( 'Filter items list', 'wpqr' ),
        );
        $args = array(
            'label' => __( 'Question', 'wpqr' ),
            'description' => __( 'Questions for Quiz', 'wpqr' ),
            'labels' => $labels,
            'supports' => array( 'comments', 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions' ),
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

    // Sets the comments to allowed by default
function turn_on_comments() { 
    update_option('default_comment_status', 'open');
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
