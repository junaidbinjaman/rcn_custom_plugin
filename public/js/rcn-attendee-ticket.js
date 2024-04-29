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
    rcn_ar_setupFormVisibilityToggle($);
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

function rcn_ar_form_toggle_handler($) {
  $('.rcn-ar-display-form-btn').on('click', foobar);

  $('.rcn-ar-hide-form-btn').on('click', foobar);

  function foobar() {
    $('.rcn-ar-display-form-btn').toggle();
    $('.rcn-ar-hide-form-btn').toggle();
    $('.rcn-ar-form').toggle();
  }
}

/**
 * Sets up click event handlers for showing and hiding a form.
 * This function adds functionality to buttons that control the visibility of the form and corresponding toggle buttons.
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_ar_setupFormVisibilityToggle($) {
  $('.rcn-ar-display-form-btn').on('click', toggleFormVisibility);

  $('.rcn-ar-hide-form-btn').on('click', toggleFormVisibility);

  /**
   * Toggles the visibility of the form and the show/hide buttons.
   */
  function toggleFormVisibility() {
    $('.rcn-ar-display-form-btn').toggle();
    $('.rcn-ar-hide-form-btn').toggle();

    $('.rcn-ar-form').toggle();
  }
}
