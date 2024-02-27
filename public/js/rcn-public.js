(function ($) {
  'use strict';

  /**
   * All of the code for public-facing JavaScript source
   * should reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */

  $(function () {
    setTimeout(() => {
      $('.rcn_vp-table-listing-body').fadeOut();
      $('.rcn_vp-table-listing-head .fa-angle-up').hide();
      $('.rcn_vp-table-listing-body-action-spinner').hide();

      $('.rcn_vp-table-listing').each(function () {
        $(this)
          .find('.rcn_vp-table-listing-head')
          .on('click', function () {
            $(this).siblings('.rcn_vp-table-listing-body').toggle();
            $(this).find('.fa-angle-down').toggle();
            $(this).find('.fa-angle-up').toggle();
          });
      });
    }, 200);
  });
})(jQuery);
