(function ($) {
  'use strict';

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(function () {
    setupEventHandlers($);
  });
})(jQuery);

function setupEventHandlers($) {
  var btn = $('.rcn-ar-admin-unique-url-generator-form .button-primary');
  var input = $('.rcn-ar-admin-unique-url-generator-form .regular-text');

  // Removing previous event handlers to avoid duplicate bindings
  btn.off('click').on('click', function () {
    var orderId = parseInt(input.val(), 10);

    if (isNaN(orderId)) {
      console.error('Invalid input: not a number');
      return;
    }

    rcn_arAjaxHandler($, orderId);
    input.val('')
  });
}

function rcn_arAjaxHandler($, orderId) {
  $.ajax({
    type: 'POST',
    url: wp_ajax.url,
    data: {
      action: 'my_post_like',
      nonce: wp_ajax.nonce,
      order_id: orderId,
    },
    success: function (response) {
      response = JSON.parse(response);
      console.log('Success:', response);
      rcn_arNotificationHandler($, response.status, response.message);
    },
    error: function (xhr, status, error) {
      rcn_arNotificationHandler($, false, error);
    },
  });
}

function rcn_arNotificationHandler($, status, message) {
  var success = $('.rcn-ar-admin-unique-url-generator-notice-success');
  var error = $('.rcn-ar-admin-unique-url-generator-notice-error');
  var warning = $('.rcn-ar-admin-unique-url-generator-notice-warning');
  var notificationParagraph = $('<p>');

  success.hide();
  error.hide();
  warning.hide();

  if (status === true) {
    notificationParagraph.text(message);
    success.empty().html(notificationParagraph).show();
    return;
  }

  if (status === false) {
    notificationParagraph.text(message);
    error.empty().html(notificationParagraph).show();
    return;
  }

  if (status === 'warning') {
    notificationParagraph.text(message);
    warning.empty().html(notificationParagraph).show();
    return;
  }
}
