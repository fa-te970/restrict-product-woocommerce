<?php
$image = WC()->plugin_url() . '/assets/images/placeholder.png';

echo '<div class="xise-root form-field term-thumbnail-wrap image-upload-settings custom-image-regions" >';

?>
<label>
    <?php esc_html_e('Custom image:', 'woocommerce'); ?>
</label>
<div id="product_cat_custom_image" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url($image); ?>"
        width="60px" height="60px" /></div>
<div style="line-height: 60px;">
    <input type="hidden" id="product_cat_custom_image_id" name="product_cat_custom_image_id" />
    <button type="button" class="upload_custom_image_button btn btn-light-primary me-3">
        <?php esc_html_e('Upload/Add image', 'woocommerce'); ?>
    </button>
    <button type="button" class="remove_custom_image_button btn btn-light">
        <?php esc_html_e('Remove image', 'woocommerce'); ?>
    </button>
</div>
<script type="text/javascript">
    if (!jQuery('#product_cat_custom_image_id').val()) {
        jQuery('.remove_custom_image_button').hide();
    }

    // Uploading files
    var add_custom_file_frame;

    jQuery(document).on('click', '.upload_custom_image_button', function (event) {

        event.preventDefault();

        if (add_custom_file_frame) {
            add_custom_file_frame.open();
            return;
        }

        // Create the media frame.
        add_custom_file_frame = wp.media.frames.downloadable_file = wp.media({
            title: '<?php esc_html_e('Choose an image', 'woocommerce'); ?>',
            button: {
                text: '<?php esc_html_e('Use image', 'woocommerce'); ?>'
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        add_custom_file_frame.on('select', function () {
            var add_custom_attachment = add_custom_file_frame.state().get('selection').first().toJSON();
            var add_custom_attachment_thumbnail = add_custom_attachment.sizes.thumbnail || add_custom_attachment.sizes.full;
            // if (isset($add_custom_attachment) && !empty($add_custom_attachment)) {
                jQuery('#product_cat_custom_image_id').val(add_custom_attachment.id);
                jQuery('#product_cat_custom_image').find('img').attr('src', add_custom_attachment_thumbnail.url);
                jQuery('.remove_custom_image_button').show();
            // }
        });

        // Finally, open the modal.
        add_custom_file_frame.open();
    });

    jQuery(document).on('click', '.remove_custom_image_button', function () {
        jQuery('#product_cat_custom_image').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
        jQuery('#product_cat_custom_image_id').val('');
        jQuery('.remove_custom_image_button').hide();
        return false;
    });

    jQuery(document).ajaxComplete(function (event, request, options) {
        if (request && 4 === request.readyState && 200 === request.status &&
            options.data && 0 <= options.data.indexOf('action=add-tag')) {

            var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
            if (!res || res.errors) {
                return;
            }
            // Clear Thumbnail fields on submit
            jQuery('#product_cat_custom_image').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
            jQuery('#product_cat_custom_image_id').val('');
            jQuery('.remove_custom_image_button').hide();
            // Clear Display type field on submit
            jQuery('#display_type').val('');
            return;
        }
    });
</script>
<div class="clear"></div>
</div>