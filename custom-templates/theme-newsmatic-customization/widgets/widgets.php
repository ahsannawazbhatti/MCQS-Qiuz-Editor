<?php

function newsmatic_custom_widgets_init() {
    // Register custom widgets
    register_widget('Newsmatic_Posts_Grid_Mcqs_Widget');
    register_widget('Newsmatic_Mcqs_Related_Widget');
}

require plugin_dir_path(__FILE__) . '/posts-grid-mcqs.php';
require plugin_dir_path(__FILE__) . '/mcqs-related.php';
