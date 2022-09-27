(function ($) {
  var __ = wp.i18n.__;
  var App = {

    cssEditor: function (fieldSelector) {
      wp.codeEditor.initialize($(fieldSelector));
    },

    editor: function(file_id) {
      let {
        tinymce: tinymceObj,
        quicktags: quicktagsObj,
      } = wp.editor.getDefaultSettings();
      
      wp.editor.initialize(file_id, {
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
    }
  }

  App.init = function () {

    if($('#lc_lpr_settings_custom_style').length) {
      this.cssEditor('#lc_lpr_settings_custom_style');
    }

    if($('#lc_lpr_settings_default_heading_description').length) {
      this.editor('lc_lpr_settings_default_heading_description');
    }
  }

  $(function () {
    App.init();
  });
})(jQuery);
