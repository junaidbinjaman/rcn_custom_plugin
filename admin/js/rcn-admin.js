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
    foobar($);
  });
})(jQuery);

function foobar($) {
  var btn = $('.rcn-ar-admin-unique-url-generator-form .button-primary');
  var input = $('.rcn-ar-admin-unique-url-generator-form .regular-text');
  var success = $('.rcn-ar-admin-unique-url-generator-notice-success');
  var error = $('.rcn-ar-admin-unique-url-generator-notice-error');
  var warning = $('.rcn-ar-admin-unique-url-generator-notice-warning');

  btn.on('click', function () {
    var value = parseInt(input.val());
    var newElement = $('<p>');
    console.log(value);

    success.hide();
    error.hide();
    warning.hide();

    if (value === 1) {
      newElement.text('Hello, World! This is a success message');
      success.html(newElement);
      success.show();
    }

    if (value === 2) {
      newElement.text('Oops! There is an error in your life!');
      error.html(newElement);
      error.show();
    }

    if (value === 3) {
      newElement.text('This is a final warning!');
      warning.html(newElement);
      warning.show();
    }
  });
}

function rcn_arAjaxHandler($) {
	$.ajax({
		method: 'POST',
		url: '',
		success: function() {
			console.log('success')
		},
		error: function() {
			console.log('error');
		}
	})
}
