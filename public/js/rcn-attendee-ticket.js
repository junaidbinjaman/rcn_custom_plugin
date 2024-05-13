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
    rcn_ar_registrationSectionToggleHandler($);
    rcn_arPageReloadHandler($);

    const isMobile = window.innerWidth < 1024 ? true : false;
    
    if (isMobile) {
      rcn_arOrderDataToggleHandler($);
    }

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

/**
 * The function toggles the attendee registration form based on attendee type
 * @param {jQuery} $ The jQuery reference.
 */
function rcn_arRegistrationSectionToggleHandler($) {
  var ticketTypes = ['conference', 'virtual', 'vip'];

  for (let i = 0; i < ticketTypes.length; i++) {
    const type = ticketTypes[i];
    togglerHelper(type);
  }

  function togglerHelper(type) {
    $(`.rcn-ar-register-${type}-attendee`).on('click', function () {
      $('.rcn-ar-registration-section').hide();

      $(`.rcn-ar-${type}-attendee`).show();
    });
  }
}

/**
 * The function reloads the page after completing the attendee registration
 * @param {jQuery} $ The jQuery reference
 */
function rcn_arPageReloadHandler($) {
  $(document).on('submit_success', '.elementor-form', function (event, res) {
    location.reload();
  });
}

/**
 * The function toggle order data section and attendee registration forms on mobile
 * @param {jQuery} $ The jQuery reference
 */
function rcn_arOrderDataToggleHandler($) {
  $('.rcn-ar-register-btn').on('click', function (event) {
    toggleHandler($);
  });

  $('.rcn-ar-back-to-order-data-btn a').on('click', function (event) {
    toggleHandler($);
    $('.rcn-ar-registration-section').hide();
  });

  function toggleHandler($) {
    $('.rcn-ar-order-data, .rcn-ar-back-to-order-data-btn').toggle();
  }
}
