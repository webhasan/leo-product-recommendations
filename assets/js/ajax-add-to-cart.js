(function($) {
    $(function() {
        var __ = wp.i18n.__;

        function getPopupCount() {
            const popupData = localStorage.getItem('lpr-popup-count');
            const hasPopupCount = (popupData && JSON.parse(popupData).count && (JSON.parse(popupData).time > Date.now() - 2 * 3600 * 1000));
            return hasPopupCount ? JSON.parse(popupData).count : 0;
        }

        // A file the visitor picked can't go through this AJAX call, so the form posts normally.
        function hasSelectedFile(form) {
            return $(form).find('input[type="file"]').filter(function() {
                return this.files && this.files.length;
            }).length > 0;
        }

        // Post the form the normal way (page reload). WooCommerce adds the product with its
        // own handler and modal.js opens the popup after the reload.
        function nativeSubmit(form, productId, submitter) {
            const $form = $(form);
            $form.data('lpr-native-submit', true);

            // A button's name/value is only sent by a real click, so copy it into a hidden field.
            const name = submitter && submitter.name ? submitter.name : 'add-to-cart';
            const value = submitter && submitter.name ? submitter.value : productId;
            const hasField = $form.find('input, select, textarea').filter(function() {
                return this.name === name && this.type !== 'submit';
            }).length > 0;

            if (!hasField && value) {
                $('<input type="hidden" data-lpr-submitter="1">').attr('name', name).val(value).appendTo($form);
            }

            HTMLFormElement.prototype.submit.call(form);
        }

        $('body').on('submit','form.cart', function (e) {
            const form = this;
            const $form = $(form);

            // Our own normal-post fallback, or a form a theme/plugin opted out with the lpr-no-ajax class.
            if ($form.data('lpr-native-submit') || $form.hasClass('lpr-no-ajax')) {
                return;
            }

            // The theme adds this form to the cart with its own AJAX call and fires
            // added_to_cart itself (Blocksy: data-add-to-cart="ajax"), so the popup opens
            // anyway. Handling it here too would send two add to cart requests.
            if (lc_ajax.theme_ajax_selector && $form.find(lc_ajax.theme_ajax_selector).length) {
                return;
            }

            const popupCount = getPopupCount();
            // fallback for older version of pro version 
            if(!lc_ajax.popup_view_times) lc_ajax.popup_view_times = 'always';
            const popupShowTimes = lc_ajax.popup_view_times === 'always' ? Infinity : +lc_ajax.popup_view_times;

            if(popupShowTimes <= popupCount) {
                return;
            }

            // Leave other submit buttons in the form alone, for example "Buy now".
            const submitter = e.originalEvent && e.originalEvent.submitter ? e.originalEvent.submitter : null;
            if (submitter && submitter.name && submitter.name !== 'add-to-cart') {
                return;
            }

            if (hasSelectedFile(form)) {
                return;
            }

            var $submitButton = $form.find('button[type="submit"]');

            // FormData keeps repeated fields (checkbox groups, multi-selects) that a flat object loses.
            const formData = new FormData(form);
            $form.find('input[type="file"][name]').each(function() {
                formData.delete(this.name);
            });

            var data = {};
            var productId = '';

            formData.forEach(function(value, name) {
                if (name !== 'add-to-cart') {
                    data[name] = value;
                } else {
                    productId = value;
                }
            });

            if (submitter && submitter.name === 'add-to-cart' && submitter.value) {
                data.product_id = submitter.value;
            } else {
                data.product_id = $submitButton.val() ? $submitButton.val() : productId;
            }

            if( !parseInt(data.product_id) ) {
                return; // not a form we understand, let it post normally
            }

            e.preventDefault();

            formData.delete('add-to-cart');
            formData.set('product_id', data.product_id);
            formData.set('action', 'lc_ajax_add_to_cart');

            // add product id to button to catch it by modal.
            $submitButton.attr('data-product_id', data.product_id);
            $submitButton.removeClass( 'added' );
            $submitButton.addClass( 'loading' );

            // Trigger event before add to cart.
            $( document.body ).trigger( 'adding_to_cart', [ $submitButton,  data]);

            $.ajax({
                type: 'POST',
                url: lc_ajax.url,
                data: formData,
                processData: false,
                contentType: false
            }).done(function(response) {

                if(response && response.success === true) {

                    $( document.body ).trigger( 'added_to_cart', [ response.data.fragments, response.data.cart_hash, $submitButton ] );
                    $submitButton.addClass('added').removeClass('loading');

                }else if(response && response.success === false && response.data && response.data.fallback) {
                    // Product type this AJAX call doesn't handle (bundles, subscriptions...).
                    $submitButton.removeClass('loading');
                    nativeSubmit(form, data.product_id, submitter);

                }else if(response && response.success === false) {
                    alert(response.data && response.data.message ? response.data.message : __('Something went wrong','leo-product-recommendations'));
                    $submitButton.removeClass('loading');
                }else {
                    alert(__('Something went wrong','leo-product-recommendations'));
                    location.reload(); 
                }

            }).fail(function(response) {
                const json = response && response.responseJSON;
                const message = json && json.data && json.data.message ? json.data.message : (json && typeof json.data === 'string' ? json.data : '');
                alert(message || __('Something went wrong','leo-product-recommendations'));
                location.reload();
            });

            return false;
        });

    });

})(jQuery);
