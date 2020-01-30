/**
 * jQuery wpr Modal
 */

// Use Example
// $('#modalId').wprModal();
// $('#modalId').wprModal({action: 'close'});

(function ( $ ) {
    $.fn.wprModal = function(options) {

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

			setTimeout(function() {
				that.addClass('fadeIn');
				overlay.addClass('fadeIn');
			}, 10);

			return false;
        }


        // close modal
        function closeModal() {
        	that.removeClass('fadeIn');
        	$('.wpr-modal-overlay').addClass('fadeIn');

        	setTimeout(function() {
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

        that.find('.wpr-modal-close, .wpr-close-modal').click(function(e) {
            closeModal();
            return false;
        });

        $('.wpr-modal').click(function(e) {
        	if(this === e.target) {
        		closeModal();
        	}
        });

    };

    // call modal
    $( document.body).on('added_to_cart', function(e, ...data) {
        
        const [,,buttonInfo] = data;
        var button =  buttonInfo[0];

        //don't show modal inside modal
        if(! $(button).closest('.recommended-product-list').length) {

            var buttonId = $(button).data('product_id');
            var modalId = '#wpr-modal-' + buttonId;
    
            if($(modalId).length) 
                $(modalId).wprModal();

        }
    });

}( jQuery ));

