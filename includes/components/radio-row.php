<?php
function render_radio_buttons($group_name, $options)
{
    echo '<div class="row xise-root" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button]">';

    foreach ($options['items'] as $item) {
        $is_checked = isset($item['checked']) && $item['checked'];
        $input_value = isset($item['value']) ? $item['value'] : '';
        $input_id = isset($item['id']) ? $item['id'] : '';
        $label_text = $item['label'];

        echo '<div>';
        echo '<label for="$input_id" class="btn btn-outline btn-outline-dashed ' . ($is_checked ? 'btn-active-light-primary active' : 'btn-active-light-primary') . ' d-flex text-start p-6" data-kt-button="true">';
        echo '<span class="form-check form-check-custom form-check-solid form-check-sm align-items-start">';
        echo '<input class="form-check-input" type="radio" name="' . $group_name . '" value="' . $input_value . '" id="' . $input_id . '"';
        if ($is_checked) {
            echo ' checked="checked"';
        }
        echo ' />';
        echo '</span>';
        echo '<span class="ms-5">';
        echo '<span class="fs-5 mb-1 d-block form-check-label">' . $label_text . '</span>';
        echo '</span>';
        echo '</label>';
        echo '</div>';
    }

    echo '</div>';
}

?>