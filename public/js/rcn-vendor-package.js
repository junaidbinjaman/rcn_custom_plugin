(function ($) {
  'use strict';
  /**
   * Global variable declarations
   */
  window.rcn_vpAjaxurl = wp_ajax_object.ajax_url;
  window.rcn_vpCategoryID = 497; // RCN vendor package category
  window.productID = 27759; // table wrapper product where each table is a variation

  /**
   * All of the js/jQuery code for vendor-package reside in this file.
   *
   * This enables to define handlers, for when the DOM is ready:
   */
  $(function () {
    rcn_vpCartInitializer($);

    rcn_vpTableFloorHandler($);

    rcn_vpAddonsHandler($);

    $('.rcn-vp-floating-cart').on('click', function () {
      setTimeout(function () {
        rcn_vpCartInitializer($);
      }, 100);
    });
  });
})(jQuery);

/**
 * Initializes the vendor package cart functionality.
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_vpCartInitializer($) {
  rcn_vpPrevNextButtonHandler($, null);

  rcn_addProductToCart($, 'loadCart');

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

  $('.rcn_vp-product-add-to-cart-btn').click('click', function () {
    rcn_vpTriggerCartOnTabletMobile($);
  });
}

/**
 * Handles the floor selection functionality in the vendor package table
 * and table location indicator initialization
 *
 * Codes responsible for table listing resides inside this function as well
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_vpTableFloorHandler($) {
  const floorSelectorForm = $('.rcn-vp-floor-selector-form form select'); // Appears on mobile and table
  let activeFloor;

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

  activeFloor = localStorage.getItem('rcn_activeFloorNumber');
  floorSelectorForm.val(activeFloor);

  floorSelectorForm.on('change', function () {
    localStorage.setItem('rcn_activeFloorNumber', $(this).val());
    rcn_floorSelectionHandler($);
  });
}

/**
 * Handles the initialization of vendor package addons functionality.
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_vpAddonsHandler($) {
  // Custom according boxes used for package addons
  rcn_vpAddonsAccording($);

  rcn_vpAddonsStatusChecker($);

  // Add "add to cart" functionality on add-ons add to cart btn
  $('.rc-vpaa-quantity').on('click', function () {
    const productID = $(this).attr('data-product-id');
    rcn_vpAddOnsAddToCart($, productID);
  });
}

/**
 * Formats a price by adding a dollar sign, rounding it to a specified number of decimal places,
 * and adding commas for thousands separators.
 *
 * @param {number} price - The price to be formatted.
 * @param {number} decimal - The number of decimal places to round the price to.
 * @returns {string} - The formatted price as a string.
 */
function rcn_vpPriceFormatter(price, decimal) {
  const formattedPrice =
    '$' + price.toFixed(decimal).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  return formattedPrice;
}

/**
 * Initializes or updates the active step number based on the specified flag.
 *
 * If the active step is already initialized, this function increments or decrements
 * the active step number, depending on the provided flag.
 *
 * @param {jQuery selector} $ - jQuery reference.
 * @param {string} flag - A string flag indicating whether to navigate to the previous or next step.
 *                        Use 'prev' for the previous step and 'next' for the next step.
 * @returns {void} - The return type is 'void' as the purpose is to update the active step, not to return a value.
 */
function rcn_vpPrevNextButtonHandler($, flag = null) {
  const totalSteps = 1; // starts from 0. So 0 means 1 step and 2 means 3 steps

  if (!localStorage.getItem('rcn_vpActiveStep')) {
    localStorage.setItem('rcn_vpActiveStep', 0);

    rcn_vpStepSwitchHandler($, totalSteps);
    return;
  }

  if (flag === 'next') {
    let activeStep = localStorage.getItem('rcn_vpActiveStep');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep + 1;
    localStorage.setItem('rcn_vpActiveStep', nextActiveStep);

    rcn_vpStepSwitchHandler($, totalSteps);
    return;
  }

  if (flag === 'prev') {
    let activeStep = localStorage.getItem('rcn_vpActiveStep');
    activeStep = Number(activeStep);

    let nextActiveStep = activeStep - 1;
    localStorage.setItem('rcn_vpActiveStep', nextActiveStep);

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
 * @param {jQuery} $ - jQuery reference.
 * @param {number} totalSteps - The total number of steps in the vendor package checkout.
 * @param {string} rcn_ajaxurl - The WP AJAX URL for server communication.
 * @returns {void} - The function doesn't return a value.
 */
function rcn_vpStepSwitchHandler($, totalSteps) {
  let activeStep = localStorage.getItem('rcn_vpActiveStep');
  let totalCartItems = localStorage.getItem('rcn_vpCartData');

  if (totalCartItems) {
    totalCartItems = JSON.parse(totalCartItems);
    totalCartItems = totalCartItems.length;
  } else {
    totalCartItems = 0;
  }

  const stepClasses = [];
  const stepIndicatorClasses = [];

  activeStep = Number(activeStep);

  if (activeStep < 0 || activeStep > totalSteps) {
    console.error({
      status: false,
      message: 'Invalid active step',
    });

    return;
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
    localStorage.setItem('rcn_vpStep0', true);
  }

  if (activeStep > 0) {
    let prevStepStatus = localStorage.getItem('rcn_vpStep' + (activeStep - 1));

    if (prevStepStatus === null) {
      console.error('Step ' + (activeStep - 1) + ' is required');
      return;
    }

    localStorage.setItem('rcn_vpStep' + activeStep, true);
    $(stepClasses[activeStep]).show();
  }

  // Sources for step indicator circle
  for (let i = 0; i <= totalSteps; i++) {
    const IndicatorClass = '.rcn-vp-step-' + i + '-indicator h2';
    stepIndicatorClasses.push(IndicatorClass);
  }

  const activeStepIndicators = stepIndicatorClasses.splice(0, activeStep + 1);
  const activeStepIndicatorsStyles = {
    backgroundColor: '#006cfa',
    cursor: 'pointer',
  };

  for (let i = 0; i < activeStepIndicators.length; i++) {
    const activeIndicator = activeStepIndicators[i];

    $(activeIndicator).off('click');

    $(activeIndicator).on('click', function () {
      localStorage.setItem('rcn_vpActiveStep', i);
      rcn_vpStepSwitchHandler($, totalSteps);
    });

    $(activeIndicator).css(activeStepIndicatorsStyles);
  }
}

/**
 * Handles the visual interactions on vp cart.
 *
 * @param {function} $ - jQuery reference.
 * @param {boolean} isUpdated - A flag(true/false) indicates whether the cart is updated.
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
  // Make sure, the cart notification container is empty
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
      const item = cartItems[i];
      const uniqueIdentifier = item.id;

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
      const productID = $(this).attr('data-product-id');

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
   *
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
 *
 * @param {function} $ - jQuery reference.
 * @param {Array} cartItems - Array of cart items containing price information.
 */
function rcn_vpCartCalculator($, cartItems) {
  const priceElement = $('.rcn-vp-cart-total'); // The element that displays the cart total

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
 *
 * @param {function} $ - jQuery reference.
 * @param {string} action - A flag('loadCart' or 'updateCart') specifies the action to perform.
 * @param {Object} cartItem - Object representing the product to be added to the cart.
 * @param {string} categoryID - ID of the product category.
 */
// prettier-ignore
function rcn_addProductToCart($, action = 'loadCart', cartItem) {
  const ajaxurl = rcn_vpAjaxurl;
  const categoryID = window.rcn_vpCategoryID;
  let notification;

  if ('loadCart' === action) {
    notification = {
      type: 'info',
      message: 'Loading cart'
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
        loadCartItems(response);
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

  function loadCartItems(response) {
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

function rcn_vpTriggerCartOnTabletMobile() {
  const popupId = 27815

  let isNotDesktop = window.matchMedia(
    'only screen and (max-width: 1030px)'
  ).matches;

  if (isNotDesktop) {
    elementorProFrontend.modules.popup.showPopup({id: popupId});
    rcn_vpCartInitializer(jQuery);
  }
}

/**
 * Removes a product from the vp cart using AJAX and updates the cart visual interaction.
 *
 * @param {function} $ - jQuery reference.
 * @param {string} productID - ID of the product to be removed from the cart.
 */
function rcn_vpRemoveProductFromCart($, productID) {
  const ajaxurl = window.rcn_vpAjaxurl;
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
    const item = cartArray[i];
    if (productID == Number(item.id)) {
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
    message: 'Removing item from cart',
  };

  rcn_vpCartVisualInteraction($, false, notification);

  $.ajax({
    type: 'POST',
    url: ajaxurl,
    data: {
      action: 'rcn_vp_remove_product_from_cart',
      productID: productID,
    },
    success: function (response) {
      response = JSON.parse(response);

      if (!response.status) {
        notification = {
          type: 'error',
          message: 'Something went wrong. <br />Please refresh the page.',
        };

        rcn_vpCartVisualInteraction($, false, notification);
        return;
      }

      handleSuccessfulProductRemoval(response);
    },
    error: function (xhr, status, error) {
      notification = {
        type: 'error',
        message: 'Something went wrong. <br />Please refresh the page.',
      };

      rcn_vpCartVisualInteraction($, false, notification);
      console.error(xhr.responseText + 'JJJ');
    },
  });

  function handleSuccessfulProductRemoval(response) {
    if (response && response.status) {
      notification = {
        type: 'success',
        message: '',
      };

      rcn_vpCartVisualInteraction($, true, notification, cartArray, callback);

      cartArray = JSON.stringify(cartArray);
      localStorage.setItem('rcn_vpCartData', cartArray);
    }

    $('.rcn-vp-table-wrapper-ul li').each(function () {
      const tableID = $(this).attr('data-table-id');

      if (Number(tableID) === response.variation_id) {
        $(this).find('.rcn_vp-table-listing-body-action-spinner').hide();
        $(this).find('.rcn_vp-table-listing-body-remove-cart-btn').show();

        $(this)
          .attr('data-is-unavailable', false)
          .removeClass('rcn-vp-added-to-cart-table')
          .addClass('rcn-vp-available-table');
      }
    });
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
 * Handles floor selection, updates UI elements, and triggers table listings.
 *
 * Facilitates the selection of different floors within the RCN vendor package checkout,
 * displaying the corresponding floor plan image and passing floor tables to the
 * rcn_vpTableListingHandler function based on the selected floorNumber.
 *
 * @param {jQuery} $ - jQuery reference.
 * @returns {void} - No return value.
 */
function rcn_floorSelectionHandler($) {
  let floorNumber = localStorage.getItem('rcn_activeFloorNumber');

  if (!floorNumber) {
    localStorage.setItem('rcn_activeFloorNumber', 1);
  }

  floorNumber = localStorage.getItem('rcn_activeFloorNumber');
  floorNumber = Number(floorNumber);

  const floorOneTables = [
    {no: 1, id: 27760},
    {no: 2, id: 27771},
    {no: 3, id: 27761},
    {no: 4, id: 27762},
    {no: 5, id: 27763},
  ];

  const floorTwoTables = [
    {no: 6, id: 27764},
    {no: 7, id: 27765},
    {no: 8, id: 27766},
    {no: 9, id: 27767},
    {no: 10, id: 27768},
    {no: 11, id: 27769},
    {no: 12, id: 27770},
    {no: 13, id: 27772},
    {no: 14, id: 27773},
    {no: 15, id: 27774},
    {no: 16, id: 27775},
    {no: 17, id: 27776},
    {no: 18, id: 27777},
    {no: 19, id: 27778},
    {no: 20, id: 27779},
  ];

  const floorThreeTables = [
    {no: 21, id: 27780},
    {no: 22, id: 27781},
    {no: 23, id: 27782},
    {no: 24, id: 27783},
    {no: 25, id: 27784},
    {no: 26, id: 27785},
    {no: 27, id: 27786},
    {no: 28, id: 27787},
    {no: 29, id: 27788},
    {no: 30, id: 27789},
    {no: 31, id: 27790},
    {no: 32, id: 27791},
    {no: 33, id: 27792},
  ];

  // Toggle floor plan map/image
  $('.rcn-vp-floor-1').toggle(floorNumber === 1);
  $('.rcn-vp-floor-2').toggle(floorNumber === 2);
  $('.rcn-vp-floor-3').toggle(floorNumber === 3);

  if (floorNumber === 1) {
    rcn_vpTableListingHandler($, floorOneTables);
  }

  if (floorNumber === 2) {
    rcn_vpTableListingHandler($, floorTwoTables);
  }

  if (floorNumber === 3) {
    rcn_vpTableListingHandler($, floorThreeTables);
  }

  setTimeout(() => {
    rcn_vpTableListingAccordingBox($);
  }, 200)
  rcn_vpTableLocationIndicatorHandler($, 0);
}

/**
 * Initializes and manages table listings, checking availability.
 *
 * This function sets up the table listing functionality, creates listings based on
 * provided variation/table IDs, and handles table availability.
 *
 * Key Features:
 * - Initializes table listing functionality.
 * - Creates listing according boxes based on provided variation/table IDs.
 * - Handles table availability.
 *
 * When a table is available:
 * - Enables 'add to cart' for available tables.
 * - disable 'add to cart' for unavailable tables.
 *
 * Click Handlers:
 * - Handles clicks on li tags.
 * - Handles clicks on the "Add to cart" and "Remove from cart" button.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {Array} tables - Array of variation/table IDs.
 * @returns {void} - No return value.
 */
function rcn_vpTableListingHandler($, tables) {
  const tableListingContainer = $('#rcn-vp-table-listing-container');
  const tableListingWrapper = $('<ul>');

  tableListingWrapper.addClass('rcn-vp-table-wrapper-ul');
  tableListingContainer.empty();

  for (let i = 0; i < tables.length; i++) {
    const table = tables[i];

    tableListingWrapper.append(
      `<li data-is-unavailable="true" class="rcn-vp-status-checking-table rcn_vp-table-listing table-${table.no}" data-table-no="${table.no}" data-table-id="${table.id}">
				  <div class="rcn_vp-table-listing-head">
					  <div class="rcn_vp-table-listing-head-left">
						  <i aria-hidden="true" class="fas fa-angle-down"></i>
						  <i aria-hidden="true" class="fas fa-angle-up"></i>
						  <span>Table - ${table.no}</span>
					  </div>
					  <div class="rcn_vp-table-listing-head-right">
						  <i data-table-id="27770" aria-hidden="true" class="fas rcn-vp-table-added-to-cart-indicator fa-check"></i>
						  <small class="rcn_vp-table-status-checking">Checking</small>
						  <small class="rcn_vp-table-status-reserved">Reserved</small>
					  </div>
				  </div>
				  <div class="rcn_vp-table-listing-body">
					  <div class="rcn_vp-table-listing-body-description">
              <img
              src="https://realitycapturenetwork.com/wp-content/uploads/2024/02/Eclipse-1s-200px.gif" 
              width="30px"
              height="30px"
              />
            </div>
					  <div class="rcn_vp-table-listing-body-action">
						  <div>
                <img 
                class="rcn_vp-table-listing-body-action-spinner" 
                src="https://realitycapturenetwork.com/wp-content/uploads/2024/02/Eclipse-1s-200px.gif" 
                width="30px"
                height="30px"
                />
							  <button class="rcn_vp-table-listing-body-add-to-cart-btn">Add To Cart</button>
							  <div class="rcn_vp-table-listing-body-remove-cart-btn">
								  Remove from cart
							  </div>
							  <small class="rcn_vp-table-status-reserved">Reserved</small>
						  </div>
						  <span class="rcn_vp-table-listing-body-action-price"></span>
					  </div>
				  </div>
      </li>`
    );
  }

  tableListingContainer.ready(function () {
    $('.rcn_vp-table-listing').each(function () {
      rcn_tableActionHandler($, $(this));
    });
  });

  rcn_tableDataHandler($, tables, tableListingContainer);

  // Handles clicks on li tags
  tableListingWrapper.on('click', 'li', function () {
    rcn_handleClickOnLi($, $(this));
  });

  tableListingContainer.append(tableListingWrapper);
}

/**
 * Handles the availability status of tables and updates UI elements accordingly.
 *
 * This function checks the availability of each table through an AJAX request,
 * and based on the response, it updates the UI elements of the tables in the targetDiv.
 * It also considers the reservation and cart status to apply proper styling.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {Array} tables - Array of table objects with 'id' and 'no' properties.
 * @param {jQuery} tableListingContainer - jQuery selector for the target container.
 * @returns {void} - No return value.
 */
function rcn_tableDataHandler($, tables, tableListingContainer) {
  const rcn_ajaxurl = window.rcn_vpAjaxurl;

  for (let i = 0; i < tables.length; i++) {
    const table = tables[i];

    $.ajax({
      type: 'POST',
      url: rcn_ajaxurl,
      data: {
        action: 'rcn_vp_get_table_data',
        variation_id: table.id,
      },
      success: function (response) {
        availableTableHandler(response, table);
      },
      error: function (xhr, status, error) {
        console.log(xhr.responseText);
      },
    });
  }

  function availableTableHandler(response, table) {
    response = JSON.parse(response);
    const status = response.status;
    const tableElement = $(tableListingContainer).find('.table-' + table.no);
    let price = Number(response.price);
    price = '<strong>Price: </strong>' + rcn_vpPriceFormatter(price, 2);

    tableElement
      .find('.rcn_vp-table-listing-body-description')
      .html(response.description);

    tableElement
      .find('.rcn_vp-table-listing-body-action-price')
      .html(price);

    // If the table is already reserved
    if (!status) {
      tableElement.removeClass('rcn-vp-status-checking-table');
      tableElement.addClass('rcn-vp-unavailable-table');
      return;
    }

    tableElement.attr('data-is-unavailable', false);

    // If the table is in cart
    let cartItems = localStorage.getItem('rcn_vpCartData');
    cartItems = JSON.parse(cartItems);
    cartItems = cartItems ? cartItems.map((item) => item.id) : [];

    let isTableInCart = cartItems.includes(table.id);

    if (isTableInCart) {
      tableElement.removeClass('rcn-vp-status-checking-table');
      tableElement.addClass('rcn-vp-added-to-cart-table');
      return;
    }

    // If the table is available and not in cart
    if (status) {
      tableElement.removeClass('rcn-vp-status-checking-table');
      tableElement.addClass('rcn-vp-available-table');
      return;
    }
  }
}

/**
 * Handles the click event on a table item/li.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {jQuery} clickedItem - The jQuery object representing the clicked table item/li.
 * @returns {void} - The function doesn't return a value.
 */
function rcn_handleClickOnLi($, clickedItem) {
  const tableListingWrapper = $('.rcn-vp-table-wrapper-ul');
  const SelectedTableID = Number(clickedItem.attr('data-table-id'));

  tableListingWrapper
    .find('.rcn_vp-table-listing')
    .removeClass('rcn-vp-selected-table');
  clickedItem.addClass('rcn-vp-selected-table');

  rcn_vpTableLocationIndicatorHandler($, SelectedTableID);
}

/**
 * Handles the click event on table action icons and performs actions based on server response.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {jQuery} actionBtn - the table add to cart actionBtn.
 * @returns {void} - No return value.
 */
function rcn_tableActionHandler($, element) {
  const rcn_ajaxurl = window.rcn_vpAjaxurl;
  const productID = window.productID;
  const variationID = element.attr('data-table-id');
  const tableNo = element.attr('data-table-no');

  // Handle add to cart event
  $(element)
    .find('.rcn_vp-table-listing-body-add-to-cart-btn')
    .on('click', function () {
      let notification = {
        type: 'info',
        message: 'Adding item to cart',
      };
      rcn_vpCartVisualInteraction($, false, notification);

      $(this).hide();
      $(element).find('.rcn_vp-table-listing-body-action-spinner').show();

      addTableToCart();
    });

  // Handle remove from cart event
  $(element)
    .find('.rcn_vp-table-listing-body-remove-cart-btn')
    .click('click', function () {
      $(this).hide();
      $(element).find('.rcn_vp-table-listing-body-action-spinner').show();

      rcn_vpRemoveProductFromCart($, variationID);
    });

  function addTableToCart() {
    $.ajax({
      type: 'POST',
      url: rcn_ajaxurl,
      data: {
        action: 'rcn_table_add_to_cart_handler',
        product_id: productID,
        variation_id: variationID,
      },
      success: function (response) {
        tableAddToCartResponseHandler(response);
      },
      error: function (xhr, status, error) {
        notification = {
          type: 'info',
          message: 'Something went wrong. Please refresh the page.',
        };

        rcn_vpCartVisualInteraction($, false, notification);
        console.log(xhr.responseText);
      },
    });
  }

  function tableAddToCartResponseHandler(response) {
    $(element).find('.rcn_vp-table-listing-body-action-spinner').hide();
    $(element).find('.rcn_vp-table-listing-body-add-to-cart-btn').show();

    const result = JSON.parse(response);

    // 1002 means, the table is recently reserved by someone else
    if (result.availability_code === 1002) {
      element
        .removeClass('rcn-vp-available-table')
        .addClass('rcn-vp-unavailable-table');
    }

    // 1001 means, the table is available for reservation
    if (result.availability_code === 1001) {
      element
        .removeClass('rcn-vp-available-table')
        .addClass('rcn-vp-added-to-cart-table');

      const id = Number(variationID);
      const name = 'Table - ' + tableNo;
      const price = result.price;
      const quantity = 1;

      // prettier-ignore
      const productData = {
        id, name, price, quantity
      }

      rcn_addProductToCart($, 'updateCart', productData);

      rcn_vpTriggerCartOnTabletMobile($);
    }
  }
}

/**
 * Initializes table according boxes.
 *
 * @param {jQuery} $ - jQuery reference.
 * @returns {void} - No return value.
 */
function rcn_vpTableListingAccordingBox($) {

  $('.rcn_vp-table-listing-body').fadeOut();
  $('.rcn_vp-table-listing-head .fa-angle-up').hide();
  $('.rcn_vp-table-listing-body-action-spinner').hide();

  $('.rcn_vp-table-listing-head').on('click', function () {
    $(this).siblings('.rcn_vp-table-listing-body').toggle();
    $(this).find('.fa-angle-down').toggle();
    $(this).find('.fa-angle-up').toggle();
  });
}

/**
 * Highlight table locations on the floor plan image.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {jQuery} tableID - The table ID.
 * @returns {void} - No return value.
 */
function rcn_vpTableLocationIndicatorHandler($, tableID) {
  const activeFloorNumber = localStorage.getItem('rcn_activeFloorNumber');

  $('.rcn-vp-floor-' + activeFloorNumber)
    .find('.rcn-vp-table-location-indicator')
    .each(function () {
      $(this).show();

      let currentTableID = $(this).data('table-id');
      currentTableID = Number(currentTableID);

      if (currentTableID !== tableID) {
        $(this).hide();
      }
    });
}

/**
 * Configures the custom accordion box used for displaying vendor package add-ons.
 *
 * @param {jQuery selector} $ - jQuery reference.
 * @returns {void} - No return value.
 */
function rcn_vpAddonsAccording($) {
  $('.rcn-vpaa-angle-down').show();
  $('.rcn-vpaa-angle-up').hide();
  $('.rcn-vpaa-body').hide();
  $('.rcn-vpaa-oosi').hide();

  $('.rcn-vpaa-wrapper').each(function () {
    var wrapper = $(this);

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
 * Checks the status of vendor package addons and updates their display based on stock availability.
 *
 * @param {jQuery} $ - jQuery reference.
 */
function rcn_vpAddonsStatusChecker($) {
  const addonsIDs = [27798, 27800, 27801, 27802, 27803, 27804, 27797];

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
        statusChecker(response, uniqueIdentifier);
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        return false;
      },
    });
  }

  function statusChecker(response, uniqueIdentifier) {
    response = JSON.parse(response);
    const accordingWrapperClass = $('.' + uniqueIdentifier);

    if (response.status) {
      accordingWrapperClass.find('.rcn-vpaa-oosc').remove();
    }

    if (response.status) {
      accordingWrapperClass
        .find('.rcn-vpaa-oosc h2')
        .replaceWith(
          '<h2 class="elementor-heading-title elementor-size-default">Status check failed</h2>'
        );
    }

    if (response.status && response.stock_quantity === 0) {
      accordingWrapperClass.find('.rcn-vpaa-oosi').toggle();
      accordingWrapperClass.find('.rc-vpaa-quantity').remove();
    }
  }
}

/**
 * Handles the process of adding a vendor package addon to the cart using AJAX.
 *
 * @param {jQuery} $ - jQuery reference.
 * @param {number} productID - The ID of the addon product to be added to the cart.
 */
function rcn_vpAddOnsAddToCart($, productID) {
  productID = Number(productID);
  const ajaxurl = window.rcn_vpAjaxurl;
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
      const response = JSON.parse(responseData);
      addOnsAddedSuccessfully(response);
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
      // the product is already added to cart.
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

      rcn_vpCartVisualInteraction($, false, cartNotification);
      return;
    }

    const productData = {
      id: response.productID,
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
