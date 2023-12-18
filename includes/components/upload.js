jQuery(document).ready(function ($) {
  // Only show the "remove image" button when needed
  if ("0" === jQuery("#product_cat_custom_image_id").val()) {
    jQuery(".remove_custom_image_button").hide();
  }

  // Uploading files
  var custom_file_frame;

  jQuery(document).on("click", ".upload_custom_image_button", function (event) {
    event.preventDefault();

    // If the media frame already exists, reopen it.
    if (custom_file_frame) {
      custom_file_frame.open();
      return;
    }

    // Create the media frame.
    // custom_file_frame = wp.media.frames.downloadable_file = wp.media({
    //     title: '<?php esc_html_e('Choose an image', 'woocommerce'); ?>',
    //     button: {
    //         text: '<?php esc_html_e('Use image', 'woocommerce'); ?>'
    //     },
    //     multiple: false
    // });

    // When an image is selected, run a callback.
    custom_file_frame.on("select", function () {
      var custom_attachment = custom_file_frame
        .state()
        .get("selection")
        .first()
        .toJSON();
      var custom_attachment_thumbnail =
        custom_attachment.sizes.thumbnail || custom_attachment.sizes.full;

      jQuery("#product_cat_custom_image_id").val(custom_attachment.id);
      // jQuery('#product_cat_custom_image').find('img').attr('src', custom_attachment_thumbnail.url);
      jQuery("#product_cat_custom_image").style.backgroundImage =
        custom_attachment_thumbnail.url;
      jQuery(".remove_custom_image_button").show();
    });

    // Finally, open the modal.
    custom_file_frame.open();
  });

  jQuery(document).on("click", ".remove_custom_image_button", function () {
    // jQuery('#product_cat_custom_image').style.backgroundImage = <?php echo esc_js(wc_placeholder_img_src()); ?>;
    jQuery("#product_cat_custom_image_id").val("");
    jQuery(".remove_custom_image_button").hide();
    return false;
  });
});
