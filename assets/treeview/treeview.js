(function ($) {
  $(document).ready(function () {
    var countryStatesObject = country_script_params.country_states;
    var countries = country_script_params.countries;
    var selectedCountriesNode = [];

    // Get selected countries and filter tree to just show them
    const inputElement = document.getElementById("resultInput_states");
    var selectedStates = [];

    if (inputElement) {
      selectedStates = JSON.parse(inputElement?.value);
      // Loop through the selectedStates array
      for (var i = 0; i < selectedStates.length; i++) {
        var countryCode = selectedStates[i].countryCode;

        // Check if the countryCode is not already in the selectedCountries array
        if (!selectedCountriesNode.includes(countryCode)) {
          selectedCountriesNode.push(countryCode);
        }
      }
      countryStatesObject = xise_getSelectedCountriesStates(
        countryStatesObject,
        selectedStates,
        false
      );
    }

    // Resulting data structure
    var dataStructure = [];

    // First, add "all" as the parent
    dataStructure.push({ id: "0", parent: "#", text: "All", stateCode: "" });

    // Loop through each country in the object
    dataStructure = xise_createDataStructure(
      dataStructure,
      countryStatesObject,
      countries
    );

    dataStructure.sort((a, b) => a.text.localeCompare(b.text));

    // Now you have the data structure in the desired format
    var jstreeCountries = $("#jstree-countries")
      .jstree({
        plugins: ["search", "checkbox", "wholerow", "crrm"],
        // ["themes", "html_data", "checkbox", "ui", "crrm"],
        core: {
          data: dataStructure,
          animation: false,
          expand_selected_onload: false,
          themes: {
            icons: false,
          },
        },
        search: {
          show_only_matches: true,
          show_only_matches_children: true,
        },
      })
      .on("loaded.jstree", function () {
        const inputElement = document.getElementById("resultInput_states");
        var selectedStates = [];

        if (inputElement) {
          selectedStates = JSON.parse(inputElement?.value);

          selectedStates.forEach(function (selectedData) {
            var state = selectedData.state;
            var countryCode = selectedData.countryCode;

            // Select state node with the specified state and countryCode
            if (state === "all_states") {
              $("#jstree-countries").jstree("select_node", countryCode);
            } else {
              $("#jstree-countries").jstree(
                "select_node",
                countryCode + "-" + state
              );
            }
          });
        }
      });

    $("#search").on("keyup change", function () {
      $("#jstree-countries").jstree(true).search($(this).val());
    });

    $("#clear").click(function (e) {
      $("#search").val("").change().focus();
    });

    $("#jstree-countries").on("ready.jstree", function () {
      // Attach the event handler for changed.jstree
      $("#jstree-countries").on("changed.jstree", function (e, data) {
        var selectedNodeIds = data.selected;
        // var objects = data.instance.get_selected(true);
        // var leaves = $.grep(objects, function (o) {
        //   return data.instance.is_leaf(o);
        // });

        // Create an array to store the selected items
        var selectedItemsArray = [];
        // Iterate through selected node IDs and get their corresponding data
        selectedNodeIds.forEach(function (nodeId) {
          var node = data.instance.get_node(nodeId);
          //   {id: 'AF', text: 'Afghanistan', icon: true, parent: '0'
          var state = node.original.stateCode; // state or country
          var countryNode = data.instance.get_node(node.parent);
          // for country, the value is 0
          var countryId = countryNode.id;

          // Check if the country has no states
          if (node.parent === "0") {
            state = "all_states";
            countryId = nodeId;
          }

          // Create an object for the selected item and push it to the array
          selectedItemsArray.push({ state: state, countryCode: countryId });
        });
        // Assuming your data is an array of objects like below
        // Loop through the data to process 'all' states and remove duplicates
        var countriesWithAllStates = [];
        for (var i = 0; i < selectedItemsArray.length; i++) {
          if (selectedItemsArray[i].state === "all_states") {
            countriesWithAllStates.push(selectedItemsArray[i].countryCode);
          }
        }

        // Remove individual state selections for countries with all states
        for (var i = 0; i < selectedItemsArray.length; i++) {
          if (
            countriesWithAllStates.includes(
              selectedItemsArray[i].countryCode
            ) &&
            selectedItemsArray[i].state !== "all_states"
          ) {
            selectedItemsArray.splice(i, 1);
            i--; // Adjust index due to removal
          }
        }
        // Set result input
        var resultInput = document.getElementById("resultInput_states");
        resultInput.value = JSON.stringify(selectedItemsArray);
      });
    });
    // END_OnChange

    // Assuming the Select2 component is already initialized
    $("#selected_country").on("change", function () {
      var selectedValues = $(this).val();
      xise_updateTree(selectedValues);
    });
    // END onChange

    function xise_updateTree(selectedValues) {
      var jstreeData = $("#jstree-countries")
        .jstree(true)
        .get_json("#", { flat: true });

      // Filter the data based on selected countries or their parents
      var filteredData = jstreeData
        .filter((item) => {
          return (
            item.id === "0" ||
            selectedValues.includes(item.id) ||
            selectedValues.includes(item.parent)
          );
        })
        .map((item) => ({
          ...item,
          stateCode:
            item?.original?.stateCode || item.id.split("-").slice(1).join("-"),
        }));

      var selectedStates = selectedValues.filter(
        (country) =>
          country !== "0" && !filteredData.some((item) => item.id === country)
      );

      var countryStatesObject = country_script_params.country_states;

      countryStatesObject = xise_getSelectedCountriesStates(
        countryStatesObject,
        selectedStates,
        true
      );

      // Resulting data structure
      var dataStructure = [];
      dataStructure = xise_createDataStructure(
        dataStructure,
        countryStatesObject,
        countries
      );

      var mergedArray = filteredData.concat(dataStructure);

      // Check if the mergedArray already contains an item with id = "0"
      var hasAllItem = mergedArray.some(function (item) {
        return item.id === "0";
      });

      // If not, add the "All" item to mergedArray
      if (!hasAllItem) {
        mergedArray.push({
          id: "0",
          parent: "#",
          text: "All",
          stateCode: "",
        });
      }

      // Sort the mergedArray alphabetically based on the 'text' property
      mergedArray.sort((a, b) => a.text.localeCompare(b.text));

      // Update the jstree instance with the filtered data and refresh
      $("#jstree-countries").jstree(true).settings.core.data = mergedArray;
      $("#jstree-countries").jstree(true).refresh(true);
      $("#jstree-countries").on("refresh.jstree", function (e) {
        $("#jstree-countries").jstree("select_node", selectedStates);
      });
    }
    // {id: '0', text: 'All', icon: true, li_attr: {…}, a_attr: {…}, …}
    // {id: 'AF', text: 'Afghanistan', icon: true, li_attr: {…}, a_attr: {…}, …}
    // {id: 'AL-Berat', text: 'Berat', icon: true, li_attr: {…}, a_attr: {…}, …}

    function xise_getSelectedCountriesStates(
      countryStatesObject,
      selectedStates,
      isFiltered
    ) {
      // Filter countryStatesObject based on selected countries
      const filteredStatesObject = {};

      selectedStates.forEach((selectedCountry) => {
        const countryCode = isFiltered
          ? selectedCountry
          : selectedCountry?.countryCode;
        if (countryStatesObject[countryCode]) {
          filteredStatesObject[countryCode] = countryStatesObject[countryCode];
        }
      });

      return filteredStatesObject;
    }

    function xise_createDataStructure(
      dataStructure,
      countryStatesObject,
      countries
    ) {
      for (var countryCode in countryStatesObject) {
        if (countryStatesObject.hasOwnProperty(countryCode)) {
          var countryFullName = countries[countryCode];

          // Add the country as a child of "all" with its full name
          dataStructure.push({
            id: countryCode,
            parent: "0",
            text: countryFullName,
            stateCode: "",
            // ...(isFiltered
            //   ? {
            //       state: {
            //         disabled: false,
            //         loaded: true,
            //         opened: false,
            //         selected: true,
            //       },
            //     }
            //   : {}),
          });

          // Loop through the states for the country
          var states = countryStatesObject[countryCode];
          for (var stateCode in states) {
            if (states.hasOwnProperty(stateCode)) {
              var stateId = countryCode + "-" + stateCode;
              dataStructure.push({
                id: stateId,
                parent: countryCode,
                text: states[stateCode],
                stateCode: stateCode,
              });
            }
          }
        }
      }
      return dataStructure;
    }
  });
})(jQuery);
