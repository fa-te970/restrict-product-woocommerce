<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'components/tooltip.php';

$parent_dir_path = plugin_dir_url(dirname(dirname(__FILE__))); // Navigate up twice to reach the plugin root
global $country_states;
global $edit_dropdown;

?>
<link href="<?php echo $parent_dir_path; ?>includes/multiselect-dropdown/dropdown.css" rel="stylesheet"
	type="text/css" />

<div class="xise-root px-7 py-3">
	<!--begin::Input group-->
	<div class="mb-4">
		<?php
		global $edit_dropdown;
		$dropdown_class = $edit_dropdown ? 'select-country-wrapper-edit xise-root' : 'select-country-wrapper xise-root'
			?>
		<!--begin::Label-->
		<label class="form-label fw-semibold" for="selected_country">Countries:</label>
		<!--end::Label-->
		<!--begin::Input-->
		<div class="<?php echo $dropdown_class; ?>">
			<select id="selected_country" class="form-select form-select-solid" multiple="multiple"
				data-kt-select2="true" data-close-on-select="false" data-placeholder="Select a country"
				data-allow-clear="true">
			</select>
			<?php
			$tooltip_title = "Select countries first";
			render_tooltip($tooltip_title);
			?>
			<button type="button" class="btn btn-light-primary" id="select-all-countries">Select All</button>

		</div>
		<!--end::Input-->
	</div>
	<!--end::Input group-->
	<!--Begin JsTree Selected regions -->
	<hr />
	<div class="countries-tree-container">
		<input class="w-100" type="text" id="search" placeholder="Filter" />
		<!-- <button id="clear-filter-dropdown">Clear</button> -->
		<div id="jstree-countries">
		</div>
	</div>
	<!--End JsTree Selected regions -->
</div>