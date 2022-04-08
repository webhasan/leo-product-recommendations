/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/settings.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/settings.js":
/*!*******************************!*\
  !*** ./assets/js/settings.js ***!
  \*******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0__);


function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_0___default()(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

(function ($) {
  var __ = wp.i18n.__;
  var App = {
    colorPicker: function colorPicker() {
      $('.lpr-setting-page .color-picker').spectrum({
        allowEmpty: true,
        showInput: true,
        showAlpha: true,
        preferredFormat: "rgb"
      });
    },
    cssEditor: function cssEditor() {
      wp.codeEditor.initialize($(".css-editor"), lpr_css_editor);
    },
    editor: function editor() {
      var _wp$editor$getDefault = wp.editor.getDefaultSettings(),
          tinymceObj = _wp$editor$getDefault.tinymce,
          quicktagsObj = _wp$editor$getDefault.quicktags;

      wp.editor.initialize('default-heading-editor', {
        tinymce: _objectSpread({}, tinymceObj, {
          toolbar1: "formatselect,,forecolor,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media,wp_adv",
          toolbar2: "strikethrough,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
          plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
          setup: function setup(editor) {
            editor.on("NodeChange", function (e) {
              editor.save();
            });
          }
        }),
        quicktags: quicktagsObj
      });
    },
    switchHeadingType: function switchHeadingType() {
      $('.lpr-field-heading_type input[type="radio"]').on('click', function () {
        $('.lpr-field-heading_type .heading-field>div').hide();
        $('.' + this.value).fadeIn();
      });
    },
    showHideSettings: function showHideSettings() {
      // grid and slider
      if ($('#lpr-field-layout_type input[type="radio"]:checked').val() === 'grid') {
        $('.lpr-setting-page #lpr-field-slider_options').closest('tr').hide();
      } else if ($('#lpr-field-layout_type input[type="radio"]:checked').val() === 'slider') {
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
      }); // global selection 

      if (!$('input[name="lc_lpr_settings[active_global_settings]"]').is(':checked')) {
        $("#lpr-field-selection_options, #lpr-field-disable_global_override").css({
          opacity: 0.3,
          pointerEvents: "none"
        });
      }

      $('input[name="lc_lpr_settings[active_global_settings]"]').on('change', function () {
        if (this.checked) {
          $("#lpr-field-selection_options, #lpr-field-disable_global_override").css({
            opacity: 1,
            pointerEvents: "inherit"
          });
        } else {
          $("#lpr-field-selection_options, #lpr-field-disable_global_override").css({
            opacity: 0.3,
            pointerEvents: "none"
          });
        }
      }); // category selector

      if ($("#global_categories input:checked").val() === "same_categories") {
        $("#global_custom_categories").css({
          opacity: 0.3,
          pointerEvents: "none"
        });
      } else {
        $("#global_custom_categories").css({
          opacity: 1,
          pointerEvents: "inherit"
        });
      }

      $("#global_categories input").on("change", function () {
        if (this.value === "same_categories") {
          $("#global_custom_categories").css({
            opacity: 0.3,
            pointerEvents: "none"
          });
        } else {
          $("#global_custom_categories").css({
            opacity: 1,
            pointerEvents: "inherit"
          });
        }
      });
    },
    select2: function select2() {
      $(".lpr-setting-page .category-selector select").select2({
        placeholder: __("All Categories", "leo-product-recommendations")
      });
      $(".lpr-setting-page .tags-selector select").select2({
        placeholder: __("All Tags", "leo-product-recommendations")
      });
    }
  };

  App.init = function () {
    this.colorPicker();
    this.cssEditor();
    this.editor();
    this.showHideSettings();
    this.select2();
    this.switchHeadingType();
  };

  $(function () {
    App.init();
  });
})(jQuery);

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/defineProperty.js":
/*!***************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/defineProperty.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;

/***/ })

/******/ });
//# sourceMappingURL=settings.min.js.map