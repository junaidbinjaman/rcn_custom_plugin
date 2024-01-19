(function ($) {
  'use strict';

  /**
   * Global variables declaration
   */
  const rcn_ajaxurl = wp_ajax_object.ajax_url;
  const categoryID = 264; // RCN vendor package category

  window.rcn_vpAjaxurl = wp_ajax_object.ajax_url;
  window.rcn_vpCategoryID = 264;

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
    rcn_vpAddonsHandler($);

    rcn_cartInitializer($);

    rcn_floorSelectionHandler($);

    $('.rcn-vp-floor-1-selector-btn').each(function () {
      $(this).on('click', function () {
        localStorage.setItem('rcn_activeFloorNumber', 1);
        rcn_floorSelectionHandler($);
      });
    });

    $('.rcn-vp-floor-2-selector-btn').each(function () {
      $(this).on('click', function () {
        localStorage.setItem('rcn_activeFloorNumber', 2);
        rcn_floorSelectionHandler($);
      });
    });

    $('.rcn-vp-floor-3-selector-btn').each(function () {
      $(this).on('click', function () {
        localStorage.setItem('rcn_activeFloorNumber', 3);
        rcn_floorSelectionHandler($);
      });
    });

    const rcn_vpFloorSelectorForm = $(
      '.rcn-vp-floor-selector-form form select'
    );

    rcn_vpFloorSelectorForm.val(localStorage.getItem('rcn_activeFloorNumber'));

    rcn_vpFloorSelectorForm.on('change', function () {
      localStorage.setItem('rcn_activeFloorNumber', $(this).val());
      rcn_floorSelectionHandler($);
    });

    $('.rc-vpaa-quantity').on('click', function () {
      const productID = $(this).attr('data-product-id');
      console.log(productID);
      rcn_vpAddOnsAddToCart($, productID);
    });

    $('.rcn-vp-floating-cart').on('click', function () {
      setTimeout(function () {
        rcn_cartInitializer($);
      }, 100);
    });
  });
})(jQuery);

function rcn_cartInitializer($) {
  rcn_vpPrevNextButtonHandler($, null);

  rcn_addProductToCart($, 'loadCart', null, window.rcn_vpCategoryID);

  $('.rcn_vps-next-button').each(function () {
    $(this).off('click');
    $(this).on('click', function () {
      rcn_vpPrevNextButtonHandler($, 'next');
    });
  });

  $('.rcn_vps-prev-button').each(function () {
    $(this).off('click');
    $(this).on('click', function () {
      rcn_vpPrevNextButtonHandler($, 'prev');
    });
  });
}

function rcn_vpPriceFormatter(price, decimal) {
  const formattedPrice =
    '$' + price.toFixed(decimal).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
function rcn_vpStepSwitchHandler($, totalSteps) {
  let rcn_ajaxurl = window.window.rcn_vpAjaxurl;
  let activeStep = localStorage.getItem('rcn-vp-active-step');
  let totalCartItems = localStorage.getItem('rcn_vpCartData');

  if (totalCartItems) {
    totalCartItems = JSON.parse(totalCartItems);
    totalCartItems = length;
  } else {
    totalCartItems = 0;
  }

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
  $('.rcn_vps-checkout-button').hide();

  if (totalCartItems) {
    $('.rcn_vps-checkout-button').toggle(activeStep === totalSteps);
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
function rcn_floorSelectionHandler($) {
  let floorNumber = localStorage.getItem('rcn_activeFloorNumber');
  const productID = 28462; // table wrapper product

  if (!floorNumber) {
    localStorage.setItem('rcn_activeFloorNumber', 1);
  }

  floorNumber = localStorage.getItem('rcn_activeFloorNumber');
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
    rcn_vpTableListingHandler($, floorOneTables, productID);
  }

  if (floorNumber === 2) {
    rcn_vpTableListingHandler($, floorTwoTables, productID);
  }

  if (floorNumber === 3) {
    rcn_vpTableListingHandler($, floorThreeTables, productID);
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
function rcn_vpTableListingHandler($, tables, productID) {
  // Initializes table listing functionality
  const targetDiv = $('#rcn-vp-table-listing-container');
  const rcn_ajaxurl = wp_ajax_object.ajax_url;
  const ul = $('<ul>');

  let action = '<small class="rcn-vp-table-meta">Checking</small>';
  let tableDataInCart = {
    status: false,
    tableNo: null,
    variationID: null,
  };
  tableDataInCart = JSON.stringify(tableDataInCart);

  targetDiv.empty();
  let isTableInCart = localStorage.getItem('rcn_vpIsTableAddedToCart');

  if (!isTableInCart) {
    localStorage.setItem('rcn_vpIsTableAddedToCart', tableDataInCart);
  }

  // Create listings based on provided variation/table IDs
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

        let isTableInCart = localStorage.getItem('rcn_vpIsTableAddedToCart');
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

  let notification = {
    type: 'info',
    message: 'Adding item to cart.',
  };

  let tableData = {
    status: true,
    tableNo: tableNo,
    tableId: variationID,
  };
  tableData = JSON.stringify(tableData);

  actionIcon.removeClass('fa-plus').addClass('fa-spinner');
  rcn_vpCartVisualInteraction($, false, notification);

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

        actionIcon.replaceWith($('<small>Reserved</small>'));
      }

      // 1001 means, the table is available for reservation
      if (result.availability_code === 1001) {
        const id = variationID;
        const name = 'Table - ' + tableNo;
        const price = result.price;
        const quantity = 1;

        // prettier-ignore
        const productData = {
          id, name, price, quantity
        }

        rcn_addProductToCart($, 'updateCart', productData);

        actionIcon.removeClass('fa-spinner').addClass('fa-check');

        $('.rcn-vp-table-action').prop('disabled', true);
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

        localStorage.setItem('rcn_vpIsTableAddedToCart', tableData);
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

/**
 * Handles the visual interactions on vp cart.
 * @param {function} $ - jQuery library.
 * @param {boolean} isUpdated - Indicates whether the cart is updated.
 * @param {Object} notification - Object containing notification details (type and message).
 * @param {Array} cartItems - Array of cart items.
 * @param {function} callback - Callback function to be executed.
 * @param {number} callbackPriority - Priority level for the callback function.
 */
function rcn_vpCartVisualInteraction(
  $,
  isUpdated = false,
  notification = {type: null, message: null},
  cartItems = null,
  callback,
  callbackPriority = 2
) {
  // Removing the vp cart container from DOM if already exists
  $('.rcn-vp-cart-notification').remove();

  const cartContainer = $('.rcn-vpciw');
  const notificationContainerClass = 'rcn-vp-cart-notification';
  const notificationContainer = $('<div>');
  const disableScrollClass = 'disable-scroll';
  const totalCartItems = cartItems ? cartItems.length : null;

  notificationContainer.addClass(notificationContainerClass);
  callbackPriority = Number(callbackPriority);

  // If the cart is empty
  if (totalCartItems === 0) {
    cartContainer.empty();

    rcn_vpCartCalculator($, null);

    const notification = {
      type: 'info',
      message: 'Your cart is empty',
    };

    notificationContainer.html(notificationHandler(notification));
    cartContainer.append(notificationContainer);
    cartContainer.addClass(disableScrollClass);
    return;
  }

  // If the cart is not empty
  if (isUpdated === false) {
    if (notification.type) {
      notificationContainer.html(notificationHandler(notification));
      cartContainer.append(notificationContainer);
      cartContainer.addClass('disable-scroll');
    }

    if (callback) {
      callback(
        notificationContainerClass,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }

    return;
  }

  // If the cart is updated
  if (isUpdated === true) {
    if (callback && 1 === callbackPriority) {
      callback(
        notificationContainerClass,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }

    const cartItemsWrapper = $('<ul>');

    // Populate the cart items
    for (let i = 0; i < cartItems.length; i++) {
      const element = cartItems[i];
      const item = cartItems[i];
      const uniqueIdentifier = element.id;

      const cartItem = $('<li>');
      const cartItemContent = `
        <div class="rcn-vp-cart-listing-name">
          ${item.name}
        </div>
        <div class="rcn-vp-cart-listing-meta">
          <span class="rcn-vp-cart-listing-quantity">
            <span class="rcn-vp-cart-listing-label">Qty:</span>
            ${item.quantity}
          </span>
          <span class="rcn-vp-cart-listing-price">
            <span class="rcn-vp-cart-listing-label">Subtotal:</span>
            ${rcn_vpPriceFormatter(Number(item.price), 2)}
          </span>
          <span data-product-id="${uniqueIdentifier}" class="rcn-vp-cart-listing-remove">
          Remove
          </span>
        </div>
        `;

      cartItem.append(cartItemContent);
      cartItem.addClass('rcn-vp-cart-listing-' + uniqueIdentifier);
      cartItem.addClass('rcn-vp-cart-listing');
      cartItem.attr('data-product-id', uniqueIdentifier);

      cartItemsWrapper.append(cartItem);
    }

    // Update cart container with the new items
    cartContainer.empty().append(cartItemsWrapper);
    cartContainer.find('ul > li:nth-child(even)').css('background', '#F2F4F8');

    // Add click event for the remove button
    $('.rcn-vp-cart-listing-remove').on('click', function () {
      let productID = $(this).attr('data-product-id');

      rcn_vpRemoveProductFromCart($, productID);
    });

    // Update cart calculation
    rcn_vpCartCalculator($, cartItems);

    // Display notification if provided
    if (notification.type) {
      notificationContainer.html(notificationHandler(notification));
      cartContainer.append(notificationContainer);
      cartContainer.addClass(disableScrollClass);
    }

    // Execute callback with appropriate priority
    if (callback && 1 !== callbackPriority) {
      callback(
        notificationContainerClass,
        cartContainer,
        disableScrollClass,
        totalCartItems
      );
    }

    return;
  }

  /**
   * Handles the creation of notification messages based on the notification type.
   * @param {Object} notification - Object containing notification details (type and message).
   * @returns {jQuery} - jQuery object representing the notification message.
   */
  function notificationHandler(notification) {
    const msg = $('<p>');

    if (notification.type === 'info') {
      msg.addClass('rcn-vp-cart-info-notification');
    }

    if (notification.type === 'success') {
      msg.addClass('rcn-vp-success-notification');
    }

    if (notification.type === 'error') {
      msg.addClass('rcn-vp-error-notification');
    }

    msg.html(notification.message);
    return msg;
  }
}

/**
 * Calculates and updates the total price on vp cart.
 * @param {function} $ - jQuery library.
 * @param {Array} cartItems - Array of cart items containing price information.
 */
function rcn_vpCartCalculator($, cartItems) {
  const priceElement = $('.rcn-vp-cart-total');

  let totalPrice = 0;

  if (cartItems && cartItems.length > 0) {
    for (let i = 0; i < cartItems.length; i++) {
      const cartItem = cartItems[i];

      totalPrice += Number(cartItem.price);
    }
  }

  totalPrice = rcn_vpPriceFormatter(totalPrice, 2);

  priceElement.text(totalPrice);
}

/**
 * Adds a product to the vp cart or loads cart data on page load using AJAX.
 * @param {function} $ - jQuery library.
 * @param {string} action - Specifies the action to perform ('loadCart' or 'updateCart').
 * @param {Object} cartItem - Object representing the product to be added to the cart.
 * @param {string} categoryID - ID of the product category.
 */
// prettier-ignore
function rcn_addProductToCart($, action = 'loadCart', cartItem, categoryID) {
  const ajaxurl = wp_ajax_object.ajax_url;
  let notification;

  if ('loadCart' === action) {
    notification = {
      type: 'info',
      message: 'Loading cart.'
    };
    rcn_vpCartVisualInteraction($, false, notification);

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
          const cartItems = JSON.parse(response);

          notification = {
            type: 'success',
            message: ''
          };

          // prettier-ignore
          rcn_vpCartVisualInteraction($, true, notification, cartItems, callback, 2);
        } catch (error) {
          notification = {
            type: 'error',
            message: 'Something went wrong. <br />Please refresh the page.'
          };
          rcn_vpCartVisualInteraction($, false, notification);

          console.error('Error parsing JSON response:', error);
        }
      },
      error: function (xhr, status, error) {
        notification = {
          type: 'error',
          message: 'Something went wrong. <br />Please refresh the page.'
        };
        rcn_vpCartVisualInteraction($, false, notification);
        console.error('AJAX error:', error);
      },
    });
  }

  if ('updateCart' === action) {
    if (!cartItem) {
        notification = {
          type: 'error',
          message: 'Something went wrong. <br />Please refresh the page.'
        };

      rcn_vpCartVisualInteraction($, false, notification);
      return;
    }

    let cartArray = localStorage.getItem('rcn_vpCartData');
    cartArray = JSON.parse(cartArray);

    if (!cartArray) {
      notification = {
        type: 'error',
        message: 'Something went wrong. <br />Please refresh the page.'
      };

      rcn_vpCartVisualInteraction($, false, notification);
      return;
    }

    cartArray.push(cartItem);
    notification = {
      type: 'success',
      message: ''
    };

    rcn_vpCartVisualInteraction($, true, notification, cartArray, callback, 2);
    cartArray = JSON.stringify(cartArray);

    localStorage.setItem('rcn_vpCartData', cartArray);
  }

  function callback(
    notificationContainerClass,
    cartContainer,
    disableScrollClass,
    totalCartItems
  ) {
    setTimeout(() => {
      $('.' + notificationContainerClass).remove();
      cartContainer.removeClass(disableScrollClass);
    }, 700);
  }
}

/**
 * Removes a product from the vp cart using AJAX and updates the cart visual interaction.
 * @param {function} $ - jQuery library.
 * @param {string} productID - ID of the product to be removed from the cart.
 */
function rcn_vpRemoveProductFromCart($, productID) {
  const ajaxurl = wp_ajax_object.ajax_url;
  let cartArray = localStorage.getItem('rcn_vpCartData');
  let isProductInCart = false;
  let notification;

  cartArray = JSON.parse(cartArray);

  if (!cartArray) {
    notification = {
      type: 'error',
      message: 'Something went wrong. <br />Please refresh the page.',
    };
    rcn_vpCartVisualInteraction($, false, notification);
    return;
  }

  for (let i = 0; i < cartArray.length; i++) {
    const element = cartArray[i];
    if (productID == element.id) {
      isProductInCart = true;
      cartArray.splice(i, 1);
      break;
    }
  }

  if (!isProductInCart) {
    notification = {
      type: 'error',
      message: 'Unidentified product',
    };
    rcn_vpCartVisualInteraction($, false, notification);
    return;
  }

  notification = {
    type: 'info',
    message: 'Removing item from cart.',
  };

  rcn_vpCartVisualInteraction($, false, notification);

  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      action: 'rcn_vp_remove_product_from_cart',
      productID: productID,
    },
    success: function (responseData) {
      response = JSON.parse(responseData);

      if (!response.status) {
        notification = {
          type: 'error',
          message: 'Something went wrong. <br />Please refresh the page.',
        };

        rcn_vpCartVisualInteraction($, false, notification);
        return;
      }
    },
    error: function (xhr, status, error) {
      notification = {
        type: 'error',
        message: 'Something went wrong. <br />Please refresh the page.',
      };

      rcn_vpCartVisualInteraction($, false, notification);
      console.error(xhr.responseText);
    },
    complete: function () {
      if (response && response.status) {
        notification = {
          type: 'success',
          message: '',
        };

        rcn_vpCartVisualInteraction($, true, notification, cartArray, callback);

        cartArray = JSON.stringify(cartArray);
        localStorage.setItem('rcn_vpCartData', cartArray);
      }

      let tableData = localStorage.getItem('rcn_vpIsTableAddedToCart');

      if (tableData) {
        tableData = JSON.parse(tableData);

        if (tableData.tableId === productID) {
          localStorage.removeItem('rcn_vpIsTableAddedToCart');

          rcn_floorSelectionHandler($, 28462);
        }
      }
    },
  });

  function callback(
    notificationContainerClass,
    cartContainer,
    disableScrollClass,
    totalCartItems
  ) {
    setTimeout(() => {
      $('.' + notificationContainerClass).remove();
      cartContainer.removeClass(disableScrollClass);
    }, 700);
  }
}

function rcn_vpAddOnsAddToCart($, productID) {
  productID = Number(productID);
  const ajaxurl = wp_ajax_object.ajax_url;
  let cartNotification;

  cartNotification = {
    type: 'info',
    message: 'Adding item to cart.',
  };

  rcn_vpCartVisualInteraction($, false, cartNotification);

  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      action: 'rcn_vp_addons_add_to_cart',
      productID: productID,
    },
    success: function (responseData) {
      let response = JSON.parse(responseData);
      addOnsAddedSuccessfully(response);
      console.log(response);
    },
    error: function (xhr, status, error) {
      cartNotification = {
        type: 'error',
        message: 'Something went wrong. <br />Please refresh the page.',
      };

      rcn_vpCartVisualInteraction($, false, cartNotification);

      console.error(xhr.responseText);
    },
  });

  function addOnsAddedSuccessfully(response) {
    if (response.status_code === 800800) {
      cartNotification = {
        type: 'info',
        message: '',
      };

      rcn_vpCartVisualInteraction($, false, cartNotification, null, callback);
      return;
    }

    if (!response.status) {
      cartNotification = {
        type: 'info',
        message: 'Something went wrong. <br />Please refresh the page.',
      };

      rcn_vpCartVisualInteraction($, false, cartNotification, null);
      return;
    }

    const productData = {
      id: response.id,
      name: response.name,
      quantity: response.cart_quantity,
      price: response.price,
    };

    rcn_addProductToCart($, 'updateCart', productData);
  }

  function callback(
    notificationContainerClass,
    cartContainer,
    disableScrollClass,
    totalCartItems
  ) {
    setTimeout(() => {
      $('.' + notificationContainerClass).remove();
      cartContainer.removeClass(disableScrollClass);
    }, 700);
  }
}

function rcn_vpAddonsHandler($) {
  const addonsIDs = [9481, 9473, 17630, 9480, 9475, 9476, 9474, 9479];

  for (let i = 0; i < addonsIDs.length; i++) {
    const ID = addonsIDs[i];
    const uniqueIdentifier = 'rcn-vpaa-' + ID;

    $.ajax({
      type: 'POST',
      url: window.rcn_vpAjaxurl,
      data: {
        action: 'rcn_vp_addons_stock_checker',
        productID: ID,
      },
      success: function (response) {
        response = JSON.parse(response);

        if (response.status && response.stock_quantity === 0) {
          $('.' + uniqueIdentifier).find('.rcn-vpaa-oosi').css('display', 'block');
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        return false;
      },
    });
  }
}
