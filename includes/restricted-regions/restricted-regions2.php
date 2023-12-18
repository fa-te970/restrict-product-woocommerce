<?php
require_once $parent_dir_path . 'components/checkbox.php';
require_once $parent_dir_path . 'components/input.php';

// Initialize the global array to store restricted categories
$restricted_categories = array();
$edit_dropdown = "";

function redirect_restricted($category_id)
{
    $restricted_redirect = get_term_meta($category_id, 'xise_restrict_redirect', true);
    $restricted_redirect_url = get_term_meta($category_id, 'xise_restrict_redirect_url', true);
    
    if (isset($restricted_redirect) && $restricted_redirect == "yes") {
        wp_redirect(home_url($restricted_redirect_url));
    } else {
        wp_redirect(home_url('/404.php'));
    }
}

function getRestrictedCategories($product_id)
{
    $terms = get_the_terms($product_id, 'product_cat');
    global $restricted_categories;

    if (!is_wp_error($terms) && is_array($terms)) {
        foreach ($terms as $term) {
            $category_id = $term->term_id;
            if (is_restricted_cat($category_id)) {
                $restricted_categories[] = $category_id;
                break;
            }
        }
    }
    return $restricted_categories;
}

function getAllRestrictedCategories()
{
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true, // Include even empty categories
    );

    $all_categories = get_terms($args);
    $restrictedCategories = array();

    foreach ($all_categories as $cat) {
        // Check if the category has the "restrict_country" field set to true
        if (is_restricted_cat($cat->term_id)) {
            $restrictedCategories[] = $cat->term_id;
        }
    }
    return $restrictedCategories;
}
function get_user_state_by_ip($ip)
{
    // Make API request
    $api_url = "http://ip-api.com/json/{$ip}?fields=regionName";
    $response = wp_safe_remote_get($api_url);

    if (is_wp_error($response)) {
        return ''; // Error handling
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Extract and return the state
    return isset($data['regionName']) ? $data['regionName'] : '';
}

function is_restricted_cat($category_id)
{
    if (is_user_logged_in() && WC()->customer) {
        $user_country = WC()->customer->get_billing_country();
        $user_state = WC()->customer->get_billing_state();
    } else if (is_user_logged_in() && WC()->customer) {
        $user_country = WC()->customer->get_shipping_country();
        $user_state = WC()->customer->get_shipping_state();
    } else {
        // If the user is not logged in or has no billing address, use geolocation
        $geo = new WC_Geolocation();
        $user_geo = $geo->geolocate_ip($geo->get_ip_address());
        $user_country = $user_geo['country'];
        $user_state = $user_geo['state'];

        if ($user_state == "")
            $user_state = get_user_state_by_ip($geo->get_ip_address());
    }

    $country_states = WC()->countries->get_states();

    $restricted = get_term_meta($category_id, 'restrict_country', true);

    if ($restricted == 'yes') {
        // $selected_states = get_term_meta($category_id, 'selected_state', true);
        $regions = get_term_meta($category_id, 'selected_state', true);
        $regions = json_decode($regions, true);

        if ($regions) {
            foreach ($regions as $region) {
                if (isset($region['state']) && $user_state !== "") {
                    $state = $region["state"];
                    $country = $region['countryCode'];

                    if (isset($country_states[$country])) {
                        $countryStates = $country_states[$country];
                    }

                    if (isset($country_states[$country])) {
                        // Check if the region is found in the array for the given country
                        if ($country === $user_country) {
                            foreach ($countryStates as $stateCode => $stateName) {
                                if (
                                    strtolower($stateCode) === strtolower($state) &&
                                    (strtolower($stateCode) == strtolower($user_state)
                                        || str_contains(strtolower($stateName), strtolower($user_state)))
                                ) {
                                    $matchFound = true;
                                    break;
                                }
                            }
                        }
                    }

                    // Check for "all_states" value in the region
                    if ($region['state'] === 'all_states' && $region['countryCode'] === $user_country) {
                        $matchFound = true;
                        break;
                    }
                } elseif (isset($region['countryCode']) && $user_state == "") {
                    if ($region['countryCode'] === $user_country) {
                        $matchFound = true;
                        break;
                    }
                }
            }
        }

        if (($restricted == 'yes') && (isset($matchFound) && $matchFound)) {
            return true;
        }
    }
    return false;
}
// Add custom checkbox field to category settings page
function taxonomy_add_new_meta_field($term_id)
{
    $form_context = '_add';
    ?>
    <div class="rounded border p-2 xise-root">
        <div class="form-field">
            <label class="fs-5" for="restrict_country<?php echo $form_context; ?>"><?php _e('Restrict Regions', 'text-domain'); ?></label>
            <?php
            render_checkbox('restrict_country' . $form_context, 'restrict_country' . $form_context, "yes", "Restrict products from specific regions", null);
            ?>
        </div>
        <?php
        show_regions($term_id, '_add', true);
        show_redirect_option($term_id, '_add', true);

        ?>
    </div>
    <?php
}


//Product Cat Edit page
function taxonomy_edit_new_meta_field($term)
{
    $term_id = $term->term_id;
    $restrict_country = get_term_meta($term_id, 'restrict_country', true);
    ?>
    <tr class="rounded border p-2 xise-root">
        <td colspan="2" class="ps-2">
            <table>
                <tr class="form-field restrict-region-checkbox">
                    <th scope="row" valign="top">
                        <label for="restrict_country">
                            <?php _e('Restrict regions', 'wh'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                        render_checkbox('restrict_country', 'restrict_country', "yes", "Restrict products from specific regions", ($restrict_country == 'yes') ? "checked" : "");
                        ?>
                    </td>
                </tr>
                <?php
                // Show the country and state fields when the checkbox is checked
                show_regions($term_id, '', !($restrict_country === 'yes'));
                show_redirect_option($term_id, '', !($restrict_country === 'yes'));
}

add_action('product_cat_add_form_fields', 'taxonomy_add_new_meta_field', 10, 1);
add_action('product_cat_edit_form_fields', 'taxonomy_edit_new_meta_field', 10, 1);

function show_regions($term_id, $form_context, $is_hidden)
{
    global $edit_dropdown;
    $selected_state = get_term_meta($term_id, 'selected_state', true); // Get the saved state value
    $selected_state = $selected_state ? $selected_state : '[{"state":"","countryCode":""}]'; // Get the saved state value

    if ($form_context === "_add") {
        $edit_dropdown = false;
        echo '<div class="form-field" id="regions" style="display:' . ($is_hidden ? 'none' : 'table-row') . ';">';
    } else {
        $edit_dropdown = true;
        echo '<tr class="form-field" id="regions" style="display:' . ($is_hidden ? 'none' : 'table-row') . ';">';
        // echo '<th></th>';
        echo '<td>';
    }
    echo '<input hidden type="text" name="resultInput_states" id="resultInput_states" value="' . esc_attr($selected_state) . '" />';
    include plugin_dir_path(__FILE__) . '/../multiselect-dropdown/dropdown.php';
    if ($form_context === "_add") {
        echo '</div>';
    } else {
        echo '</td>';
        echo "</tr>";
    }
}

function show_redirect_option($term_id, $form_context, $is_hidden)
{
    global $edit_dropdown;

    $xise_restrict_redirect = get_term_meta($term_id, 'xise_restrict_redirect', true);
    $xise_restrict_redirect_url = get_term_meta($term_id, 'xise_restrict_redirect_url', true);

    if ($form_context === "_add") {
        $edit_dropdown = false;
        echo '<div class="form-field" id="regions-redirect" style="display:' . ($is_hidden ? 'none' : 'table-row') . ';">';
    } else {
        $edit_dropdown = true;
        echo '<tr class="form-field" id="regions-redirect" style="display:' . ($is_hidden ? 'none' : 'table-row') . ';">';
        echo '<td>';
    }
    render_checkbox('xise_restrict_redirect' . $form_context, 'xise_restrict_redirect' . $form_context, "yes", "Redirect restricted categories", ($xise_restrict_redirect == 'yes') ? "checked" : "");
    render_input("Redirect URL", "xise_restrict_redirect_url", "Enter a relative address, such as /about", $xise_restrict_redirect_url, "Enter redirect url for restricted regions");
    if ($form_context === "_add") {
        echo '</div>';
    } else {
        echo '</td>';
        echo "</tr>";

        echo "</table>";
        echo "</td>";
        echo "</tr>";
    }
}

// Save custom fields and detect changes in the restrict country checkbox
function save_custom_category_fields($term_id)
{
    $new_value = "";
    $result_value = "";
    $redirected = "";
    $redirect_url_value = "";

    if (isset($_POST["resultInput_states"])) {
        $result_value = isset($_POST["resultInput_states"]) ? sanitize_text_field(trim($_POST["resultInput_states"])) : '';
    }
    if (isset($_POST["xise_restrict_redirect_url"])) {
        $redirect_url_value = isset($_POST["xise_restrict_redirect_url"]) ? sanitize_text_field(trim($_POST["xise_restrict_redirect_url"])) : '';
    }

    if (isset($_POST["restrict_country"])) {
        $new_value = $_POST['restrict_country'] == 'yes' ? 'yes' : 'no';
        update_term_meta($term_id, 'selected_state', ($_POST['restrict_country'] == 'yes') ? $result_value : '[{"state":"","countryCode":""}]');
    }

    if (isset($_POST["xise_restrict_redirect"])) {
        $redirected = $_POST['xise_restrict_redirect'] == 'yes' ? 'yes' : 'no';
        update_term_meta($term_id, 'xise_restrict_redirect_url', ($_POST['xise_restrict_redirect'] == 'yes') ? $redirect_url_value : '');
    }

    update_term_meta($term_id, 'restrict_country', $new_value);
    update_term_meta($term_id, 'xise_restrict_redirect', $redirected);
}

// Save custom fields and detect changes in the restrict country checkbox
function save_custom_category_fields_add($term_id)
{
    if (isset($_POST["restrict_country_add"])) {
        $new_value = $_POST["restrict_country_add"] == 'yes' ? 'yes' : 'no';
    }
    if (isset($_POST["xise_restrict_redirect_add"])) {
        $restricted = $_POST["xise_restrict_redirect_add"] == 'yes' ? 'yes' : 'no';
    }
    $resultValue = '';
    if (isset($_POST["resultInput_states"])) {
        $resultValue = isset($_POST["resultInput_states"]) ? sanitize_text_field(trim($_POST["resultInput_states"])) : '';
    }
    if (isset($_POST["xise_restrict_redirect_url"])) {
        $redirect_url_value = isset($_POST["xise_restrict_redirect_url"]) ? sanitize_text_field(trim($_POST["xise_restrict_redirect_url"])) : '';
    }

    update_term_meta($term_id, 'restrict_country', $new_value);
    update_term_meta($term_id, 'selected_state', ($_POST['restrict_country_add'] == 'yes') ? $resultValue : '[{"state":"","countryCode":""}]');
    update_term_meta($term_id, 'xise_restrict_redirect', $restricted);
    update_term_meta($term_id, 'xise_restrict_redirect_url', ($_POST['xise_restrict_redirect_add'] == 'yes') ? $redirect_url_value : '');

}

add_action('edited_product_cat', 'save_custom_category_fields', 10, 1);
add_action('create_product_cat', 'save_custom_category_fields_add', 10, 1);

function get_current_category_id()
{
    $current_category = get_queried_object();
    if ($current_category instanceof WP_Term) {
        return $current_category->term_id;
    }
    return 0;
}

function restrict_products_by_region_cat($q)
{
    if ((is_shop() || is_product_category()) && $q->is_main_query()) {
        // !is_admin() &&
        $restricted_categories = array(); 

        $cat_id = get_current_category_id();

        if (is_restricted_cat($cat_id)) {
            $restricted_categories[] = $cat_id;
        }

        if (!empty($restricted_categories)) {
            $q->set(
                'tax_query',
                array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $restricted_categories,
                        'include_children' => true,
                        'operator' => 'NOT IN',
                    )
                )
            );
        }

        if (!$q->have_posts() && (count($restricted_categories) > 0)) {
            redirect_restricted($restricted_categories[0]);
        }
    }
}
// page with specific category
add_action('woocommerce_product_query', 'restrict_products_by_region_cat', 5, 1);
function restrict_search_results_by_categories($query)
{
    if ($query->is_search() && $query->is_main_query()) {
        $query->set(
            'tax_query',
            array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => getAllRestrictedCategories(),
                    'include_children' => true,
                    'operator' => 'NOT IN',
                ),
            )
        );
    }
}
add_action('pre_get_posts', 'restrict_search_results_by_categories', 10, 1);

function restrict_single_product_summary()
{
    if (is_singular('product')) {
        $product_id = get_queried_object_id();
        $terms = get_the_terms($product_id, 'product_cat');

        foreach ($terms as $term) {
            $category_id = $term->term_id;

            if (is_restricted_cat($category_id)) {
                redirect_restricted($category_id);
                exit;
            }
        }
    }
}
// in category page
add_action('template_redirect', 'restrict_single_product_summary');

function remove_restricted_products_from_cart_($cart)
{
    global $restricted_categories;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $restricted_categories[] = getRestrictedCategories($product_id);

        // Check if the product belongs to any of the restricted categories
        if (has_term($restricted_categories, 'product_cat', $product_id)) {
            $cart->remove_cart_item($cart_item_key);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'remove_restricted_products_from_cart_', 10, 1);

function load_css_js()
{
    $parent_dir_path = plugin_dir_url(dirname(dirname(__FILE__))); // Navigate up twice to reach the plugin root
    // Pass country states to the JavaScript file
    $country_states = WC()->countries->get_states();
    $countries = WC()->countries->get_countries();
    $country_states = array_merge($country_states, array_fill_keys(array_diff(array_keys($countries), array_keys($country_states)), array()));

    wp_register_script('custom-country-script', $parent_dir_path . 'assets/js/cat-custom-regions1.js', array('jquery'), '1.1', true);
    wp_localize_script(
        'custom-country-script',
        'custom_country_script_params',
        array(
            'countries' => $countries,
            'path' => $parent_dir_path
        )
    );
    wp_enqueue_script('custom-country-script');

    // wp_enqueue_style('treeview-styles', $parent_dir_path . 'assets/treeview/treeview.css', array(), '1.0.0', 'all');
    wp_enqueue_script('treeview', $parent_dir_path . 'assets/treeview/treeview.js', array('jquery', 'custom-country-script'), '1.0.0', true);
    wp_enqueue_style('jstree-styles', $parent_dir_path . 'assets/treeview/style.min.css', array(), '1.0.0', 'all');
    wp_enqueue_script('jstree', $parent_dir_path . 'assets/treeview/jstree.min.js', array('jquery'), '1.0.0', true);

    wp_localize_script(
        'treeview',
        'country_script_params',
        array(
            'country_states' => $country_states,
            'countries' => $countries,
        )
    );
}
add_action('admin_enqueue_scripts', 'load_css_js', 20, 1);

?>