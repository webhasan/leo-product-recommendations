(function($) {
    $(function() {
        $deactive_links = $('.wp-list-table.plugins .active').find('.deactivate a');
        
        $deactive_links.each(function() {
            var pluginSlug = $(this).closest('tr.active').data('slug');
            var $modal = $('#pgfy-feedback-modal-' + pluginSlug );

            if($modal.length) {
                var deactivationLink = $(this).attr('href');
                var $modalRadio = $modal.find('label input.reason');

                $modal.find('a.pgfy-feedback-deactivation-link').attr('href',deactivationLink);

                // active modal
                $(this).on('click',function(e) {
                    e.preventDefault();
                    $modal.addClass('is-active');
                });

                $modalRadio.on('change', function() {
                    $(this).closest('fieldset').siblings().find('.pgfy-inner-field').slideUp();
                    $(this).parent('label').next('.pgfy-inner-field').slideDown();
                    
                })
            }

        });   
        
        // close modal
        $('.pgfy-feedback-modal button.pgfy-feedback-modal-close').on('click', function() {
            $(this).closest('.pgfy-feedback-modal').removeClass('is-active');
        });
    });
})(jQuery);

