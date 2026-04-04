document.addEventListener('livewire:load', function () {
  const originalInitCheckoutNavigation = window.initCheckoutNavigation;

  window.initCheckoutNavigation = function () {
    const checkoutNavigation = originalInitCheckoutNavigation();

    checkoutNavigation.onClick = function (event) {
      const button = this.getButton(event);
      const stepData = JSON.parse(button && button.dataset && button.dataset.step || '{}');
      if (stepData.place) {
        if (document.getElementById('payment-method-bitrail')?.checked) {
          const bitrailData = window.checkoutConfig.payment.bitrail_gateway;
          paymentComponent(bitrailData).openModal();
        }
        else {
          hyvaCheckout.order.place();
        }
      } else {
        hyvaCheckout.navigation.stepTo(stepData.route, stepData.validate);
      }
    };

    return checkoutNavigation;
  };
}
);

function paymentComponent(bitrailData) {
  const modal = document.getElementById("paymentModal");
  const modalTtile = document.getElementById("paymentModalTitle");
  const modalCloseButtons = document.querySelectorAll(".close-modal");
  modalTtile.innerHTML = bitrailData.paymentMethodTitle;

  return {
    async openModal() {
      showLoadingSpinner();
      const orderCreated = await this.getQuoteDetail();
      if (orderCreated) {
        this.showModal();
        this.initializePayment();
      }
      hideLoadingSpinner();
    },
    showModal() {
      modalCloseButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          this.closeModal();
        });
      });
      modal.style.display = "flex";
    },

    closeModal() {
      modal.style.display = "none";
    },

    async getQuoteDetail() {
      const { success, data, error } = await fetchQuoteDetail(bitrailData.nonceCode);
      if (success) {
        bitrailData = { ...bitrailData, ...data };
        window.checkoutConfig = {
          ...window.checkoutConfig ?? {},
          payment: {
            ...window.checkoutConfig?.payment ?? {},
            bitrail_gateway: {
              ...window.checkoutConfig?.payment?.bitrail_gateway ?? {},
              ...data
            }
          }
        }
        await loadVendorJsScript();
        return true
      }
      console.log(error);
      return false;
    },

    initializePayment() {
      const iframe = document.getElementById("ordersmodal");
      window.BitRail.init(bitrailData.authToken, {
        api_url: bitrailData.apiUrl,
        parent_element: iframe,
        frame_attributes: { style: null },
      });

      window.BitRail.order(
        bitrailData.orderToken,
        bitrailData.destinationVaultHandle,
        Number(bitrailData.grandTotal).toFixed(2),
        'USD',
        bitrailData.description,
        this.prepareOrderInfo(),
        this.orderCallback.bind(this)
      );
    },

    orderCallback(response) {
      switch (response.status) {
        case 'success':
          console.log("payment successful");
          this.completeOrder(response.verification_token);
          break;
        case 'failed':
          console.log('Payment failed. Please try again.');
          break;
        case 'cancelled':
          console.log('Payment cancelled');
          break;
        default:
          console.log('Unexpected response:', response);
      }
    },

    async completeOrder(verificationToken) {
      const { success, error } = await registerPayment(bitrailData.orderNumber, verificationToken)
      if (success) {
        hyvaCheckout.order.place();
      }
      else {
        console.error("Error processing request: ", error)
      }

      this.closeModal();
      showLoadingSpinner();
    },

    prepareOrderInfo() {
      return {
        OrderID: bitrailData.orderNumber,
        Customer: `${bitrailData.customerFirstName} ${bitrailData.customerLastName}`,
        Email: bitrailData.customerEmail,
        Shipping: this.formatAddress(bitrailData.shippingAddress),
        Billing: this.formatAddress(bitrailData.billingAddress),
      };
    },

    formatAddress(address) {
      return `${address.firstname} ${address.lastname}, ${address.street}, ${address.city}, ${address.region}, ${address.postcode}, ${address.countryId}`;
    }
  };
}

