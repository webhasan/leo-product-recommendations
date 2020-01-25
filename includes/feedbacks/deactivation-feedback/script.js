(function($) {
    //esc_html 
    function escHtml(unsafe) {
        return unsafe.replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
     }

    $(function() {
        $deactive_links = $('.wp-list-table.plugins .active').find('.deactivate a');
        
        $deactive_links.each(function() {
            var pluginSlug = $(this).closest('tr.active').data('slug');
            var $modal = $('#pgfy-feedback-modal-' + pluginSlug );

            if($modal.length) {
                var deactivationLink = $(this).attr('href');
                var $modalRadio = $modal.find('label input.reason');
                var $form = $modal.find('form');
                var $submitButton = $modal.find('.pgfy-feedback-modal-card-foot button');

                $modal.find('a.pgfy-feedback-deactivation-link').attr('href',deactivationLink);

                // active modal
                $(this).on('click',function(e) {
                    e.preventDefault();
                    $modal.addClass('is-active');
                });
                
                // show description and input field
                $modalRadio.on('change', function() {
                    $(this).closest('fieldset').siblings().find('.pgfy-inner-field').slideUp();
                    $(this).parent('label').next('.pgfy-inner-field').slideDown();
                    
                });
                
                // submit button action
                $submitButton.on('click', function(e) {
                    e.preventDefault();
                    $form.submit();
                });

                //form on submission
                $form.on('submit', function(e) {
                    $submitButton.css('opacity','.5');

                    e.preventDefault();

                    formData = $(this).serializeArray().map(function(item) {
                        item.value = escHtml(item.value);
                        return item;
                    });

                    var request =  $.ajax({
                        method: 'POST',
                        url: ajax_url,
                        data: {
                            formData: formData,
                            action: 'send_deactivation_feedback'
                        }
                    })
                    
                    request.done(function() {
                        window.location.href = deactivationLink;
                    });

                    request.fail(function() {
                        window.location.href = deactivationLink;
                    });
                });
            }

        });   
        
        // close modal
        $('.pgfy-feedback-modal button.pgfy-feedback-modal-close').on('click', function() {
            $(this).closest('.pgfy-feedback-modal').removeClass('is-active');
        });
    });
})(jQuery);

