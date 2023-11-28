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
   *
   */
  $(function () {
    // Global variables for vendor checkout steps
    const rcn_vcs = ['rcn-vcs1', 'rcn-vcs2', 'rcn-vcs3'];

    // This function initializes the vendor checkout step status in the browser's local storage
    rcn_initializeVendorCheckoutStepStatus(rcn_vcs);

    // Handle the "Prev" button click on vendor package checkout steps
    $('.rcn-vendor-checkout-prev').on('click', () =>
      rcn_handleRCNVscPrev($, rcn_vcs)
    );

    // Handle the "Next" button click on vendor package checkout steps
    $('.rcn-vendor-checkout-next').on('click', () =>
      rcn_handleRCNVscNext($, rcn_vcs)
    );

    // Switching vendor package checkout steps on the screen and updating the step indicator circle at the top
    rcn_changeVendorCheckoutSteps($, rcn_vcs);
  });
})(jQuery);

/**
 * rcn_initializeVendorCheckoutStepStatus
 * This function initializes the vendor checkout step status in the browser's local storage.
 */
function rcn_initializeVendorCheckoutStepStatus(rcn_vcs) {
  // Get step statuses
  for (let i = 0; i < rcn_vcs.length; i++) {
    const element = rcn_vcs[i];
    const step = localStorage.getItem(element);
    if (step === null && i === 0) {
      localStorage.setItem(element, 'in progress');
    } else if (step === null) {
      localStorage.setItem(element, 'not started');
    }
  }

  const activeStep = localStorage.getItem('activeStep');

  if (activeStep === null) {
    localStorage.setItem('activeStep', 0);
  }
}

/**
 * The function switch from current status screen to previous status screen
 *
 * @param {number} $ jQuery selector.
 */
function rcn_handleRCNVscPrev($, rcn_vcs) {
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);
  localStorage.setItem('activeStep', activeStep - 1);

  for (let i = 0; i < rcn_vcs.length; i++) {
    const element = rcn_vcs[i];
    const step = localStorage.getItem(element);

    if (step === 'in progress' && i !== 0) {
      localStorage.setItem(rcn_vcs[i - 1], 'in progress');
    }
  }

  /**
   * Switch vendor package checkout steps on the screen
   * and update the step indicator circle at the top.
   */
  rcn_changeVendorCheckoutSteps($, rcn_vcs);
}

/**
 * The function switch from current status screen to next status screen
 *
 * @param {number} $ jQuery selector.
 */
function rcn_handleRCNVscNext($, rcn_vcs) {
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);
  localStorage.setItem('activeStep', activeStep + 1);

  console.log(activeStep);

  for (let i = 0; i < rcn_vcs.length; i++) {
    if (i === activeStep) {
      localStorage.setItem(rcn_vcs[i], 'completed');
      localStorage.setItem(rcn_vcs[i + 1], 'in progress');
    }
  }

  /**
   * Switch vendor package checkout steps on the screen
   * and update the step indicator circle at the top.
   */
  rcn_changeVendorCheckoutSteps($, rcn_vcs);
}

/**
 * rcn_changeVendorCheckoutSteps
 *
 * This function is responsible for switching vendor package checkout steps on the screen
 * and updating the step indicator circle at the top. It reads the vendor checkout step
 * status set by the rcn_initializeVendorCheckoutStepStatus function.
 *
 * The function performs two key actions:
 * 1. Changes the background color of the step indicator at the top to reflect the current step.
 * 2. Manages the visibility of screens, hiding the current one and displaying the next or
 *    previous one based on the step status.
 *
 * For a more detailed understanding of how this function works, please refer to its body.
 *
 * @param {string} $ - jQuery selector.
 */
function rcn_changeVendorCheckoutSteps($, rcn_vcs) {
  // Initialize variables
  const completeColor = '#0040E0'; // Color for completed steps
  const incompleteColor = '#515151'; // Color for incomplete steps

  const totalSteps = rcn_vcs.length - 1;
  let activeStep = localStorage.getItem('activeStep');
  activeStep = Number(activeStep);

  // Get step statuses from local storage
  const step1 = localStorage.getItem('rcn-vcs1');
  const step2 = localStorage.getItem('rcn-vcs2');
  const step3 = localStorage.getItem('rcn-vcs3');

  // Hide all the steps by default
  for (let i = 0; i < rcn_vcs.length; i++) {
    let element = rcn_vcs[i];
    element = $('.' + element);

    element.hide();

    if (activeStep === i) {
      element.fadeIn('fast');
    }
  }

  // Display "Prev" and "Next" buttons by default
  $('.rcn-vendor-checkout-prev').fadeIn();
  $('.rcn-vendor-checkout-next').fadeIn();

  // Hide the "Prev" button on Step 1, as there is no previous step.
  if (activeStep === 0) {
    $('.rcn-vendor-checkout-prev').fadeOut();
  }

  // Hide the "Next" button on Step 3 if Step 2 is completed and Step 3 is in progress.
  if (activeStep === totalSteps) {
    $('.rcn-vendor-checkout-next').fadeOut();
  }

  for (let i = 0; i < rcn_vcs.length; i++) {
    const element = rcn_vcs[i];
    const step = localStorage.getItem(element);

    $('.rcn-vcsc' + i).css('border-radius', '100%');

    if (step === 'in progress' || step === 'completed') {
      $('.rcn-vcsc' + i).css('background', completeColor);
    } else {
      $('.rcn-vcsc' + i).css('background', incompleteColor);
    }
  }
}
