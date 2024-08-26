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
        rcn_arURLGeneratorBtnHandle($);
    });
})(jQuery);

/**
 * The function handle the click event of attendee registration page url generator button
 *
 * @param {jQuery} $ The jQuery reference.
 */
function rcn_arURLGeneratorBtnHandle($) {
    var btn = $('.rcn-ar-admin-unique-url-generator-form .button-primary');
    var input = $('.rcn-ar-admin-unique-url-generator-form .regular-text');
    var loader = $('.rcn-ar-admin-unique-url-generator-form .rcn-ar-loader');

    btn.on('click', function () {
        var orderId = parseInt(input.val(), 10);

        if (isNaN(orderId)) {
            console.error('Invalid input: not a number');
            return;
        }

        loader.show();

        rcn_arSlotRegistrationHandler($, orderId, loader);
    });
}

/**
 * The function call the backend register_attendee_slots function by passing necessary data
 * and displays relevant message on the dashboard based on response status.
 *
 * @param {jQuery} $ The jQuery reference
 * @param {int} orderId The order Ideally
 * @param {string} loader The loader message
 */
function rcn_arSlotRegistrationHandler($, orderId, loader) {
    $.ajax({
        type: 'POST',
        url: wp_ajax.url,
        data: {
            action: 'ar_admin_url_generator',
            nonce: wp_ajax.nonce,
            order_id: orderId,
        },
        success: function (response) {
            response = JSON.parse(response);

            rcn_arNotificationHandler($, response.status, response.message);
        },
        error: function (xhr, status, error) {
            rcn_arNotificationHandler($, false, error);
        },
        complete: function () {
            loader.hide();
            $('.rcn-ar-admin-unique-url-generator-form .regular-text').val('');
        },
    });
}

/**
 * The function handler the notification for admin attendee registration
 *
 * @param {jQuery} $ jQuery reference
 * @param {boolean | string} status The response status
 * @param {string} message The message to display in the notification body
 * @returns {void}
 */
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

/**
 * The function calls a backend function to send reminder to all the emails of the order
 * where there are still available slots
 */
function rconAttendeeRegisterReminderHandler() {
    let $ = jQuery;
    jQuery('.rcon-dashboard-notification').show();
    jQuery('.rcon-dashboard-notification').css('display', 'flex');

    var emailSubject = jQuery(
        '.rcon-dashboard-unregistered-attendee-email-reminder #email-subject'
    ).val();
    var emailBody = jQuery(
        '.rcon-dashboard-unregistered-attendee-email-reminder #email-body'
    ).val();

    $.ajax({
        type: 'post',
        url: wp_ajax.url,
        data: {
            action: 'foobar',
            nonce: wp_ajax.nonce,
            emailSubject,
            emailBody,
        },
        success: function (response) {
            console.log(response);
            jQuery('.rcon-dashboard-notification').find('img').hide();
            jQuery('.rcon-dashboard-notification').html(
                '<h1 style="color: green"> All reminders are sent successfully</h1>'
            );
        },
        error: function (xhr, status, error) {
            console.log(error);
            jQuery('.rcon-dashboard-notification').html(
                '<h1 style="color: red">Something went wrong</h1>'
            );
        },
    });
}

function sendReminderToSelectedOrders() {
    var $ = jQuery;
    var selectedOrderData = [];

    $(
        '.r-con-dashboard-unregistered-attendee-list .checkbox-wrapper input:checked'
    ).each(function () {
        var orderId = $(this).attr('data-order-id');
        var email = $(this).attr('data-billing-email');

        selectedOrderData.push({orderId, email});
    });

    console.log(selectedOrderData);

    $.ajax({
        type: 'POST',
        url: wp_ajax.url,
        data: {
            action: 'attendee_reg_reminder',
            nonce: wp_ajax.nonce,
            selectedOrderData: selectedOrderData,
        },
        success: function (res) {
            console.log(res);
        },
        error: function (xhr, status, status) {
            console.log(status);
        },
    });
}
