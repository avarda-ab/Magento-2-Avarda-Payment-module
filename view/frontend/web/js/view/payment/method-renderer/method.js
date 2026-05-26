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
    'Magento_Ui/js/modal/modal',
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
                this._super().observe('ssn');
                return this;
            },
            getInstructions: function () {
                if (window.checkoutConfig.payment.instructions[this.item.method].apr_widget.enabled) {
                    let s = document.createElement("script");
                    s.src = window.checkoutConfig.payment.instructions[this.item.method].apr_widget.url;
                    s.type = 'text/javascript';
                    s.dataset.paymentId = window.checkoutConfig.payment.instructions[this.item.method].apr_widget.paymentId;
                    s.dataset.widgetJwt = window.checkoutConfig.payment.instructions[this.item.method].apr_widget.widgetJwt;
                    s.dataset.customStyles = window.checkoutConfig.payment.instructions[this.item.method].apr_widget.styles;
                    document.head.appendChild(s);
                }
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
            getLoanWarning: function () {
                return window.checkoutConfig.payment.instructions[this.item.method]['loan_warning'];
            },
            placeOrder: function() {
                var self = this;
                if (!additionalValidators.validate()) {
                    return;
                }
                var inputId = 'avarda_ssn_input_' + self.getCode();
                var $content = $('#avarda-ssn-modal-' + self.getCode());
                $content.modal({
                        type: 'popup',
                        title: $t('Verification'),
                        buttons: [{
                            text: $t('Continue'),
                            class: 'action primary',
                            click: function () {
                                var ssn = $('#' + inputId).val();
                                if (!ssn) {
                                    return;
                                }
                                self.ssn(ssn);
                                this.closeModal();
                                self.submitOrder();
                            }
                        }]
                    })
                    .modal('openModal');
            },
            submitOrder: function() {
                var self = this;
                placeOrderAction(self.getData(), self.messageContainer).done(function () {
                    fullScreenLoader.startLoader();
                    self.getRestPaymentRedirectUrl().done(function (response) {
                        $.mage.redirect(response);
                    }).fail(function () {
                        fullScreenLoader.stopLoader();
                        self.addErrorMessage($t('An error occurred on the server. Please try to place the order again.'));
                    });
                });
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
        });
    }
);
