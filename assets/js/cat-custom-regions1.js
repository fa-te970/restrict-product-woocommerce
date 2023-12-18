(function ($) {
  $("#restrict_country, #restrict_country_add").change(function () {
    if ($(this).is(":checked")) {
      $("#regions").show();
      $("#regions-redirect").show();
      // $('#selected_country, #selected_country_add').select2('open');
      // $($("#selected_country, #selected_country_add").select2("select2-container")).addClass("xise-root");
      $("#selected_country, #selected_country_add").select2("focus");
    } else {
      $("#regions").hide();
      $("#regions-redirect").hide();
    }
  });

  function xise_selectAll() {
    $("#selected_country > option").prop("selected", true);
    $("#selected_country").trigger("change");
  }

  // Convert country name to a suitable filename
  function xise_getFlagFilename(countryName) {
    // Define a mapping of special characters to their ASCII equivalents
    var characterMap = {
      å: "a",
    };

    // Handle specific country name differences
    var countryNameMapping = {
      "bonaire,-saint-eustatius-and-saba": "bonaire",
      belau: "belarus",
      antarctica: "",
      "bouvet-island": "",
      "cocos-(keeling)-islands": "cocos-island",
      "congo-(brazzaville)": "",
      "congo-(kinshasa)": "",
      "cura&ccedil;ao": "curacao",
      cyprus: "",
      eswatini: "",
      "faroe-islands": "",
      "french-guiana": "",
      "french-southern-territories": "",
      guadeloupe: "",
      guyana: "",
      "heard-island-and-mcdonald-islands": "",
      "marshall-islands": "marshall-island",
      mayotte: "",
      "new-caledonia": "",
      "north-macedonia": "",
      "palestinian-territory": "palestine",
      pitcairn: "pitcairn-islands",
      reunion: "",
      "s&atilde;o-tom&eacute;-and-pr&iacute;ncipe": "",
      "saint-barth&eacute;lemy": "",
      "saint-helena": "",
      "saint-lucia": "",
      "saint-martin-(dutch-part)": "",
      "saint-martin-(french-part)": "",
      "saint-pierre-and-miquelon": "",
      "saint-vincent-and-the-grenadines": "",
      "south-georgia/sandwich-islands": "",
      "svalbard-and-jan-mayen": "",
      "timor-leste": "",
      "turks-and-caicos-islands": "",
      "united-kingdom-(uk)": "united-kingdom",
      "united-states-(us)": "united-states",
      "united-states-(us)-minor-outlying-islands": "",
      vatican: "",
      "virgin-islands-(british)": "",
      "virgin-islands-(us)": "",
      "wallis-and-futuna": "",
      "western-sahara": "",
    };

    // Convert country name to lowercase and replace special characters
    var normalizedCountryName = countryName
      .toLowerCase()
      .replace(/[å ]/g, function (match) {
        if (match === "å") {
          return characterMap[match]; // Replacing 'å' with mapped value from characterMap
        } else if (match === " ") {
          return "-"; // Replacing space with hyphen
        }
      });

    // Handle country name discrepancies
    if (countryNameMapping[normalizedCountryName] !== undefined) {
      return countryNameMapping[normalizedCountryName];
    } else {
      return normalizedCountryName.replace(/\s+/g, "-");
    }
  }

  var optionFormat = function (item) {
    var rootPath = custom_country_script_params.path;

    if (!item.id) {
      return item.text;
    }

    var span = document.createElement("span");
    var flagFilename = xise_getFlagFilename(item.text);

    var flagImgUrl =
      rootPath + "assets/metronic/media/flags/" + flagFilename + ".svg";

    if (flagFilename == "") {
      span.textContent = item.text;
    } else {
      var img = document.createElement("img");
      img.src = flagImgUrl;
      img.className = "rounded-circle h-20px me-2";
      img.alt = "image";

      span.appendChild(img);
      span.appendChild(document.createTextNode(item.text));
    }
    span.classList.add("xise-root");

    return $(span);
  };

  // Attach the functions to the buttons
  $(document).on("click", "#select-all-countries", xise_selectAll);

  // Class definition
  var KTSelect2 = (function () {
    var initCountryDropDown = function (selectedCountryCodes, countries) {
      // Pre-select items based on selectedCountryCodes
      var selectedItems = countries.map(function (country) {
        if (selectedCountryCodes.includes(country.id)) {
          return {
            id: country.id,
            text: country.text,
            selected: true,
          };
        }
        return country;
      });

      // multi select
      $("#selected_country").select2({
        placeholder: "Select a country",
        data: selectedItems,
        containerCssClass: "xise-root",
        templateResult: optionFormat,
        templateSelection: optionFormat,
      });
    };

    return {
      init: function () {
        const inputElement = document.getElementById("resultInput_states");
        var countries = custom_country_script_params.countries;
        var selectedCountryCodes = [];
        var data = [];

        if (inputElement) {
          //   [{ state: "FRS", countryCode: "IR" },
          //     { state: "Istanbul", countryCode: "TR" }];
          var selectedCountries = JSON.parse(inputElement?.value);
          selectedCountryCodes = [
            ...new Set(selectedCountries.map((item) => item.countryCode)),
          ];
        }

        for (const key in countries) {
          if (countries.hasOwnProperty(key)) {
            data.push({
              id: key,
              text: countries[key],
            });
          }
        }

        initCountryDropDown(selectedCountryCodes, data);
        // $("#selected_country").select2("container").addClass("xise-root");
      },
    };
  })();

  // Initialization
  jQuery(document).ready(function () {
    KTSelect2.init();
  });
})(jQuery);
