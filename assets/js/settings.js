(function ($) {
  var __ = wp.i18n.__;
  var App = {
    colorPicker: function() {
        $('.lpr-setting-page .color-picker').spectrum({
          allowEmpty: true,
          showInput: true,
          showAlpha: true,
          preferredFormat: "rgb"
        });
    },

    cssEditor: function () {
      wp.codeEditor.initialize($(".css-editor"), lpr_css_editor);
    },

    editor: function() {
      let {
        tinymce: tinymceObj,
        quicktags: quicktagsObj,
      } = wp.editor.getDefaultSettings();
      
      wp.editor.initialize('default-heading-editor', {
        tinymce: {
          ...tinymceObj,
          toolbar1:
            "formatselect,,forecolor,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media,wp_adv",
          toolbar2:
            "strikethrough,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
          plugins:
            "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",

          setup: function (editor) {
            editor.on("NodeChange", function (e) {
              editor.save();
            });
          }
        },
        quicktags: quicktagsObj,
      });
    },
    switchHeadingType: function() {
     $('.lpr-field-heading_type input[type="radio"]').on('click', function() {
        $('.lpr-field-heading_type .heading-field>div').hide();
        $('.' + this.value).fadeIn();
      });
    },

    showHideSettings: function () {
      // grid and slider
      if ($('#lpr-field-layout_type input[type="radio"]:checked').val() === 'grid') {
        $('.lpr-setting-page #lpr-field-slider_options').closest('tr').hide();
      }else if($('#lpr-field-layout_type input[type="radio"]:checked').val() === 'slider') {
        $('.lpr-setting-page #lpr-field-grid_options').closest('tr').hide();
      }

      $('#lpr-field-layout_type input[type="radio"]').on('change', function () {
        console.log(this.value);

        if (this.value === 'slider') {
          $('.lpr-setting-page #lpr-field-slider_options').closest('tr').slideDown();
          $('.lpr-setting-page #lpr-field-grid_options').closest('tr').hide();
        } else {
          $('.lpr-setting-page #lpr-field-grid_options').closest('tr').slideDown();
          $('.lpr-setting-page #lpr-field-slider_options').closest('tr').hide();
        }
      });

      // global selection 
      if(!$('input[name="lc_lpr_settings[active_global_settings]"]').is(':checked')) {
        $(
          "#lpr-field-selection_options, #lpr-field-disable_global_override"
        ).css({
          opacity: 0.3,
          pointerEvents: "none",
        });
      }

      $('input[name="lc_lpr_settings[active_global_settings]"]').on('change', function() {
        if(this.checked) {
          $(
            "#lpr-field-selection_options, #lpr-field-disable_global_override"
          ).css({
            opacity: 1,
            pointerEvents: "inherit",
          });
        }else {
          $(
            "#lpr-field-selection_options, #lpr-field-disable_global_override"
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
      $(".lpr-setting-page .category-selector select").select2({
        placeholder: __("All Categories", "leo-product-recommendations"),
      });

      $(".lpr-setting-page .tags-selector select").select2({
        placeholder: __("All Tags","leo-product-recommendations"),
      });
    }
  }

  App.init = function () {
    this.colorPicker();
    this.cssEditor();
    this.editor();
    this.showHideSettings();
    this.select2();
    this.switchHeadingType();
  }

  $(function () {
    App.init();
  });
})(jQuery);
