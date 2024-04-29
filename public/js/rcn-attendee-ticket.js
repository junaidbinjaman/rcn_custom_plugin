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
    rcn_ar_form_toggle_handler($);
  });
})(jQuery);

function rcn_ar_registered_attendee_toggle_handler($) {
  $('.rcn-ar-registered-attendee-listings .header').on('click', function () {
    $(this).find('.up').toggle();
    $(this).find('.down').toggle();

    $(this).siblings().toggle();
  });
}

function rcn_ar_form_toggle_handler($) {
  $('.rcn-ar-display-form-btn').on('click', foobar);

  $('.rcn-ar-hide-form-btn').on('click', foobar);

  function foobar() {
    $('.rcn-ar-display-form-btn').toggle();
    $('.rcn-ar-hide-form-btn').toggle();
    $('.rcn-ar-form').toggle();
  }
}
