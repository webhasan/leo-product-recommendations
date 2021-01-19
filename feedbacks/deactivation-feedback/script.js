(function($, __) {
      $(function() {

        $deactive_links = $('.wp-list-table.plugins .active').find('.deactivate a');
        
        $deactive_links.each(function() {
            var pluginSlug = $(this).closest('tr.active').data('slug');
            var $modal = $('#lprw-feedback-modal-' + pluginSlug );

            if($modal.length) {
                var deactivationLink = $(this).attr('href');
                var $modalRadio = $modal.find('label input.reason');
                var $form = $modal.find('form');
                var $submitButton = $modal.find('.lprw-feedback-modal-card-foot button');

                $modal.find('a.lprw-feedback-deactivation-link').attr('href',deactivationLink);

                // active modal
                $(this).on('click',function(e) {
                    e.preventDefault();
                    $modal.addClass('is-active');
                });
                
                // show description and input field
                $modalRadio.on('change', function() {
                    $(this).closest('fieldset').siblings().find('.lprw-inner-field').slideUp();
                    $(this).parent('label').next('.lprw-inner-field').slideDown();
                    
                });
                
                // submit button action
                $submitButton.on('click', function(e) {
                    e.preventDefault();
                    $form.submit();
                });

                

                //form on submission
                $form.on('submit', function(e) {
                    
                    e.preventDefault();

                    $formData = $(this).serializeArray().reduce(function(obj, item) {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});

                    if(!$formData.reason) {
                        $modal.removeClass('is-active');
                        return false;
                    }

                    
                    //$submitButton.addClass('submiting').text(__('Deactivating...','pgfy_deactivation_plugin'));

                    var request =  $.ajax({
                        method: 'POST',
                        url: ajax_url,
                        data: {
                            action: 'deactivation_feedback',
                            security: security,
                            formData: $formData
                        }
                    })
                    
                    request.done(function() {
                        // window.location.href = deactivationLink;
                    });

                    request.fail(function() {
                    //    window.location.href = deactivationLink;
                    });
                });
            }

        });   
        
        // close modal
        $('.lprw-feedback-modal button.lprw-feedback-modal-close').on('click', function() {
            $(this).closest('.lprw-feedback-modal').removeClass('is-active');
        });
    });
})(jQuery, wp.i18n.__);

