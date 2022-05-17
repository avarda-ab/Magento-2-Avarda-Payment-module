/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/url-builder',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate',
    'mage/validation'
],
    function (ko, $, Component, placeOrderAction, additionalValidators, urlBuilder, mageUrlBuilder, fullScreenLoader, $t) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Avarda_Payments/payment',
                ssn: ''
            },
            initObservable: function () {
                this._super()
                    .observe('ssn');
                return this;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method]['instructions'];
            },
            getImageSrc: function () {
                return window.checkoutConfig.payment.instructions[this.item.method]['image'];
            },
            getTermsText: function () {
                return window.checkoutConfig.payment.instructions[this.item.method]['terms_text'];
            },
            getTermsLink: function () {
                return window.checkoutConfig.payment.instructions[this.item.method]['terms_link'];
            },
            placeOrder: function() {
                var self = this;
                if (self.validate() && additionalValidators.validate()) {
                    placeOrderAction(self.getData(), self.messageContainer).done(function () {
                        fullScreenLoader.startLoader();

                        self.getRestPaymentRedirectUrl().done(function(response) {
                            $.mage.redirect(response);
                        }).fail(function() {
                            fullScreenLoader.stopLoader();
                            self.addErrorMessage($t('An error occurred on the server. Please try to place the order again.'));
                        });
                    });
                }
            },
            getRestPaymentRedirectUrl: function() {
                var serviceUrl = 'rest/V1/avardapayment/redirect';
                var completeUrl = mageUrlBuilder.build(serviceUrl);
                return $.ajax(completeUrl, {cache: false});
            },
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'avarda_payments_ssn': this.ssn()
                    }
                };
            },
            validate: function() {
                var form = '#' + this.getCode() + '_form';
                return $(form).validation() && $(form).validation('isValid') && this.ssn();
            },
        });
    }
);
