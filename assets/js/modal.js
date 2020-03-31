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
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/js/modal.dev.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/modal.dev.js":
/*!********************************!*\
  !*** ./assets/js/modal.dev.js ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * jQuery wpr Modal
 */
;

(function ($) {
  var _this = this;

  //setup cart items in localstorage to exclude from recommendation
  function wpr_cart_items() {
    $.ajax({
      method: 'GET',
      url: pgfy_ajax_modal.url,
      data: {
        action: 'pgfy_get_cart_items',
        nonce: pgfy_ajax_modal.nonce
      }
    }).done(function (data) {
      localStorage.setItem('wpr_cart_items', data);
    });
  }

  wpr_cart_items();
  $(document.body).on('added_to_cart removed_from_cart wc_fragments_refreshed', 'wpr_cart_items'); //modal plugin

  $.fn.wprModal = function (options) {
    var settings = $.extend({
      action: 'open' // opton for modal open or close default: open

    }, options);
    var that = _this; // modal overlay

    var overlay = $('<div class="wpr-modal-overlay show"></div>'); // opne modal

    function opneModal() {
      var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
      that.addClass('show');
      $('body').css('paddingRight', scrollbarWidth);
      $('body').addClass('wpr-modal-opened').prepend(overlay);
      setTimeout(function () {
        that.addClass('fadeIn');
        overlay.addClass('fadeIn');
      }, 10);
      return false;
    } // close modal


    function closeModal() {
      that.removeClass('fadeIn');
      $('.wpr-modal-overlay').addClass('fadeIn');
      setTimeout(function () {
        that.removeClass('show');
        $('.wpr-modal-overlay').remove();
        $('body').css('paddingRight', 0);
        $('body').removeClass('wpr-modal-opened');
      }, 200);
    } // call modal open


    if (settings.action === 'open') {
      opneModal();
    } // call modal close


    if (settings.action === 'close') {
      closeModal();
    }

    that.find('.wpr-modal-close, .wpr-close-modal').click(function (e) {
      closeModal();
      return false;
    });
    $('.wpr-modal').click(function (e) {
      if (_this === e.target) {
        closeModal();
      }
    });
  }; // call modal


  $(document.body).on('added_to_cart', function (e) {
    for (var _len = arguments.length, data = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      data[_key - 1] = arguments[_key];
    }

    var buttonInfo = data[2];
    var button = buttonInfo[0]; //don't show modal inside modal

    if (!$(button).closest('.recommended-product-list').length) {
      var buttonId = $(button).data('product_id');
      var modalId = '#wpr-modal-' + buttonId;

      if ($(modalId).length) {
        var $recommendProductsWrapper = $(modalId).find('.recommend-products-wrapper');
        var recommendProducts = $recommendProductsWrapper.data('recommend-ids');

        if (recommendProducts) {
          recommendProducts = recommendProducts.split(',').map(Number);
        }

        var addedProducts = localStorage.getItem('wpr_cart_items');

        if (addedProducts) {
          addedProducts = addedProducts.split(',').map(Number);
          recommendProducts = recommendProducts.filter(function (id) {
            return addedProducts.indexOf(id) < 0;
          });
        } // return if all recommend product are already in cart


        if (!recommendProducts.length) return;
        $(modalId).wprModal();
        $.ajax({
          method: 'GET',
          url: pgfy_ajax_modal.url,
          data: {
            action: 'fetch_modal_products',
            nonce: pgfy_ajax_modal.nonce,
            recommended_items: recommendProducts
          }
        }).done(function (data) {
          $recommendProductsWrapper.html(data);
        });
      }
    }
  });
})(jQuery);

/***/ })

/******/ });
//# sourceMappingURL=modal.js.map