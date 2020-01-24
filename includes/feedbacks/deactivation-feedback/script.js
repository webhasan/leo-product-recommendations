(function($) {
    $(function() {
        $deactive_links = $('.wp-list-table.plugins .active').find('.deactivate a');
        
        $deactive_links.each(function() {
            var deactivationLink = $(this).attr('href');


            var pluginSlug = $(this).closest('tr.active').data('slug');
            var targetedModalId = '#pgfy-feedback-modal-' + pluginSlug;

            $(targetedModalId).find('a.pgfy-deactivation-link').attr('href',deactivationLink);

            $(this).on('click',function(e) {

                if($(targetedModalId).length) {
                    e.preventDefault();
                    $(targetedModalId).addClass('is-active');
                }

            });
        });
        
    });
    
})(jQuery);