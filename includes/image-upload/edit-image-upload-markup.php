<?php
global $current_term;
global $custom_image_id;

function restricted_placeholder_img_src($size = 'woocommerce_thumbnail')
{
    global $current_term;
    global $custom_image_id;
    $src = WC()->plugin_url() . '/assets/images/placeholder.png';

    if ($current_term)
        $custom_image_id = absint(get_term_meta($current_term->term_id, 'custom_image_id', true));

    if ((!empty($custom_image_id)) || ($custom_image_id != "0")) {
        if (is_numeric($custom_image_id)) {
            $image = wp_get_attachment_image_src($custom_image_id, $size);

            if (!empty($image[0])) {
                $src = $image[0];
            }
        } else {
            $src = $custom_image_id;
        }
    }
    echo $src;
}


echo '<tr class="xise-root form-field term-thumbnail-wrap image-upload-settings custom-image-regions">';
global $custom_image_id;

?>
<th scope="row" valign="top"><label>
        <?php esc_html_e('Custom image:', 'woocommerce'); ?>
    </label></th>
<td>
    <div id="product_cat_custom_image" style="float: left; margin-right: 10px;"><img
            src="<?php echo esc_url(restricted_placeholder_img_src()); ?>" width="60px" height="60px" /></div>
    <div style="line-height: 60px;">
        <input type="hidden" id="product_cat_custom_image_id" name="product_cat_custom_image_id"
            value="<?php echo esc_attr($custom_image_id); ?>" />
        <button type="button" class="upload_custom_image_button btn btn-light-primary me-3">
            <?php esc_html_e('Upload/Add image', 'woocommerce'); ?>
        </button>
        <button type="button" class="remove_custom_image_button btn btn-light">
            <?php esc_html_e('Remove image', 'woocommerce'); ?>
        </button>
    </div>
    <script type="text/javascript">
        if ('0' === jQuery('#product_cat_custom_image_id').val()) {
            jQuery('.remove_custom_image_button').hide();
        }

        // Uploading files
        var custom_file_frame;

        jQuery(document).on('click', '.upload_custom_image_button', function (event) {

            event.preventDefault();

            if (custom_file_frame) {
                custom_file_frame.open();
                return;
            }

            // Create the media frame.
            custom_file_frame = wp.media.frames.downloadable_file = wp.media({
                title: '<?php esc_html_e('Choose an image', 'woocommerce'); ?>',
                button: {
                    text: '<?php esc_html_e('Use image', 'woocommerce'); ?>'
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            custom_file_frame.on('select', function () {
                var custom_attachment = custom_file_frame.state().get('selection').first().toJSON();
                var custom_attachment_thumbnail = custom_attachment.sizes.thumbnail || custom_attachment.sizes.full;
                // if (isset($custom_attachment) && !empty($custom_attachment)) {
                    var imageSizes = '<?php echo json_encode($custom_attachment['sizes']); ?>'; // Get available sizes
                    var srcset = '';
                    for (var size in imageSizes) {
                        if (imageSizes.hasOwnProperty(size)) {
                            srcset += imageSizes[size].url + ' ' + imageSizes[size].width + 'w, ';
                        }
                    }
                    srcset = srcset.slice(0, -2); // Remove the trailing comma and space

                    jQuery('#product_cat_custom_image_id').val(custom_attachment.id);
                    jQuery('#product_cat_custom_image').find('img').attr('src', custom_attachment_thumbnail.url);
                    jQuery('#product_cat_custom_image').find('img').attr('srcset', srcset); // Set the srcset attribute
                    jQuery('.remove_custom_image_button').show();
                // }

            });

            // Finally, open the modal.
            custom_file_frame.open();
        });



        jQuery(document).on('click', '.remove_custom_image_button', function () {

            jQuery('#product_cat_custom_image').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
            jQuery('#product_cat_custom_image_id').val('');
            jQuery('.remove_custom_image_button').hide();
            return false;
        });
    </script>
    <div class="clear"></div>
</td>
</tr>