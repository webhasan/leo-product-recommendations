(function($) {
    $(function() {
        var __ = wp.i18n.__;

        $('body').on('submit','form.cart', function (e) {   
            e.preventDefault();
            
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');

            var data = {};
            var productId = '';

            $form.serializeArray().forEach(function(option) {
                if(option.name !== 'add-to-cart') {
                    data[option.name] = option.value;
                }else {
                    productId = option.value;
                }
            });

            data.product_id = $submitButton.val() ? $submitButton.val() : productId;

            if( !!parseInt(data.product_id) ) {
                // add product id to button to catch it by modal.
                $submitButton.attr('data-product_id', data.product_id);
                $submitButton.removeClass( 'added' );
                $submitButton.addClass( 'loading' );

                // Trigger event before add to cart.
                $( document.body ).trigger( 'adding_to_cart', [ $submitButton,  data]);

                $.ajax({
                    type: 'POST',
                    url: lc_ajax.url,
                    data: {
                        action: 'lc_ajax_add_to_cart',
                        ...data
                    }
                }).done(function(response) {

                    if(response.success === true) {

                        $( document.body ).trigger( 'added_to_cart', [ response.data.fragments, response.data.cart_hash, $submitButton ] );
                        $submitButton.addClass('added').removeClass('loading');

                    }else if(response.success === false) {
                        alert(response.data.message);
                        $submitButton.removeClass('loading');
                    }else {
                        alert(__('Something went wrong','leo-product-recommendations'));
                        location.reload(); 
                    }
     
                }).fail(function(response) {
                    alert(response.responseJSON.data.message);
                    location.reload();
                });

                return false;
            }
        });

    });

})(jQuery);


