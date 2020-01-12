/* global wc_add_to_cart_params */
(function($) {
    $(function() {

        if ( typeof wc_add_to_cart_params === 'undefined' ) {
            return false;
        }

        $('.entry-summary form.cart').on('submit', function (e) {

            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var serializedData = $form.serializeArray();
            var data = {};

            serializedData.forEach(function(option) {
                if(option.name !== 'add-to-cart') {
                    data[option.name] = option.value;
                }
            });

            if(!data.product_id) {
                data.product_id = $submitButton.val();
            }

            // add product id to button to catch it by modal.
            $submitButton.attr('data-product_id', data.product_id);

            $submitButton.removeClass( 'added' );
            $submitButton.addClass( 'loading' );

            // Trigger event.
            $( document.body ).trigger( 'adding_to_cart', [ $submitButton, data ] );

            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ),
                data: data,

                success: function( response ) {
                    if ( ! response ) {
                        return;
                    }

                    if (response.error && response.product_url) {
                        alert('Error! Please fill all required fields before adding this product to your cart.')
                        window.location = response.product_url;
                        return; // if error found quite here and reload
                    }


                    $('.woocommerce-error').remove();
                    $submitButton.addClass('added').removeClass('loading');

                    // Trigger event so themes can refresh other areas.
                    $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, $submitButton ] );
                },

                dataType: 'json'
            });

            return false;
        });

        // storefront sticky add to cart
        // Todo: apply Ajax add to cart on "storefront-sticky-add-to-cart__content-button" 

        // $('body').on('click','.storefront-sticky-add-to-cart__content-button', function(e) {
        //     e.preventDefault();
        //     $('.entry-summary .single_add_to_cart_button').click();
        // });

    });

})(jQuery);


