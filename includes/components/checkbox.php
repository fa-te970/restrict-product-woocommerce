<?php
function render_checkbox($input_id, $input_name, $input_value, $label_text, $checked_attribute = null)
{
    $is_checked = $checked_attribute ? "checked = checked" : "";

    echo <<<HTML
        <!--begin::Checkbox-->
        <div class="xise-root form-check form-check-custom form-check-solid">
            <input class="form-check-input" type="checkbox" value="$input_value" id="$input_id" name="$input_name" $is_checked>
            <label class="form-check-label ms-3 fs-5" for="$input_id">$label_text</label>
        </div>
        <!--end::Checkbox-->
HTML;
}