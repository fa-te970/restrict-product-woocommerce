<?php
$current_term = null;
$parent_dir_path = plugin_dir_path(dirname(__FILE__));
require_once $parent_dir_path . 'components/checkbox.php';
require_once $parent_dir_path . 'components/radio-row.php';

function is_product_in_restricted_categories($product)
{
    $product_id = $product->get_id();
    $terms = get_the_terms($product_id, 'product_cat');

    if ($terms && !is_wp_error($terms)) {
        // Loop through each product category
        foreach ($terms as $term) {
            $wh_hide_from_guests = get_term_meta($term->term_id, 'wh_restrict_image', true);

            if (!is_wp_error($wh_hide_from_guests) && ($wh_hide_from_guests == '1')) {
                return $term->term_id;
                // break;
            }
        }
    }
    return null;
}

//Product Category_Create page
function wh_taxonomy_add_new_meta_field()
{
    $parent_dir_path = plugin_dir_path(dirname(__FILE__));
    ?>
    <div class="rounded border p-2 xise-root">
        <div class="form-field">
            <label class="fs-5" for="wh_restrict_image">
                <?php _e('Restrict product image', 'wh'); ?>
            </label>
            <?php
            render_checkbox('wh_restrict_image', 'wh_restrict_image', "1", "Restrict product image for guests", null);
            ?>
        </div>

        <div class="form-field image-restriction-options">
            <label for="wh_image_restriction">
                <?php _e('image display option:', 'wh'); ?>
            </label>
            <?php
            $radio_group_name = 'wh_image_restriction';
            $radio_options = array(
                'items' => array(
                    array(
                        'label' => 'Use category thumbnail as product image',
                        'checked' => true,
                        'value' => 'thumbnail',
                        'id' => 'wh_thumb_radio',
                    ),
                    array(
                        'label' => 'Use custom image as product image',
                        'checked' => false,
                        'value' => 'custom',
                        'id' => 'wh_custom_image_radio'
                    )
                ),
            );

            render_radio_buttons($radio_group_name, $radio_options);
            ?>
        </div>
        <?php
        include_once($parent_dir_path . 'image-upload/add-image-upload-markup.php');
        echo '</div>';
}

//Product Category_Edit page
function wh_taxonomy_edit_meta_field($term)
{
    $parent_dir_path = plugin_dir_path(dirname(__FILE__));
    $term_id = $term->term_id;
    $wh_restrict_image = get_term_meta($term_id, 'wh_restrict_image', true);
    $wh_image_restriction = get_term_meta($term_id, 'wh_image_restriction', true);
    $current_term = $term
        ?>
        <tr class="rounded border p-2 xise-root">
            <td colspan="2" class="ps-4">
                <table class="fs-5">
                    <tr class="form-field">
                        <th scope="row" valign="top">
                            <?php _e('Restrict product image', 'wh'); ?>
                        </th>
                        <td>
                            <?php
                            render_checkbox('wh_restrict_image', 'wh_restrict_image', "1", "Restrict product image for guests", ($wh_restrict_image == '1') ? "checked" : "");
                            ?>
                        </td>
                    </tr>
                    <tr class="form-field image-restriction-options">
                        <th scope="row" valign="top">
                            <?php _e('image display option:', 'wh'); ?>
                        </th>
                        <td>
                            <?php
                            $radio_group_name = 'wh_image_restriction';
                            $radio_options = array(
                                // 'label' => 'Default radios',
                                'items' => array(
                                    array(
                                        'label' => 'Use category thumbnail as product image',
                                        'checked' => ($wh_image_restriction == 'thumbnail'),
                                        'value' => 'thumbnail',
                                        'id' => 'wh_thumb_radio',
                                    ),
                                    array(
                                        'label' => 'Use custom image as product image',
                                        'checked' => ($wh_image_restriction == 'custom'),
                                        'value' => 'custom',
                                        'id' => 'wh_custom_image_radio'
                                    )
                                ),
                            );
                            render_radio_buttons($radio_group_name, $radio_options);
                            ?>
                        </td>
                    </tr>
                    <?php
                    global $current_term;
                    $current_term = $term;
                    include_once($parent_dir_path . 'image-upload/edit-image-upload-markup.php');
                    ?>
                </table>
            </td>
        </tr>
        <?php
}

add_action('product_cat_add_form_fields', 'wh_taxonomy_add_new_meta_field', 10);
add_action('product_cat_edit_form_fields', 'wh_taxonomy_edit_meta_field', 10, 1);

// Save extra taxonomy fields
function wh_save_taxonomy_custom_meta($term_id)
{
    $wh_restrict_image = isset($_POST['wh_restrict_image']) ? '1' : '0';
    $wh_image_restriction = filter_input(INPUT_POST, 'wh_image_restriction');

    update_term_meta($term_id, 'wh_restrict_image', $wh_restrict_image);
    update_term_meta($term_id, 'wh_image_restriction', $wh_image_restriction);

    if ($_POST['wh_image_restriction'] == "custom") {
        update_term_meta($term_id, 'custom_image_id', absint($_POST['product_cat_custom_image_id']));
    } else {
        update_term_meta($term_id, 'custom_image_id', '0');
    }
}

add_action('edited_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);
add_action('create_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);

// Add a custom class to the product
function custom_archive_product_loop($classes)
{
    if (!is_user_logged_in()) {
        global $product;
        $str_class_hide = 'product-image-hide';

        if ($product) {
            $restrictedCat = is_product_in_restricted_categories($product);
            if ($restrictedCat !== null) {
                // Add a custom attribute to the product classes
                $classes[] = 'product-category-ids-' . $restrictedCat;
                $classes[] = $str_class_hide;
            }
        }
    }
    return $classes;
}

add_filter('post_class', 'custom_archive_product_loop');

function custom_cart_item_thumbnail($thumbnail, $cart_item)
{
    if (!is_user_logged_in()) {
        $product_id = $cart_item['product_id'];
        $terms = get_the_terms($product_id, 'product_cat');

        // Check if there are categories assigned to the product
        if ($terms && !is_wp_error($terms)) {
            // Loop through each product category
            foreach ($terms as $term) {
                $wh_hide_from_guests = get_term_meta($term->term_id, 'wh_restrict_image', true);
                $wh_image_restriction = get_term_meta($term->term_id, 'wh_image_restriction', true);

                // Check if the 'wh_restrict_image' field is set to '1' for any category
                if (!is_wp_error($wh_image_restriction) && ($wh_hide_from_guests == '1')) {
                    if ($wh_image_restriction === 'thumbnail') {
                        $image_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    } elseif ($wh_image_restriction === 'custom') {
                        $image_id = absint(get_term_meta($term->term_id, 'custom_image_id', true));
                    }
                    $image_url = wp_get_attachment_url($image_id);

                    // If the category thumbnail URL is available, update the thumbnail image
                    // Create a new instance of WC_Product
                    $product_instance = wc_get_product($product_id);
                    $size = 'woocommerce_thumbnail';
                    $image_size = apply_filters('single_product_archive_thumbnail_size', $size);
                    $thumbnail = $product_instance->get_image($image_size, array('src' => $image_url ? $image_url : wc_placeholder_img_src(), 'srcset' => $image_url ? $image_url : wc_placeholder_img_src()));
                    break;
                }
            }
        }
    }
    return $thumbnail;
}
add_filter('woocommerce_cart_item_thumbnail', 'custom_cart_item_thumbnail', 10, 2);

function custom_loop_product_thumbnail()
{
    if (!is_user_logged_in()) {
        global $product;
        if ($product && is_a($product, 'WC_Product')) {
            $product_id = $product->get_id();

            $terms = get_the_terms($product_id, 'product_cat');

            // Check if there are categories assigned to the product
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $wh_image_restriction = get_term_meta($term->term_id, 'wh_image_restriction', true);
                    $wh_hide_from_guests = get_term_meta($term->term_id, 'wh_restrict_image', true);

                    if (!is_wp_error($wh_image_restriction) && ($wh_hide_from_guests == '1')) {
                        if ($wh_image_restriction === 'thumbnail') {
                            $image_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                        } elseif ($wh_image_restriction === 'custom') {
                            $image_id = absint(get_term_meta($term->term_id, 'custom_image_id', true));
                        }

                        $image_url = wp_get_attachment_url($image_id);

                        if ($image_url) {
                            // remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 5);

                            // Create a new instance of WC_Product
                            $product_instance = wc_get_product($product->get_id());
                            $size = 'woocommerce_thumbnail';
                            $image_size = apply_filters('single_product_archive_thumbnail_size', $size);

                            ?>
                                <a class="product-image-link2" href="<?php echo esc_url($product->get_permalink()); ?>">
                                    <?php echo $product_instance->get_image($image_size, array('src' => $image_url, 'srcset' => wp_get_attachment_image_srcset($image_id, $size)));
                                    ?>
                                </a>
                                <?php
                                break;
                        }
                        // break; // No need to check other categories once one is found
                    }
                }
            }
        }
    }
}
// Remove the default function and add your custom one
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action('woocommerce_before_shop_loop_item_title', 'custom_loop_product_thumbnail', 20);

function get_image_settings_for_categories($category_ids)
{
    $image_settings = array();

    if (is_array($category_ids) && !empty($category_ids)) {
        foreach ($category_ids as $category_id) {
            $wh_image_restriction = get_term_meta($category_id, 'wh_image_restriction', true);
            $image_id = ($wh_image_restriction === 'custom') ? get_term_meta($category_id, 'custom_image_id', true) : get_term_meta($category_id, 'thumbnail_id', true);

            // Get the existing srcset attribute from the image
            $srcset = wp_get_attachment_image_srcset($image_id);

            // var_dump("sett");
            // var_dump($category_id);
            // var_dump($wh_image_restriction);
            // var_dump(wp_get_attachment_url($image_id));
            // var_dump($srcset);

            $image_settings[$category_id] = array(
                'wh_image_restriction' => $wh_image_restriction,
                'wh_image' => $image_id ? wp_get_attachment_url($image_id) : wc_placeholder_img_src(),
                'wh_image_srcset' => $srcset
            );
        }
    }
    return $image_settings;
}

function get_restricted_category_ids()
{
    $restricted_categories = [];
    $args = array(
        'taxonomy' => 'product_cat',
        'meta_query' => array(
            array(
                'key' => 'wh_restrict_image',
                'value' => '1',
                'compare' => '=',
            ),
        ),
    );

    $categories = get_terms($args);

    foreach ($categories as $category) {
        $restricted_categories[] = $category->term_id;
    }

    return $restricted_categories;
}

function enqueue_plugin_scripts()
{
    $parent_dir_path = plugin_dir_url(dirname(dirname(__FILE__))); // Navigate up twice to reach the plugin root
    wp_enqueue_style('restricted-product-styles', $parent_dir_path . 'assets/css/restrictedProduct2.css', array(), '1.0.0', 'all');
    wp_enqueue_script('featured-image-restrictions', $parent_dir_path . 'assets/js/featured-image-restrictions3.js', array('jquery'), '1.0.0', true);
    // Pass data to JavaScript for identifying restricted category IDs and image settings
    $restricted_category_ids = get_restricted_category_ids();
    $image_settings = get_image_settings_for_categories($restricted_category_ids);

    wp_localize_script('featured-image-restrictions', 'customImageSettings', $image_settings);
}
// add_action('wp_enqueue_scripts', 'enqueue_plugin_scripts');
add_action('wp_enqueue_scripts', 'enqueue_plugin_scripts', 99999);
add_action('admin_enqueue_scripts', 'enqueue_plugin_scripts');