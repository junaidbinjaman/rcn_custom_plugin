(function ($) {
  'use strict';

  /**
   * All of the code for public-facing JavaScript source
   * should reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */

  $(function () {

    

    $('.rcn-nav-child-menu-arrow').on('click', function(e) {
      $(this).siblings('.rcn-nav-child-menu-wrapper').toggle();
      $(this).find('.dashicons-arrow-up').toggle();
      $(this).find('.dashicons-arrow-down').toggle();

      $(this).siblings().css({
        'borderLeft': 'solid 1px red'
      });

      e.stopPropagation();
    });
  });
})(jQuery);
