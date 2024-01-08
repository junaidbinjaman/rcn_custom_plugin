(function ($) {
  'use strict';

  /**
   * Global variables declaration
   */
  const rcn_ajaxurl = wp_ajax_object.ajax_url;
  const productID = 28462; // table wrapper product
  const categoryID = 264;

  /**
   * All of the code for vendor-package JavaScript source
   * reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */
  $(function () {
    // Sources for vendor table listing box
    const vtsb = $('.rcn-vtsb')[0];
    new SimpleBar(vtsb, {autoHide: false});

    // custom according boxes used for package addons
    rcn_vpcb($);

    rcn_vpPrevNextButtonHandler($, null, rcn_ajaxurl);

    $('.rcn_vps-prev-button').each(function () {
      $(this).on('click', function () {
        rcn_vpPrevNextButtonHandler($, 'prev', rcn_ajaxurl);
      });
    });

    $('.rcn_vps-next-button').each(function () {
      $(this).on('click', function () {
        rcn_vpPrevNextButtonHandler($, 'next', rcn_ajaxurl);
      });
    });

    rcn_floorSelectionHandler($, 1, productID, rcn_ajaxurl);

    $('.rcn-vp-floor-1-selector-btn').each(function () {
      $(this).on('click', function () {
        rcn_floorSelectionHandler($, 1, productID, rcn_ajaxurl);
      });
    });

    $('.rcn-vp-floor-2-selector-btn').each(function () {
      $(this).on('click', function () {
        rcn_floorSelectionHandler($, 2, productID, rcn_ajaxurl);
      });
    });

    $('.rcn-vp-floor-3-selector-btn').each(function () {
      $(this).on('click', function () {
        rcn_floorSelectionHandler($, 3, productID, rcn_ajaxurl);
      });
    });

    // prettier-ignore
    const cartItems = [
      {id: 203, name: 'Example product - 1', quantity: 3, price: '$3030'},
      {id: 204, name: 'Example product - 2', quantity: 2, price: '$6500'},
      {id: 205, name: 'Example product - 3', quantity: 4, price: '$2170'},
      {id: 206, name: 'Example product - 4', quantity: 2, price: '$3800'},
      {id: 207, name: 'Example product - 5', quantity: 1, price: '$3190'},
      {id: 208, name: 'Example product - 6', quantity: 4, price: '$00'},
      {id: 209, name: 'Example product - 7', quantity: 4, price: '$2070'},
      {id: 210, name: 'Example product - 8', quantity: 1, price: '$2000'},
      {id: 211, name: 'Example product - 9', quantity: 1, price: '$2000'},
    ];

    const obj = {
      id: 211,
      name: 'Example product - 9',
      quantity: 1,
      price: '$2000',
    };
    // prettier-ignore
    // rcn_vpCartVisualInteraction($, true, cartItems, message, callbackForTesting, 2);
    rcn_addProductToCart($, 'loadCart', obj, null, null, categoryID, rcn_ajaxurl);
  });
})(jQuery);

function rcn_vpPriceFormatter(price) {
  const formattedPrice =
    '$' + price.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  return formattedPrice;
}

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
function rcn_vpPrevNextButtonHandler($, flag = null, rcn_ajaxurl) {
  const totalSteps = 2;
  if (!localStorage.getItem('rcn-vp-active-step')) {
    localStorage.setItem('rcn-vp-active-step', 0);

    rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl);
    return;
  }

  if (flag === 'next') {
    let activeStep = localStorage.getItem('rcn-vp-active-step');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep + 1;
    localStorage.setItem('rcn-vp-active-step', nextActiveStep);

    rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl);
    return;
  }

  if (flag === 'prev') {
    let activeStep = localStorage.getItem('rcn-vp-active-step');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep - 1;
    localStorage.setItem('rcn-vp-active-step', nextActiveStep);

    rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl);
    return;
  }

  rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl);
}

/**
 * Handles the switching of steps in the RCN vendor package checkout.
 *
 * This function is responsible for managing the step transitions within
 * the RCN vendor package checkout. It facilitates the dynamic loading of content
 * for each step, updating UI elements, and handling relevant data.
 *
 * @param {jQuery} $ - The jQuery selector to identify the target element.
 * @param {number} totalSteps - The total number of steps in the vendor package checkout.
 * @param {string} rcn_ajaxurl - The WP AJAX URL for server communication.
 * @returns {void} - The function doesn't return a value.
 */
function rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl) {
  let activeStep = localStorage.getItem('rcn-vp-active-step');

  const stepClasses = [];
  const stepIndicatorClasses = [];

  activeStep = Number(activeStep);

  if (activeStep < 0 || activeStep > totalSteps) {
    return {
      status: false,
      message: 'Invalid active step',
    };
  }

  // Sources for prev, next and checkout buttons
  $('.rcn_vps-prev-button').toggle(activeStep > 0);
  $('.rcn_vps-next-button').toggle(activeStep < totalSteps);
  $('.rcn_vps-checkout-button').toggle(activeStep === totalSteps);

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
    let prevStepStatus = localStorage.getItem(
      'rcn-vp-step-' + (activeStep - 1)
    );

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

  for (let i = 0; i < activeStepIndicators.length; i++) {
    const activeIndicator = activeStepIndicators[i];

    $(activeIndicator).off('click');

    $(activeIndicator).on('click', function () {
      localStorage.setItem('rcn-vp-active-step', i);
      rcn_vpStepSwitchHandler($, totalSteps, rcn_ajaxurl);
    });

    $(activeIndicator).css(activeStepIndicatorsStyles);
  }
}

/**
 * Handles floor selection, updates UI elements, and triggers table listings.
 *
 * Facilitates the selection of different floors within the RCN vendor package checkout,
 * displaying the corresponding floor plan image and passing floor tables to the
 * rcn_vpTableListingHandler function based on the selected floorNumber.
 *
 * @param {jQuery} $ - jQuery selector for the target element.
 * @param {number} floorNumber - Selected floor number (default is 1).
 * @param {number} productID - Product ID representing the vendor table.
 * @param {string} rcn_ajaxurl - AJAX URL for server communication.
 * @returns {void} - No return value.
 */
function rcn_floorSelectionHandler($, floorNumber = 1, productID, rcn_ajaxurl) {
  floorNumber = Number(floorNumber);

  const floorOneTables = [
    {no: 1, id: 28463},
    {no: 2, id: 28464},
    {no: 3, id: 28465},
    {no: 4, id: 28466},
    {no: 5, id: 28467},
  ];
  const floorTwoTables = [
    {no: 6, id: 28468},
    {no: 7, id: 28469},
    {no: 8, id: 28470},
    {no: 9, id: 28471},
    {no: 10, id: 28472},
    {no: 11, id: 28473},
    {no: 12, id: 28474},
    {no: 13, id: 28475},
    {no: 14, id: 28476},
    {no: 15, id: 28477},
    {no: 16, id: 28478},
    {no: 17, id: 28479},
    {no: 18, id: 28480},
    {no: 19, id: 28481},
    {no: 20, id: 28482},
  ];

  const floorThreeTables = [
    {no: 21, id: 28483},
    {no: 22, id: 28484},
    {no: 23, id: 28485},
    {no: 24, id: 28486},
    {no: 25, id: 28487},
    {no: 26, id: 28488},
    {no: 27, id: 28489},
    {no: 28, id: 28490},
    {no: 29, id: 28491},
    {no: 30, id: 28492},
    {no: 31, id: 28493},
    {no: 32, id: 28494},
    {no: 33, id: 28495},
    {no: 34, id: 28496},
    {no: 35, id: 28497},
    {no: 36, id: 28498},
    {no: 37, id: 28499},
    {no: 38, id: 28500},
    {no: 39, id: 28501},
    {no: 40, id: 28502},
    {no: 41, id: 28503},
    {no: 42, id: 28504},
    {no: 43, id: 28505},
    {no: 44, id: 28506},
    {no: 45, id: 28507},
  ];

  $('.rcn-vp-floor-1').toggle(floorNumber === 1);
  $('.rcn-vp-floor-2').toggle(floorNumber === 2);
  $('.rcn-vp-floor-3').toggle(floorNumber === 3);

  if (floorNumber === 1) {
    rcn_vpTableListingHandler($, floorOneTables, productID, rcn_ajaxurl);
  }

  if (floorNumber === 2) {
    rcn_vpTableListingHandler($, floorTwoTables, productID, rcn_ajaxurl);
  }

  if (floorNumber === 3) {
    rcn_vpTableListingHandler($, floorThreeTables, productID, rcn_ajaxurl);
  }
}

/**
 * Initializes and manages table listings, checking availability and cart status.
 *
 * This function sets up the table listing functionality, creates listings based on
 * provided variation/table IDs, and handles table availability and cart status.
 *
 * Key Features:
 * - Initializes table listing functionality.
 * - Creates listings based on provided variation/table IDs.
 * - Handles table availability and cart status.
 *
 * When a table is in the cart:
 * - Disables 'add to cart' for all li tags.
 *
 * When a table is available:
 * - Enables 'add to cart' for available tables.
 *
 * Click Handlers:
 * - Handles clicks on li tags.
 * - Handles clicks on the plus icon associated with available tables.
 *
 * @param {jQuery} $ - jQuery selector for the target element.
 * @param {Array} tables - Array of variation/table IDs.
 * @param {number} productID - Product ID representing the vendor table.
 * @param {string} rcn_ajaxurl - AJAX URL for server communication.
 * @returns {void} - No return value.
 */
function rcn_vpTableListingHandler($, tables, productID, rcn_ajaxurl) {
  // Initializes table listing functionality
  const targetDiv = $('#rcn-vp-table-listing-container');
  const ul = $('<ul>');

  let action = '<small class="rcn-vp-table-meta">Confirming...</small>';
  let tableDataInCart = {
    status: false,
    tableNo: null,
    variationID: null,
  };
  tableDataInCart = JSON.stringify(tableDataInCart);

  targetDiv.empty();
  let isTableInCart = localStorage.getItem('rcn-vp-is-table-added-to-cart');

  if (!isTableInCart) {
    localStorage.setItem('rcn-vp-is-table-added-to-cart', tableDataInCart);
  }

  // Creates listings based on provided variation/table IDs
  for (let i = 0; i < tables.length; i++) {
    const table = tables[i];

    ul.append(
      `<li data-is-unavailable="true" class="rcn-vp-unavailable-table table-${table.no}" data-table-no="${table.no}">
        Table - ${table.no}${action}
      </li>`
    );
  }

  rcn_tableAvailabilityHandler($, tables, rcn_ajaxurl, productID, targetDiv);

  // Handles clicks on li tags
  ul.on('click', 'li', function () {
    rcn_handleClickOnLi($(this), ul);
  });

  ul.on('click', '.rcn-vp-table-action', function () {
    rcn_tableActionHandler($, ul, $(this), productID, rcn_ajaxurl);
  });

  targetDiv.append(ul);
}

/**
 * Handles the availability status of tables and updates UI elements accordingly.
 *
 * This function checks the availability of each table through an AJAX request,
 * and based on the response, it updates the UI elements of the tables in the targetDiv.
 * It also considers the reservation and cart status to apply proper styling.
 *
 * @param {jQuery} $ - jQuery selector for the target element.
 * @param {Array} tables - Array of table objects with 'id' and 'no' properties.
 * @param {string} rcn_ajaxurl - AJAX URL for server communication.
 * @param {number} productID - Product ID representing the vendor table.
 * @param {jQuery} targetDiv - jQuery selector for the target container.
 * @returns {void} - No return value.
 */

function rcn_tableAvailabilityHandler(
  $,
  tables,
  rcn_ajaxurl,
  productID,
  targetDiv
) {
  function iconElement(iconClass, tableId) {
    return `<i data-table-id="${tableId}" aria-hidden="true" class="fas ${iconClass} rcn-vp-table-action"></i>`;
  }

  function reservedElement(tableId) {
    return `<span data-table-id="${tableId}" class="rcn-vp-reserved-table"><small>Reserved</small></span>`;
  }

  for (let i = 0; i < tables.length; i++) {
    const table = tables[i];

    $.ajax({
      type: 'POST',
      url: rcn_ajaxurl,
      data: {
        action: 'is_table_available',
        product_id: productID,
        variation_id: table.id,
      },
      success: function (response) {
        const jsonResponse = JSON.parse(response);
        const status = jsonResponse.status;
        const action = status
          ? iconElement('fa-plus', table.id)
          : reservedElement(table.id);
        const tableElement = $(targetDiv).find('.table-' + table.no);
        tableElement.find('small').replaceWith(() => $(action));

        let isTableInCart = localStorage.getItem(
          'rcn-vp-is-table-added-to-cart'
        );
        isTableInCart = JSON.parse(isTableInCart);

        if (
          isTableInCart['status'] &&
          tableElement.attr('data-table-no') === isTableInCart['tableNo']
        ) {
          tableElement.removeClass('rcn-vp-unavailable-table');
          tableElement.addClass('rcn-vp-selected-table');
          tableElement.find('.fa-plus').replaceWith(() => $(iconElement('fa-check', table.id))); // prettier-ignore
          tableElement.find('i').prop('disabled', true);
        }

        if (
          isTableInCart['status'] &&
          tableElement.attr('data-table-no') !== isTableInCart['tableNo']
        ) {
          tableElement.addClass('rcn-vp-unavailable-table');
          tableElement.find('i').prop('disabled', true);
        }

        if (status && !isTableInCart['status']) {
          tableElement.removeClass('rcn-vp-unavailable-table');
          tableElement.addClass('rcn-vp-available-table');
          tableElement.attr('data-is-unavailable', false);
        }
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
      },
    });
  }
}

/**
 * Handles the click event on a table item/li.
 *
 * @param {jQuery} clickedItem - The jQuery object representing the clicked table item/li.
 * @param {jQuery} ul - The jQuery object representing the parent unordered list of table items.
 * @returns {void} - The function doesn't return a value.
 */
function rcn_handleClickOnLi(clickedItem, ul) {
  var isUnavailable = clickedItem.data('is-unavailable') === false;

  if (isUnavailable) {
    ul.find('li')
      .removeClass('rcn-vp-selected-table')
      .filter(':not(.rcn-vp-unavailable-table)')
      .addClass('rcn-vp-available-table');

    clickedItem.addClass('rcn-vp-selected-table');
    clickedItem.removeClass('rcn-vp-available-table');
    rcn_highlightTableOnFloorPlan();
  }
}

/**
 * Handles the click event on table action icons and performs actions based on server response.
 *
 * @param {jQuery} $ - jQuery selector for the target element.
 * @param {jQuery} ul - The parent <ul> element containing the table listings.
 * @param {number} productID - The product ID representing the vendor tables.
 * @param {string} rcn_ajaxurl - The AJAX URL for server communication.
 * @returns {void} - No return value.
 */
function rcn_tableActionHandler($, ul, actionBtn, productID, rcn_ajaxurl) {
  const variationID = actionBtn.attr('data-table-id');
  const tableNo = actionBtn.parent().attr('data-table-no');
  const actionIcon = actionBtn;

  let tableData = {
    status: true,
    tableNo: tableNo,
    tableId: variationID,
  };
  tableData = JSON.stringify(tableData);

  actionIcon.removeClass('fa-plus').addClass('fa-spinner');

  // Storing the current parent element
  const parentElement = actionBtn.parent();

  $.ajax({
    type: 'POST',
    url: rcn_ajaxurl,
    data: {
      action: 'rcn_table_add_to_cart_handler',
      product_id: productID,
      variation_id: variationID,
    },
    success: function (response) {
      const result = JSON.parse(response);

      // 1002 means, the table is recently reserved by someone else
      if (result.availability_code === 1002) {
        parentElement.removeClass('rcn-vp-selected-table');
        parentElement.removeClass('rcn-vp-available-table');
        parentElement.addClass('rcn-vp-unavailable-table');
        parentElement.prop('disabled', true);

        parentElement.attr('data-is-unavailable', true);

        actionIcon.replaceWith($('<small>Already reserved</small>'));
      }

      // 1001 means, the table is available for reservation
      if (result.availability_code === 1001) {
        actionIcon.removeClass('fa-spinner').addClass('fa-check');

        $('.rcn-vp-table-action').prop('disabled', true);
        $('.rcn-vp-table-action').parent().prop('disabled', true);
        $('.rcn-vp-table-action').parent().attr('data-is-unavailable', true);
        $('.rcn-vp-table-action')
          .parent()
          .removeClass('rcn-vp-available-table');

        $('.rcn-vp-table-action').each(function () {
          const currentParent = $(this).parent();
          const parentTableNo = currentParent.attr('data-table-no');

          if (parentTableNo !== tableNo) {
            currentParent.addClass('rcn-vp-unavailable-table');
          }
        });

        localStorage.setItem('rcn-vp-is-table-added-to-cart', tableData);
      }
    },
    error: function (xhr, status, error) {
      console.log(xhr.responseText);
    },
  });
}

function rcn_highlightTableOnFloorPlan($) {
  // TODO: Implement the function to highlight table location on the floor plan
  console.log('Calling the function to highlight table location on floor plan');
}

function rcn_vpCartVisualInteraction(
  $,
  isUpdated = false,
  cartItems = [],
  message,
  callback,
  callbackPriority = 2
) {
  $('.rcn-vp-cart-message').remove();
  const cartContainer = $('.rcn-vpciw');
  const messageContainer = $('<div class="rcn-vp-cart-message">');
  const disableScrollClass = 'disable-scroll';
  const totalCartItems = cartItems.length;
  callbackPriority = Number(callbackPriority);

  if (isUpdated === false) {
    if (message) {
      messageContainer.html(message);

      cartContainer.append(messageContainer);
      cartContainer.addClass('disable-scroll');
    }

    if (callback) {
      callback(
        messageContainer,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }
  }

  if (isUpdated === true) {
    if (callback && 1 === callbackPriority) {
      callback(
        messageContainer,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }

    const ul = $('<ul>');

    for (let i = 0; i < cartItems.length; i++) {
      const element = cartItems[i];
      const uniqueIdentifier = element.id;

      const cartItem = $('<li>');
      const cartItemContent =
        '<span class="name">' +
        element.name +
        '</span>' +
        '<span class="quantity">' +
        element.quantity +
        '</span>' +
        '<span class="price">' +
        rcn_vpPriceFormatter(Number(element.price)) +
        '</span>' +
        '<span class="remove">Remove</span>';

      cartItem.append(cartItemContent);
      cartItem.addClass('rcn-vp-cart-listing-' + uniqueIdentifier);
      cartItem.addClass('rcn-vp-cart-listing');
      cartItem.attr('data-product-id', uniqueIdentifier);

      ul.append(cartItem);
    }

    cartContainer.empty().append(ul);
    cartContainer.find('ul > li:nth-child(even)').css('background', '#F2F4F8');

    $('.remove').on('click', function () {
      let productID = $(this).parent();
      productID = productID.attr('data-product-id');

      rcn_vpRemoveProductFromCart(productID);
    });

    if (message) {
      messageContainer.html(message);

      cartContainer.append(messageContainer);
      cartContainer.addClass(disableScrollClass);
    }

    if (callback && 1 !== callbackPriority) {
      callback(
        messageContainer,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }
  }
}

//TODO: Create remove product from cart function
function rcn_vpRemoveProductFromCart(productID) {
  console.log('Remove product from cart ' + productID);
}

function callbackForTesting(
  messageContainer,
  cartContainer,
  disableScrollClass
) {
  setTimeout(() => {
    messageContainer.remove();
    cartContainer.removeClass(disableScrollClass);
  }, 3000);
}

function rcn_addProductToCart(
  $,
  action = 'loadCart',
  cartItem,
  callback,
  callbackPriority,
  categoryID,
  ajaxurl
) {
  if ('loadCart' === action) {
    const message = '<p>Loading cart data. Please wait...</p>';
    rcn_vpCartVisualInteraction($, false, cartItem, message);

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'rcn_get_vp_products',
        categoryID: categoryID,
      },
      success: function (response) {
        response = JSON.parse(response);
        console.log(response);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
      },
    });
  }
}

function rcn_addProductToCart(
  $,
  action = 'loadCart',
  cartItem,
  callback,
  callbackPriority,
  categoryID,
  ajaxurl
) {
  let message;

  if ('loadCart' === action) {
    message = '<p>Loading cart data. Please wait...</p>';
    rcn_vpCartVisualInteraction($, false, cartItem, message);

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'rcn_get_vp_products',
        categoryID: categoryID,
      },
      success: function (response) {
        try {
          localStorage.setItem('rcn_vpCartData', response);
          const parsedResponse = JSON.parse(response);
          message = '<p>Data loaded successfully</p>';

          // prettier-ignore
          rcn_vpCartVisualInteraction($, true, parsedResponse, message, callback, 2);
        } catch (error) {
          console.error('Error parsing JSON response:', error);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX error:', error);
      },
    });
  }

  if ('updateCart' === action) {
    if (!cartItem) {
      message =
        '<p style="text-align: center">Something went wrong. <br />Please try again or contact support</p>';
      rcn_vpCartVisualInteraction($, false, [], message, callback, 2);
      return;
    }

    let cartArray = localStorage.getItem('rcn_vpCartData');
    cartArray = JSON.parse(cartArray);
    cartArray.push(cartItem);
    message = 'The product is added to cart successfully';

    rcn_vpCartVisualInteraction($, true, cartArray, message, callback, 2);
    cartArray = JSON.stringify(cartArray);

    localStorage.setItem('rcn_vpCartData', cartArray);
  }

  function callback(
    messageContainer,
    cartContainer,
    disableScrollClass,
    totalCartItems
  ) {
    console.log(messageContainer);
    setTimeout(() => {
      $('.rcn-vp-cart-message').remove();
      cartContainer.removeClass(disableScrollClass);
    }, 700);
  }
}
