<?php
function render_radio_buttons($group_name, $options)
{
    echo '<div class="form-group xise-root">';
    echo '<label>' . $options['label'] . '</label>';
    echo '<div class="radio-list">';

    foreach ($options['items'] as $item) {
        $is_checked = isset($item['checked']) && $item['checked'];
        $is_disabled = isset($item['disabled']) && $item['disabled'];

        $input_id = isset($item['id']) ? $item['id'] : '';
        $input_name = $group_name;
        $input_value = isset($item['value']) ? $item['value'] : '';

        echo '<label class="radio' . ($is_disabled ? ' radio-disabled' : '') . '">';
        echo '<input type="radio" name="' . $input_name . '" value="' . $input_value . '" id="' . $input_id . '"';
        if ($is_checked) {
            echo ' checked="checked"';
        }
        if ($is_disabled) {
            echo ' disabled="disabled"';
        }
        echo '/>';
        echo '<span></span>';
        echo $item['label'];
        echo '</label>';
    }

    echo '</div>';
    echo '</div>';
}
?>