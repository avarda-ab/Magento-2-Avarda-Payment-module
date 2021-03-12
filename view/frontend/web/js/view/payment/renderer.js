/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'avarda_payments_invoice',
                component: 'Avarda_Payments/js/view/payment/method-renderer/method'
            },
            {
                type: 'avarda_payments_direct_invoice',
                component: 'Avarda_Payments/js/view/payment/method-renderer/method'
            },
            {
                type: 'avarda_payments_loan',
                component: 'Avarda_Payments/js/view/payment/method-renderer/method'
            },
            {
                type: 'avarda_payments_partpayment',
                component: 'Avarda_Payments/js/view/payment/method-renderer/method'
            },
        );

        return Component.extend({});
    }
);
