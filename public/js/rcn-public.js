(function ($) {
  'use strict';

  /**
   * All of the code for public-facing JavaScript source
   * should reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */

  $(function () {
    $('.rcn_vp-table-listing-head').on('click', function() {
      $('.rcn_vp-table-listing-body').toggle();
    })
    
  });
})(jQuery);