(function ($) {
  'use strict';

  /**
   * All of the code for attendee registration public-facing JavaScript source
   * should reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */

  $(function () {
    rcn_ar_initializeAttendeeToggle($);
    rcn_ar_registrationSectionToggleHandler($)
  });
})(jQuery);

/**
 * Initializes click event handlers for toggling the display of additional details
 * for each registered attendee in a list.
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_ar_initializeAttendeeToggle($) {
  $('.rcn-ar-registered-attendee-listings .header').on('click', function () {
    $(this).find('.up').toggle();
    $(this).find('.down').toggle();

    $(this).siblings().toggle();
  });
}

function rcn_ar_registrationSectionToggleHandler($) {
  var ticketTypes = ['regular', 'virtual', 'vip'];

  for (let i = 0; i < ticketTypes.length; i++) {
    const type = ticketTypes[i];
    togglerHelper(type)
  }

  function togglerHelper(type) {
    $(`.rcn-ar-register-${type}-attendee`).on('click', function () {
      $('.rcn-ar-registration-section').hide();
  
      $(`.rcn-ar-${type}-attendee`).show();
    });
  }
}
