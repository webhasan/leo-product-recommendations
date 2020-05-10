(function($) {
    $(function() {
        var __ = wp.i18n.__;

        $('body.single-product form.cart').on('submit', function (e) {            
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var data = $form.serialize();

            var dataObject = {};
            var productId = '';

            $form.serializeArray().forEach(function(option) {
                if(option.name !== 'add-to-cart') {
                    dataObject[option.name] = option.value;
                }else {
                    productId = option.value;
                }
            });

            dataObject.product_id = $submitButton.val() ? $submitButton.val() : productId;

            if(!dataObject.product_id) {
                dataObject.product_id = $submitButton.val() ? $submitButton.val() : productId;
            }

            if(data.search('add-to-cart') === -1) {
                data += '&add-to-cart=' + dataObject.product_id;
            }

            // recommend products mdoal
            var $targetMdoal = $('#wpr-modal-' + dataObject.product_id);

            if($targetMdoal.length) {
                // add product id to button to catch it by modal.
                $submitButton.attr('data-product_id', dataObject.product_id);
                $submitButton.removeClass( 'added' );
                $submitButton.addClass( 'loading' );

                // Trigger event before add to cart.
                $( document.body ).trigger( 'adding_to_cart', [ $submitButton,  dataObject]);

                $.ajax({
                    type: 'POST',
                    url: pgfy_ajax.url,
                    data: {
                        action: 'pgfy_ajax_add_to_cart',
                        nonce: pgfy_ajax.nonce,
                        data: data
                    }
                }).done(function(response) {
                    if(response.success === true) {
                        // thinking about this
                        // $modalHeading  = $('<div class="wpr-message">' + icon + response.data.message + '</div>');
                        // $modalHeading.find('a').removeClass().addClass(' wpr-cart-button').html('View cart →'); 
                        // $targetMdoal.find('.wpr-message').replaceWith($modalHeading);

                        $( document.body ).trigger( 'added_to_cart', [ response.data.fragments, response.data.cart_hash, $submitButton ] );
                        $submitButton.addClass('added').removeClass('loading');

                    }else if(response.success === false) {
                        alert(response.data.message);
                        $submitButton.removeClass('loading');
                    }else {
                        alert(__('Something went wrong','woocommerce-product-recommend'));
                        location.reload(); 
                    }
     
                }).fail(function(response) {
                    alert(response.responseJSON.data.message);
                    location.reload();
                })
                return false;
            }
        });

    });

})(jQuery);


