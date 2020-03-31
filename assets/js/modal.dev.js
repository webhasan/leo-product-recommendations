/**
 * jQuery wpr Modal
 */

;(function ( $ ) {
    //setup cart items in localstorage to exclude from recommendation
    function wpr_cart_items() {
        $.ajax({
            method: 'GET',
            url: pgfy_ajax_modal.url,
            data: {
                action: 'pgfy_get_cart_items',
                nonce: pgfy_ajax_modal.nonce
            }
        }).done(data => {
            localStorage.setItem('wpr_cart_items', data);
        });
    }

    wpr_cart_items();

    $( document.body ).on( 'added_to_cart removed_from_cart wc_fragments_refreshed', 'wpr_cart_items');
    

    //modal plugin
    $.fn.wprModal = (options) => {

        var settings = $.extend({
            action: 'open' // opton for modal open or close default: open
        }, options );

        var that = this;


        // modal overlay
        var overlay = $('<div class="wpr-modal-overlay show"></div>');

        // opne modal
        function opneModal() {
        	var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

			that.addClass('show');

			$('body').css('paddingRight', scrollbarWidth);

			$('body').addClass('wpr-modal-opened').prepend(overlay);

			setTimeout(() => {
				that.addClass('fadeIn');
				overlay.addClass('fadeIn');
			}, 10);

			return false;
        }

        // close modal
        function closeModal() {
        	that.removeClass('fadeIn');
        	$('.wpr-modal-overlay').addClass('fadeIn');

        	setTimeout(() => {
				that.removeClass('show');
				$('.wpr-modal-overlay').remove();
				$('body').css('paddingRight', 0);
				$('body').removeClass('wpr-modal-opened');
			}, 200);
        }


        // call modal open
        if(settings.action === 'open') {
        	opneModal();
        }

        // call modal close
        if(settings.action === 'close') {
        	closeModal();
        }

        that.find('.wpr-modal-close, .wpr-close-modal').click((e) => {
            closeModal();
            return false;
        });

        $('.wpr-modal').click((e) => {
        	if(this === e.target) {
        		closeModal();
        	}
        });

    };

    // call modal
    $( document.body).on('added_to_cart', (e, ...data) => {
 
        const [,,buttonInfo] = data;
        var button =  buttonInfo[0];

      
        //don't show modal inside modal
        if(! $(button).closest('.recommended-product-list').length) {

            var buttonId = $(button).data('product_id');
            var modalId = '#wpr-modal-' + buttonId;
    
            if($(modalId).length) {
                var $recommendProductsWrapper = $(modalId).find('.recommend-products-wrapper');

                var recommendProducts = $recommendProductsWrapper.data('recommend-ids');
                if(recommendProducts) {
                    recommendProducts = recommendProducts.split(',').map(Number);
                }

                var addedProducts = localStorage.getItem('wpr_cart_items');
                if(addedProducts) {
                    addedProducts = addedProducts.split(',').map(Number);
                    recommendProducts = recommendProducts.filter(id => addedProducts.indexOf( id ) < 0);
                }

                // return if all recommend product are already in cart
                if(!recommendProducts.length) return;
                $(modalId).wprModal();
            
                $.ajax({
                    method: 'GET',
                    url: pgfy_ajax_modal.url,
                    data: {
                        action: 'fetch_modal_products',
                        nonce: pgfy_ajax_modal.nonce,
                        recommended_items: recommendProducts
                    }
                }).done((data) => {
                    $recommendProductsWrapper.html(data);
                });
            }
        }
    });

}( jQuery ));

