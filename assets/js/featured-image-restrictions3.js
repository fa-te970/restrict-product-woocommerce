jQuery(document).ready(function ($) {
  $(".image-restriction-options, .image-upload-settings").hide();

  // Check the initial state of the restrict checkbox
  var initialRestrictValue = $("#wh_restrict_image").is(":checked");

  // Show/hide settings based on checkbox selection
  function xise_toggleSettingsVisibility() {
    if ($("#wh_restrict_image").is(":checked")) {
      $(".image-restriction-options, .image-upload-settings").show();
    } else {
      $(".image-restriction-options, .image-upload-settings").hide();
    }
  }

  // Show settings if checkbox is initially checked
  if (initialRestrictValue) {
    xise_toggleSettingsVisibility();
  }

  // Show/hide settings based on checkbox selection
  $("#wh_restrict_image").on("change", function () {
    xise_toggleSettingsVisibility();
  });

  // Custom image region visivility
  $(".custom-image-regions").hide();
  var initialUploadCustomImage = $("#wh_custom_image_radio").is(":checked");

  function xise_toggleUploadImageVisibility() {
    if (
      $("#wh_custom_image_radio").is(":checked") &&
      $("#wh_restrict_image").is(":checked")
    ) {
      $(".custom-image-regions").show();
    } else {
      $(".custom-image-regions").hide();
    }
  }

  // Show settings if checkbox is initially checked
  if (initialUploadCustomImage) {
    xise_toggleUploadImageVisibility();
  }

  // Show/hide settings based on checkbox selection
  $("#wh_custom_image_radio, #wh_thumb_radio, #wh_restrict_image").on(
    "change",
    function () {
      xise_toggleUploadImageVisibility();
    }
  );

  ////// Replace product image for restricted categories
  // Array of page elements where you want to replace images
  var selectors = [
    ".product", // Shop and search results(used in archive page and single page product)
    ".product-summary", // Product summary in cart
  ];

  function xise_replaceProductImages(customImageSettings, selectors) {
    $.each(customImageSettings, function (categoryId, settings) {
      selectors.forEach(function (selector) {
        $(selector).each(function () {
          var productElement = $(this);
          // Extract the category ID from the class attribute
          var classList = productElement.attr("class").split(/\s+/);
          var productCategoryId = null;

          for (var i = 0; i < classList.length; i++) {
            if (classList[i].indexOf("product-category-ids-") === 0) {
              productCategoryId = parseInt(
                classList[i].replace("product-category-ids-", "")
              );
              break;
            }
          }

          // Compare the extracted category ID with the categoryId in the loop
          if (
            productCategoryId !== null &&
            productCategoryId === parseInt(categoryId)
          ) {
            if (!!settings.wh_image) {
              productElement.find("img").attr("src", settings.wh_image);
              xise_removeProductImageLink();
            }
            if (!!settings.wh_image_srcset) {
              productElement
                .find("img")
                .attr("srcset", settings.wh_image_srcset);
            }

            var enlargeButton = document.querySelector(
              ".product-additional-galleries"
            );

            if (enlargeButton) {
              enlargeButton.style.display = "none";
            }

            // Create a MutationObserver
            var observer = new MutationObserver(function (mutationsList) {
              for (var mutation of mutationsList) {
                if (mutation.type === "childList") {
                  // Check if the newly added element is the one you're looking for
                  var addedElement = mutation.addedNodes[0];
                  if (
                    addedElement &&
                    addedElement.tagName === "IMG" &&
                    addedElement.getAttribute("role") === "presentation"
                  ) {
                    // Disconnect the observer to prevent triggering while modifying the element
                    observer.disconnect();

                    // Modify the src attribute of the added image element
                    addedElement.src = settings.wh_image;
                  }
                }
              }
            });

            var galleryImageElement = $(productElement).find(
              ".woocommerce-product-gallery__image"
            )[0];

            if (!!galleryImageElement)
              observer.observe(galleryImageElement, { childList: true });

            var anchorElement = $(galleryImageElement).find("a");

            if (!!anchorElement && !!settings.wh_image) {
              anchorElement.attr("href", settings.wh_image);
            }
          }
        });
      });
    });
  }

  function xise_removeProductImageLink() {
    var atag = document.querySelector(".product-image-link");
    if (atag) {
      atag.remove();
    }
  }

  xise_replaceProductImages(customImageSettings, selectors);

  function xise_observeDOMChanges() {
    var observer = new MutationObserver(function (mutationsList) {
      for (var mutation of mutationsList) {
        if (mutation.type === "childList") {
          xise_replaceProductImages(customImageSettings, selectors);
        }
      }
    });

    // Start observing changes to the entire document's DOM
    observer.observe(document.body, { childList: true, subtree: true });
  }

  // Call the function to observe DOM changes continuously
  xise_observeDOMChanges();

  // Get all elements matching the selector
  var elements = document.querySelectorAll(
    "li.product-image-hide a:first-child img"
  );
  elements.forEach(function (element) {
    element.style.display = "block";
  });
});
