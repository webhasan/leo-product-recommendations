(function ($) {
  var __ = wp.i18n.__;
  var App = {
    colorPicker: function() {
        $('.wpr-setting-page .color-picker').spectrum({
          allowEmpty: true,
          showInput: true,
          showAlpha: true,
          preferredFormat: "rgb"
        });
    },

    cssEditor: function () {
      wp.codeEditor.initialize($(".css-editor"), wpr_css_editor);
    },

    showHideSettings: function () {
      // grid and slider
      if ($('#wpr-field-layout_type input[type="radio"]:checked').val() === 'grid') {
        $('.wpr-setting-page .form-table tr').eq(3).hide();
      }else if($('#wpr-field-layout_type input[type="radio"]:checked').val() === 'slider') {
        $('.wpr-setting-page .form-table tr').eq(2).hide();
      }

      $('#wpr-field-layout_type input[type="radio"]').on('change', function () {
        if (this.value === 'slider') {
          $('.wpr-setting-page .form-table tr').eq(3).slideDown();
          $('.wpr-setting-page .form-table tr').eq(2).hide();
        } else {
          $('.wpr-setting-page .form-table tr').eq(2).slideDown();
          $('.wpr-setting-page .form-table tr').eq(3).hide();
        }
      });

      // global selction 
      if(!$('input[name="lc_wpr_settings[active_global_settings]"]').is(':checked')) {
        $(
          "#wpr-field-selection_options, #wpr-field-disable_global_override"
        ).css({
          opacity: 0.3,
          pointerEvents: "none",
        });
      }

      $('input[name="lc_wpr_settings[active_global_settings]"]').on('change', function() {
        if(this.checked) {
          $(
            "#wpr-field-selection_options, #wpr-field-disable_global_override"
          ).css({
            opacity: 1,
            pointerEvents: "inherit",
          });
        }else {
          $(
            "#wpr-field-selection_options, #wpr-field-disable_global_override"
          ).css({
            opacity: 0.3,
            pointerEvents: "none",
          });
        }
      });

      // category selector
      if ($("#global_categories input:checked").val() === "same_categories") {
        $("#global_custom_categories").css({
          opacity: 0.3,
          pointerEvents: "none",
        });
      } else {
        $("#global_custom_categories").css({
          opacity: 1,
          pointerEvents: "inherit",
        });
      }

      $("#global_categories input").on("change", function () {

        if (this.value === "same_categories") {
          $("#global_custom_categories").css({
            opacity: 0.3,
            pointerEvents: "none",
          });
        } else {
          $("#global_custom_categories").css({
            opacity: 1,
            pointerEvents: "inherit",
          });
        }
      });
    },

    select2: function() {
      $(".wpr-setting-page .category-selector select").select2({
        placeholder: __("All Categories", "woo-product-recommendations"),
      });

      $(".wpr-setting-page .tags-selector select").select2({
        placeholder: __("All Tags","woo-product-recommendations"),
      });
    }
  }

  App.init = function () {
    this.colorPicker();
    this.cssEditor();
    this.showHideSettings();
    this.select2();
  }

  $(function () {
    App.init();
  });
})(jQuery);
