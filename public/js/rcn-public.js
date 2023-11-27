jQuery(function ($) {
  $(document).ready(function () {
    rcn_initializeVendorCheckoutStepStatus();
    $('.rcn-vendor-checkout-next').on('click', () => handleRCNVscNext($));

    $('.rcn-vendor-checkout-prev').on('click', () => rcn_handleRCNVscPrev($));

    rcnChangeVendorCheckoutSteps($);
  });
});

/**
 * rcn_initializeVendorCheckoutStepStatus
 * This function initializes the vendor checkout step status in the browser's local storage.
 */
function rcn_initializeVendorCheckoutStepStatus() {
  // Get step statuses
  const step1 = localStorage.getItem('rcn-vcsc1');
  const step2 = localStorage.getItem('rcn-vcsc2');
  const step3 = localStorage.getItem('rcn-vcsc3');

  // Initialize statuses if not already initialized
  if (step1 === null) {
    localStorage.setItem('rcn-vcsc1', 'in progress');
  }

  if (step2 === null) {
    localStorage.setItem('rcn-vcsc2', 'not started');
  }

  if (step3 === null) {
    localStorage.setItem('rcn-vcsc3', 'not started');
  }
}

/**
 * The function switch from current status screen to previous status screen
 *
 * @param {number} $ jQuery selector.
 */
function rcn_handleRCNVscPrev($) {
  // Get step statuses
  const step1 = localStorage.getItem('rcn-vcsc1');
  const step2 = localStorage.getItem('rcn-vcsc2');
  const step3 = localStorage.getItem('rcn-vcsc3');

  // Updating step's status in browser's local storage
  if (step1 === 'in progress') {
    /**
     * Perform an action when users are on step 1.
     *
     * Since there are no previous steps, the "Prev" button is hidden on this step.
     */
  } else if (step2 === 'in progress') {
    /**
     * When the user interacts with the "prev" button on screen two:
     *
     * - Step 1's status changes from "completed" to "in progress."
     * - Step 2 remains in progress.
     * - The visual indication of the step circle background color at the top of the screen
     *   remains unchanged, indicating that both step 1 and step 2 are currently in progress.
     */
    localStorage.setItem('rcn-vcsc1', 'in progress');
  } else if (step3 === 'in progress') {
    /**
     * When the user interacts with the "prev" button on screen three:
     *
     * - Step 2's status changes from "completed" to "in progress."
     * - Step 3 remains in progress.
     * - The visual indication of the step circle background color at the top of the screen
     *   remains unchanged, signifying that both step 2 and step 3 are currently in progress.
     */
    localStorage.setItem('rcn-vcsc3', 'in progress');
    localStorage.setItem('rcn-vcsc2', 'in progress');
  }

  rcnChangeVendorCheckoutSteps($);
}

function handleRCNVscNext($) {
  const step1 = localStorage.getItem('rcn-vcsc1');
  const step2 = localStorage.getItem('rcn-vcsc2');
  const step3 = localStorage.getItem('rcn-vcsc3');

  if (step1 === 'in progress') {
    console.log('Hello, World');
    localStorage.setItem('rcn-vcsc1', 'completed');
    localStorage.setItem('rcn-vcsc2', 'in progress');
  } else if (step2 === 'in progress') {
    localStorage.setItem('rcn-vcsc2', 'completed');
    localStorage.setItem('rcn-vcsc3', 'in progress');
  } else if (step3 === 'in progress') {
    localStorage.setItem('rcn-vcsc3', 'completed');
    location.replace('/checkout');
  }

  rcnChangeVendorCheckoutSteps($);
}

function rcnChangeVendorCheckoutSteps($) {
  const completeColor = '#0040E0';
  const incompleteColor = '#515151';

  const step1 = localStorage.getItem('rcn-vcsc1');
  const step2 = localStorage.getItem('rcn-vcsc2');
  const step3 = localStorage.getItem('rcn-vcsc3');

  $('.rcn-vcs1').fadeOut('fast');
  $('.rcn-vcs2').fadeOut('fast');
  $('.rcn-vcs3').fadeOut('fast');
  $('.rcn-vendor-checkout-prev').fadeIn();
  $('.rcn-vendor-checkout-next').fadeIn();

  if (step1 === 'in progress') {
    $('.rcn-vendor-checkout-prev').fadeOut();
  }

  if (step2 === 'completed' && step3 === 'in progress') {
    $('.rcn-vendor-checkout-next').fadeOut();
  }

  if (step1 === 'in progress' || step1 === 'completed') {
    $('.rcn-vcsc1').css('background', completeColor);
  } else {
    $('.rcn-vcsc1').css('background', incompleteColor);
  }

  if (step2 === 'in progress' || step2 === 'completed') {
    $('.rcn-vcsc2 .elementor-divider__text').css('background', completeColor);
  } else {
    $('.rcn-vcsc2 .elementor-divider__text').css('background', incompleteColor);
  }

  if (step3 === 'in progress' || step3 === 'completed') {
    $('.rcn-vcsc3').css('background', completeColor);
  } else {
    $('.rcn-vcsc3').css('background', incompleteColor);
  }

  if (step1 === 'in progress') {
    $('.rcn-vcs1').fadeIn('slow');
  } else if (step2 === 'in progress') {
    $('.rcn-vcs2').fadeIn('slow');
  } else if (step3 === 'in progress') {
    $('.rcn-vcs3').fadeIn('slow');
  }
}
