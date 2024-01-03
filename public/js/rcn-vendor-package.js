(function ($) {
  'use strict';

  /**
   * All of the code for vendor-package JavaScript source
   * reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */
  $(function () {
    // Sources for vendor cart
    const vpciEvenLi = $('#rcn-vpci ul > li:even');
    vpciEvenLi.css('background', '#F2F4F8');

    // Sources for vendor table listing box
    const vtsb = $('.rcn-vtsb')[0];
    new SimpleBar(vtsb, {autoHide: false});

    // custom according boxes used for package addons
    rcn_vpcb($);

    rcn_vpPrevNextButtonHandler($);

    $('.rcn_vps-prev-button').each(function () {
      $(this).on('click', function () {
        rcn_vpPrevNextButtonHandler($, 'prev');
      });
    });

    $('.rcn_vps-next-button').each(function () {
      $(this).on('click', function () {
        rcn_vpPrevNextButtonHandler($, 'next');
      });
    });
  });
})(jQuery);

/**
 * The function contains all the jQuery codes that powers the custom according box
 * used for vendor package add-ons.
 *
 * @param {jQuery selector} $ - The jQuery selector to identify the target element.
 */
function rcn_vpcb($) {
  $('.rcn-vpaa-angle-down').show();
  $('.rcn-vpaa-angle-up').hide();
  $('.rcn-vpaa-body').hide();
  $('.rcn-vpaa-oosi').hide();

  $('.rcn-vpaa-wrapper').each(function () {
    var wrapper = $(this);

    var result = wrapper.find('.rcn-vpaa-oosi').hasClass('rcn-oos');

    if (result) {
      wrapper.find('.rcn-vpaa-oosi').show();
      wrapper.find('.rc-vpaa-quantity').hide();
    }

    wrapper.on('click', '.rcn-vpaa-header', function (event) {
      event.stopPropagation();

      $(this).toggleClass('rcn-vpaa-header-active', 1000);

      wrapper.find('.rcn-vpaa-angle-down').toggle();
      wrapper.find('.rcn-vpaa-angle-up').toggle();
      wrapper.find('.rcn-vpaa-body').toggle();
    });
  });
}

/**
 * Initializes or updates the active step number based on the specified flag.
 *
 * If the active step is already initialized, this function increments or decrements
 * the active step number, depending on the provided flag.
 *
 * @param {jQuery selector} $ - The jQuery selector to identify the target element.
 * @param {string} flag - A string flag indicating whether to navigate to the previous or next step.
 *                        Use 'prev' for the previous step and 'next' for the next step.
 * @returns {void} - The return type is 'void' as the purpose is to update the active step, not to return a value.
 */

function rcn_vpPrevNextButtonHandler($, flag = null) {
  const totalSteps = 2;
  if (!localStorage.getItem('rcn-vp-active-step')) {
    localStorage.setItem('rcn-vp-active-step', 0);

    rcn_vpStepSwitchHandler($, totalSteps);
    return;
  }

  if (flag === 'next') {
    let activeStep = localStorage.getItem('rcn-vp-active-step');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep + 1;
    localStorage.setItem('rcn-vp-active-step', nextActiveStep);

    rcn_vpStepSwitchHandler($, totalSteps);
    return;
  }

  if (flag === 'prev') {
    let activeStep = localStorage.getItem('rcn-vp-active-step');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep - 1;
    localStorage.setItem('rcn-vp-active-step', nextActiveStep);

    rcn_vpStepSwitchHandler($, totalSteps);
    return;
  }

  rcn_vpStepSwitchHandler($, totalSteps);
}

function rcn_vpStepSwitchHandler($, totalSteps) {
  let activeStep = localStorage.getItem('rcn-vp-active-step');
  let is_product_in_cart;

  const ajaxurl = wp_ajax_object.ajax_url;
  const productID = 1427;
  const stepClasses = [];
  const stepIndicatorClasses = [];

  activeStep = Number(activeStep);

  if (activeStep < 0) {
    return {
      status: false,
      message: 'Invalid active step',
    };
  }

  if (activeStep > totalSteps) {
    return {
      status: false,
      message: 'Invalid active step',
    };
  }

  // Sources for prev, next and checkout buttons
  $('.rcn_vps-prev-button').hide();
  $('.rcn_vps-next-button').hide();
  $('.rcn_vps-checkout-button').hide();

  if (activeStep > 0) {
    $('.rcn_vps-prev-button').show();
  }

  if (activeStep !== totalSteps) {
    $('.rcn_vps-next-button').show();
  }

  if (totalSteps === activeStep) {
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'is_product_in_cart',
        product_id: productID,
      },
      success: function (response) {
        var result = JSON.parse(response);
        console.log(result.message);

        if (result.status) {
          is_product_in_cart = true;
          $('.rcn_vps-checkout-button').show();
        } else {
          console.log('No table is in cart');
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
      },
    });
  }

  // Sources for step hide and show
  for (let i = 0; i <= totalSteps; i++) {
    const stepClass = '.rcn-vp-step-' + [i];
    stepClasses.push(stepClass);
  }

  for (let i = 0; i < stepClasses.length; i++) {
    const stepClass = stepClasses[i];
    $(stepClass).hide();
  }

  if (activeStep === 0) {
    $(stepClasses[0]).show();
    localStorage.setItem('rcn-vp-step-0', true);
  }

  if (activeStep > 0) {
    let prevStepStatus = localStorage.getItem('rcn-vp-step-' + (activeStep - 1));

    if (prevStepStatus === null) {
      console.error('Step ' + (activeStep - 1) + ' is required');
      return;
    }

    localStorage.setItem('rcn-vp-step-' + activeStep, true);
    $(stepClasses[activeStep]).show();
  }

  // Sources for step indicator circle
  for (let i = 0; i <= totalSteps; i++) {
    const IndicatorClass = '.rcn-vp-step-' + i + '-indicator h2';
    stepIndicatorClasses.push(IndicatorClass);
  }

  const activeStepIndicators = stepIndicatorClasses.splice(0, activeStep + 1);
  const activeStepIndicatorsStyles = {
    backgroundColor: '#0040E0',
    cursor: 'pointer',
  };

  for (let i = 0; i <= activeStepIndicators.length; i++) {
    const activeIndicator = activeStepIndicators[i];
    $(activeIndicator).css(activeStepIndicatorsStyles);

    $(activeIndicator).on('click', function () {
      localStorage.setItem('rcn-vp-active-step', i);
      rcn_vpStepSwitchHandler($, totalSteps);
    });
  }
}
