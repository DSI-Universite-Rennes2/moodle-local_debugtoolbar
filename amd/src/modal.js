define(['jquery', 'core/modal_factory'], function($, ModalFactory) {
    return {
        initialize: function() {
            $('table.cachesused:eq(0)').attr('id', 'local-debugtoolbar-cache');
            $('table.cachesused:eq(1)').attr('id', 'local-debugtoolbar-sessions');

            $('.local-debugtoolbar-modal').on('click', function(e) {
                ModalFactory.create({
                    type: ModalFactory.types.ALERT,
                    title: $(e.currentTarget).text(),
                    body: $($(e.currentTarget).attr('data-targetid')),
                    large: true
                })
                .then(function(modal) {
                    modal.show();
                });
            });
        }
    };
});
