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

function rconSelectedUnregisteredAttendeeReminderToRegister($) {
    var selectedOrderData = [];

    $('.rcon-unregistered-attendee-reminder').on('click', function () {
        selectedOrderData.length = 0;

        $(
            '.r-con-dashboard-unregistered-attendee-list .checkbox-wrapper input:checked'
        ).each(function () {
            var orderId = $(this).attr('data-order-id');
            var email = $(this).attr('data-billing-email');

            selectedOrderData.push({orderId, email});
        });

        const selectedOrders = selectedOrderData.length;
        let emailCountMessage =
            'The reminder will be sent to <strong>' +
            selectedOrders +
            '</strong> email';

        if (selectedOrders === 0) {
            emailCountMessage =
                '<span style="color: red;"><strong>NO ORDER SELECTED.</strong> Please select at least 1 order to continue</span>';
            $('.rcon-selected-order-reminder-email-form a.button').attr(
                'disabled',
                true
            );
        }

        if (selectedOrders !== 0) {
            $('.rcon-selected-order-reminder-email-form a.button').attr(
                'disabled',
                false
            );
        }

        $('.rcon-selected-order-reminder-email-instruction .email-count').html(
            emailCountMessage
        );

        $(this).find('.dashicons-edit').fadeOut('fast');
        $('.modal').fadeIn('fast');
        $('.overlay').fadeIn('fast');
    });

    $('.rcon-close-modal-btn').on('click', function () {
        $('.rcon-unregistered-attendee-reminder .dashicons-edit').fadeIn();
        $('.modal').fadeOut('fast');
        $('.overlay').fadeOut('fast');
    });

    $('.rcon-selected-order-reminder-email-form a.button').on(
        'click',
        function () {
            $(
                '.rcon-selected-order-reminder-email-form .notification .sent'
            ).fadeOut('fast');
            $(
                '.rcon-selected-order-reminder-email-form .notification .sending'
            ).fadeIn('slow');

            var subject = $(
                '.rcon-selected-order-reminder-email-form input'
            ).val();
            var emailBody = $(
                '.rcon-selected-order-reminder-email-form textarea'
            ).val();

            console.log(
                'Subject: ',
                subject,
                'Email: ',
                emailBody,
                selectedOrderData
            );
            sendReminderToSelectedOrders(
                $,
                subject,
                emailBody,
                selectedOrderData
            );
        }
    );
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

function sendReminderToSelectedOrders(
    $,
    subject,
    emailBody,
    selectedOrderData
) {
    $.ajax({
        type: 'POST',
        url: wp_ajax.url,
        data: {
            action: 'attendee_reg_reminder',
            nonce: wp_ajax.nonce,
            subject: subject,
            emailBody: emailBody,
            selectedOrderData: selectedOrderData,
        },
        success: function (res) {
            console.log(res);
            $(
                '.rcon-selected-order-reminder-email-form .notification .sending'
            ).fadeOut('fast');
            $(
                '.rcon-selected-order-reminder-email-form .notification .sent'
            ).fadeIn('slow');
        },
        error: function (xhr, status, status) {
            console.log(status);
        },
    });
}

function rconUnregisteredAttendeeReminder($) {
    $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading').show();
    $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-success').hide();
    $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-error').hide();

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
        orderIds: [orderId],
        emailSubject,
        emailBody
    }

    $.ajax({
        type: 'POST',
        url: wp_ajax.url,
        data: {
            action: 'unregistered_attendee_reminder',
            nonce: wp_ajax.nonce,
            data: data
        },
        success: function (res) {
            console.log(res);
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading').hide();
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-success').show();
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-error').hide();
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-loading').hide();
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-success').hide();
            $('.rcon-unregistered-attendee-reminder-form .notifications .rcn-error').show();
        },
    });
}
