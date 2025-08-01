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
        rconSelectedUnregisteredAttendeeReminderToRegister($);
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
 * The function handles the popup visibility toggle.
 *
 * The popup contains a form that send available attendee registration
 * email reminder to the billing email address of the selected orders.
 *
 * @param {jQuery} $ The jQuery reference.
 */
function rconSelectedUnregisteredAttendeeReminderToRegister($) {
    
    // Show the popup
    $('.rcon-unregistered-attendee-reminder').on('click', function () {
        $(this).find('.dashicons-edit').fadeOut('fast');
        $('.modal').fadeIn('fast');
        $('.overlay').fadeIn('fast');
    });

    // Hide the popup
    $('.rcon-close-modal-btn').on('click', function () {
        $('.rcon-unregistered-attendee-reminder .dashicons-edit').fadeIn();
        $('.modal').fadeOut('fast');
        $('.overlay').fadeOut('fast');
    });
}

/**
 * Retrieves the form values for unregistered attendee reminders and sends them 
 * to a PHP function via AJAX to dispatch the email reminder.
 *
 * @param {jQuery} $ - The jQuery reference.
 */
function rconUnregisteredAttendeeReminder($) {
    // Sending status notification
    $(
        '.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading'
    ).show();
    $(
        '.rcon-unregistered-attendee-reminder-form .notifications .rcn-success'
    ).hide();
    $(
        '.rcon-unregistered-attendee-reminder-form .notifications .rcn-error'
    ).hide();

    var formType;
    var orderId;
    var emailSubject;
    var emailBody;

    $('.rcon-unregistered-attendee-reminder-form').each(function () {
        formType = $(this).find('input[name="form-type"]').val();
        orderId = $(this).find('input[name="order-id"]').val();
        emailSubject = $(this).find('input[name="email-subject"]').val();
        emailBody = $(this).find('textarea[name="email-body"]').val();
    });

    var data = {
        formType,
        orderIds: [(orderId ? orderId : [])],
        emailSubject,
        emailBody,
    };

    if (formType === 'selected') {
        $(
            '.r-con-dashboard-unregistered-attendee-list .checkbox-wrapper input:checked'
        ).each(function () {
            var orderId = $(this).attr('data-order-id');

            data.orderIds.push(orderId);
        });
    }

    $.ajax({
        type: 'POST',
        url: wp_ajax.url,
        data: {
            action: 'handle_unregistered_attendee_reminder_email_sending',
            nonce: wp_ajax.nonce,
            data: data,
        },
        success: function (res) {
            // Sending status notification
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading'
            ).hide();
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-success'
            ).show();
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-error'
            ).hide();
        },
        error: function (xhr, status, error) {
            // Sending status notification
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading'
            ).hide();
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-success'
            ).hide();
            $(
                '.rcon-unregistered-attendee-reminder-form .notifications .rcn-error'
            ).show();
        },
    });
}
