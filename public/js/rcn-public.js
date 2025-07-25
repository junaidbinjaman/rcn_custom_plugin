(function ($) {
    'use strict';

    /**
     * All of the code for public-facing JavaScript source
     * should reside in this file.
     *
     * This enables to define handlers, for when the DOM is ready:
     */

    $(function () {
        $(document).on('elementor/popup/show', function (event, id, instance) {
            if (31797 === id) {
                $('.rcn-nav-child-menu-arrow').on('click', function (e) {
                    $(this).siblings('.rcn-nav-child-menu-wrapper').toggle();
                    $(this).find('.dashicons-arrow-right').toggle();
                    $(this).find('.dashicons-arrow-down').toggle();

                    $(this).siblings().css({
                        borderLeft: 'solid 1px #444E5A',
                    });

                    e.stopPropagation();
                });
            }
        });
    });
})(jQuery);


  
