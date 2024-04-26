(function ($) {
    'use strict';
  
    /**
     * All of the code for public-facing JavaScript source
     * should reside in this file.
     *
     * This enables to define handlers, for when the DOM is ready:
     */
  
    $(function () {
        rcn_ar_registered_attendee_toggle_handler($);
    });
  })(jQuery);

  function rcn_ar_registered_attendee_toggle_handler($) {
    $('.rcn-ar-registered-attendee-listings .header').on('click', function() {
        $(this).find('.up').toggle();
        $(this).find('.down').toggle();
        
        $(this).siblings().toggle();
    })
  }
  