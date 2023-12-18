<?php
// $parent_dir_path = plugin_dir_path(__FILE__); // Navigate up twice to reach the plugin root
// require_once $parent_dir_path . 'tooltip.php';

function render_input($input_label, $input_name, $placeholder, $input_value, $tooltip)
{
    echo <<<HTML
        <div class="d-flex flex-column mb-8 fv-row fv-plugins-icon-container">
            <!--begin::Label-->
            <label for="$input_name" class="d-flex align-items-center fs-6 fw-semibold mb-2">
            <span class="required">$input_label</span>
                <!-- <span class="ms-1" data-bs-toggle="tooltip" aria-label="$tooltip"
                    data-bs-original-title="$tooltip" data-kt-initialized="1">
                    <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                </span> -->
            </label>
            <!--end::Label-->
            <input type="text" class="form-control form-control-solid" placeholder="$placeholder" name="$input_name" id="$input_name" value="$input_value">
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
        </div>
HTML;
}
