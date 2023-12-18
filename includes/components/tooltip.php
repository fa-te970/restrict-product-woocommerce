<?php
function render_tooltip($title)
{
    echo <<<HTML
    <span class="xise-root tooltip ms-1" data-bs-toggle="tooltip" title="$title">
        <i class="ki-duotone ki-information-5 text-gray-500 fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </span>
    HTML;
}