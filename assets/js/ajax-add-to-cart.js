(function($) {
    $(function() {
        var __ = wp.i18n.__;
        //var icon = '<svg xmlns="http://www.w3.org/2000/svg" height="512px" viewBox="0 0 512 512" width="512px" class=""><g><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#2196f3" data-original="#2196F3" class="" style="fill:#46C28E" data-old_color="#2196f3"></path><path d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0" fill="#fafafa" data-original="#FAFAFA" class="active-path" style="fill:#FFFFFF" data-old_color="#fafafa"></path></g> </svg>';

        function dataObj(formArray) {
            var returnArray = {};
            for (var i = 0; i < formArray.length; i++){
              returnArray[formArray[i]['name']] = formArray[i]['value'];
            }
            return returnArray;
        }

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


