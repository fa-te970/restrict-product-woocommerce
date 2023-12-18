<?php
/*
Plugin Name: Ultimate B2B Solutions
Description: Unlock Limitless Business Potential with Ultimate B2B Solutions: Your All-in-One Powerhouse Plugin! 
Version: 0.0.1
Author: Xise Inc. 
Author URI: https://xise.tech
*/

// function enqueue_jquery_ui() {
//     wp_enqueue_script('jquery');
//     wp_enqueue_script('jquery-ui-core');
//     wp_enqueue_script('jquery-ui-widget');
//     wp_enqueue_script('jquery-ui-mouse');
//     wp_enqueue_script('jquery-ui-draggable');
//     wp_enqueue_script('jquery-ui-sortable');
// }
// add_action('admin_enqueue_scripts', 'enqueue_jquery_ui', 5);

function enqueue_product_restriction_assets_head()
{
    // $dir_path = plugin_dir_url(__FILE__); // Navigate to reach the plugin root

    // Enqueue jQuery
    wp_enqueue_script('jquery', false, array(), false, false);

    // Enqueue jQuery UI
    wp_enqueue_script('jquery-ui-core', false, array(), false, false);
    wp_enqueue_script('jquery-ui-widget', false, array(), false, false);
    wp_enqueue_script('jquery-ui-mouse', false, array(), false, false);
    wp_enqueue_script('jquery-ui-draggable', false, array(), false, false);
    wp_enqueue_script('jquery-ui-sortable', false, array(), false, false);
   
    // Add your custom script tag here
    // echo '<script src="' . $dir_path . 'assets/metronic/js/custom/widgets.js"></script>';
    // echo '<script src="' . $dir_path . 'assets/metronic/js/widgets.bundle.js"></script>';
}
add_action('wp_head', 'enqueue_product_restriction_assets_head', 10);

function enqueue_product_restriction_assets()
{
    $dir_path = plugin_dir_url(__FILE__); // Navigate to reach the plugin root

    // Enqueue jQuery
    wp_enqueue_script('jquery', false, array(), false, false);

    // Enqueue jQuery UI
    wp_enqueue_script('jquery-ui-core', false, array(), false, false);
    // wp_enqueue_script('jquery-ui-widget', false, array(), false, false);
    // wp_enqueue_script('jquery-ui-widgets', false, array(), false, false);

    // Enqueue CSS files
    // wp_enqueue_style('datatables-css', $dir_path . 'assets/metronic/plugins/custom/datatables/datatables.bundle.css');
    wp_enqueue_style('global-css', $dir_path . 'assets/metronic/plugins/global/plugins.bundle.css');
    wp_enqueue_style('style-css', $dir_path . 'assets/metronic/css/style.bundle_.css');
    // wp_enqueue_style('select2-style-css', $dir_path . 'assets/metronic/css/select2.css');

    // Enqueue JavaScript files
    wp_enqueue_script('global-plugins-js', $dir_path . 'assets/metronic/plugins/global/plugins.bundle.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-draggable', 'jquery-ui-sortable'), null, true);
    //bugs
    wp_enqueue_script('scripts-js', $dir_path . 'assets/metronic/js/scripts.bundle.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-draggable', 'jquery-ui-sortable'), null, true);
    // wp_enqueue_script('datatables-js', $dir_path . 'assets/metronic/plugins/custom/datatables/datatables.bundle.js', array('jquery'), null, true);
    // wp_enqueue_script('widget-bundle-js', $dir_path . 'assets/metronic/js/widgets.bundle.js', array('jquery'), null, true);
    // wp_enqueue_script('widget-js', $dir_path . 'assets/metronic/js/custom/widgets.js', array('jquery'), null, true);

    // Add localized variable
    // wp_localize_script('scripts-js', 'hostUrl', $dir_path . 'assets/');

    // Enqueue custom JavaScript files
    // wp_enqueue_script('listing-js', $dir_path . 'assets/metronic/js/custom/apps/ecommerce/customers/listing/listing.js', array('jquery'), null, true);
    // wp_enqueue_script('add-js', $dir_path . 'assets/metronic/js/custom/apps/ecommerce/customers/listing/add.js', array('jquery'), null, true);
    // wp_enqueue_script('export-js', $dir_path . 'assets/metronic/js/custom/apps/ecommerce/customers/listing/export.js', array('jquery'), null, true);
    // wp_enqueue_script('chat-js', $dir_path . 'assets/metronic/js/custom/apps/chat/chat.js', array('jquery'), null, true);
    // wp_enqueue_script('upgrade-plan-js', $dir_path . 'assets/metronic/js/custom/utilities/modals/upgrade-plan.js', array('jquery'), null, true);
    // wp_enqueue_script('create-app-js', $dir_path . 'assets/metronic/js/custom/utilities/modals/create-app.js', array('jquery'), null, true);
    // wp_enqueue_script('user-search-js', $dir_path . 'assets/metronic/js/custom/utilities/modals/users-search.js', array('jquery'), null, true);
}
// enqueue_product_restriction_assets();
add_action('admin_enqueue_scripts', 'enqueue_product_restriction_assets', 20);

// Include the file for image restriction
$root_path = plugin_dir_path(__FILE__);

require_once $root_path . 'includes/restrict-product-image/featured-image-restriction.php';

// Include the file for region restriction
require_once $root_path . 'includes/restricted-regions/restricted-regions2.php';

?>

