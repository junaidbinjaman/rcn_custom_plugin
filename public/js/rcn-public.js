(function ($) {
  'use strict';

  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   */
  $(function () {
    // Global variables for vendor checkout steps
    const rcn_vcs = ['rcn-vcs0', 'rcn-vcs1', 'rcn-vcs2'];

    // Initialize vendor checkout step status in the browser's local storage
    rcn_initializeVendorCheckoutStepStatus(rcn_vcs);

    // Handle the "Prev" button click on vendor package checkout steps
    $('.rcn-vendor-checkout-prev').on('click', () =>
      rcn_handleRCNVscPrev($, rcn_vcs)
    );

    // Handle the "Next" button click on vendor package checkout steps
    $('.rcn-vendor-checkout-next').on('click', () =>
      rcn_handleRCNVscNext($, rcn_vcs)
    );

    // Switch vendor package checkout steps on the screen and update the step indicator circle at the top
    rcn_changeVendorCheckoutSteps($, rcn_vcs);

    // The ticket prices for vendors' attendees will be multiplied based on the quantity selected by vendor
    const rcn_atfv_qsapces = $('#form-field-name');
    $('#form-field-name').on('change', () =>
      rcn_calculate_attendee_ticket_prices_for_vendor($, rcn_atfv_qsapces)
    );
  });
})(jQuery);

/**
 * Initialize vendor checkout step status in the browser's local storage.
 *
 * @param {string[]} rcn_vcs - Array of vendor checkout step identifiers.
 */
function rcn_initializeVendorCheckoutStepStatus(rcn_vcs) {
  /**
   * Iterate through the 'rcn_vcs' array to check if the status and active step
   * are already stored in the browser's local storage.
   * If they are not found, set up the initial values and set the active step to 1.
   */
  for (let i = 0; i < rcn_vcs.length; i++) {
    const element = rcn_vcs[i];

    // Get step statuses
    const step = localStorage.getItem(element);

    // Set the initial status for the first step to 'in progress', others to 'not started'
    if (step === null && i === 0) {
      localStorage.setItem(element, 'in progress');
    } else if (step === null) {
      localStorage.setItem(element, 'not started');
    }
  }

  // Initialize the active step if not set
  const activeStep = localStorage.getItem('activeStep');
  if (activeStep === null) {
    localStorage.setItem('activeStep', 0);
  }
}

/**
 * Handle moving to the previous vendor checkout step.
 *
 * @param {number} $ - jQuery selector.
 * @param {string[]} rcn_vcs - Array of vendor checkout step identifiers.
 */
function rcn_handleRCNVscPrev($, rcn_vcs) {
  // Get active step number
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);

  // Decrease active step number by 1
  localStorage.setItem('activeStep', activeStep - 1);

  // Move the status of the step before the current one to "in progress"
  if (activeStep !== 0) {
    localStorage.setItem(rcn_vcs[activeStep - 1], 'in progress');
  }

  /**
   * Switch vendor package checkout steps on the screen
   * and update the step indicator circle at the top
   */
  rcn_changeVendorCheckoutSteps($, rcn_vcs);
}

/**
 * Handle moving to the next vendor checkout step.
 *
 * @param {number} $ - jQuery selector.
 * @param {string[]} rcn_vcs - Array of vendor checkout step identifiers.
 */
function rcn_handleRCNVscNext($, rcn_vcs) {
  // Get active step number
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);

  // Total number of steps
  const totalSteps = rcn_vcs.length - 1;

  // Increase active step number by 1
  localStorage.setItem('activeStep', activeStep + 1);

  // Set the 'completed' status for the current step and 'in progress' for the next step
  if (totalSteps !== activeStep) {
    localStorage.setItem(rcn_vcs[activeStep], 'completed');
    localStorage.setItem(rcn_vcs[activeStep + 1], 'in progress');
  }

  if (activeStep === totalSteps) {
    localStorage.setItem(rcn_vcs[activeStep], 'in progress');
    console.log('Sorry! there are no steps up next');
  }

  /**
   * Switch vendor package checkout steps on the screen
   * and update the step indicator circle at the top
   */
  rcn_changeVendorCheckoutSteps($, rcn_vcs);
}

/**
 * Switch vendor package checkout steps on the screen and update the step indicator circle at the top.
 * This function reads the vendor checkout step status set by rcn_initializeVendorCheckoutStepStatus.
 *
 * @param {string} $ - jQuery selector.
 * @param {string[]} rcn_vcs - Array of vendor checkout step identifiers.
 */
function rcn_changeVendorCheckoutSteps($, rcn_vcs) {
  // Initialize variables
  const completeColor = '#0040E0'; // The color of the step circle at the top indicates whether the step is in a "completed" or "in progress" state
  const incompleteColor = '#515151'; // The color of the step circle at the top signifies that the step is in a "not completed" status
  const totalSteps = rcn_vcs.length - 1;
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);

  /**
   * Iterate through the array and initially hide all the steps.
   * If the active step number is equal to the iteration count (i),
   * display the corresponding step.
   */
  for (let i = 0; i < rcn_vcs.length; i++) {
    let element = rcn_vcs[i];
    element = $('.' + element);

    // Hide all the steps
    element.hide();

    // Show the active step
    if (activeStep === i) {
      element.fadeIn('fast');
    }
  }

  // Display "Prev" and "Next" buttons by default
  $('.rcn-vendor-checkout-prev').fadeIn();
  $('.rcn-vendor-checkout-next').fadeIn();

  // Hide the "Prev" button on Step 1, as there is no previous step
  if (activeStep === 0) {
    $('.rcn-vendor-checkout-prev').hide();
  }

  // Hide the "Next" button when a vendor is on the last step since there are no more steps after it
  if (activeStep === totalSteps) {
    $('.rcn-vendor-checkout-next').hide();
  }

  // Update the step indicator circles' appearance based on the step status
  for (let i = 0; i < rcn_vcs.length; i++) {
    const element = rcn_vcs[i];

    // Get step status
    const step = localStorage.getItem(element);

    // Apply rounded borders to the step indicator numbers
    $('.rcn-vcsc' + i).css('border-radius', '100%');

    // Update the step indicator circle's background
    if (step === 'in progress' || step === 'completed') {
      $('.rcn-vcsc' + i).css('background', completeColor);
    } else {
      $('.rcn-vcsc' + i).css('background', incompleteColor);
    }
  }
}

/**
 * Multiply the price of an "attendee ticket for vendor" by the quantity of tickets selected by the vendor.
 * Format the price with a dollar sign ($) and comma (,) as a thousands separator
 * And place it in the price area on the box.
 *
 * @param {number} $ jQuery selector
 * @param {jQuery Selector} selectField jQuery code that selects the input field
 */
function rcn_calculate_attendee_ticket_prices_for_vendor($, selectField) {
  // Select the h2 tag that displays the total price
  const totalTicketPrice = $('.rcn_attendee_ticket_price_for_vendor h2');

  // Get ticket quantity selected by vendor and convert it into a number
  let quantity = selectField.val();
  quantity = Number(quantity);

  $('a.product_type_simple.add_to_cart_button.ajax_add_to_cart.elementor-button.elementor-size-sm').data('quantity', 10)
  console.log($('.rcn-atfvqsf-button a').data('quantity'));

  // Format the price and place it in the price area
  totalTicketPrice.text(rcn_formatPrice(quantity * 500));
}

/**
 * Format a numeric price value as a currency string with decimal placeable option,
 * a comma as a thousands separator, and an optional currency symbol.
 *
 * @param {number} price - The numeric price value to be formatted.
 * @returns {string} - The formatted currency string.
 */
function rcn_formatPrice(price) {
  // Convert the price to a string with two decimal places
  if (price === 0) {
    var formattedPrice = price.toFixed(2);
  } else {
    var formattedPrice = price.toFixed(0);
  }

  // Add a comma as a thousands separator
  formattedPrice = formattedPrice.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  // Add any currency symbol or additional formatting as needed
  formattedPrice = '$' + formattedPrice;

  return formattedPrice;
}
