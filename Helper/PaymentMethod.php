<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */

namespace Avarda\Payments\Helper;

use Avarda\Payments\Model\Ui\AlternativeDirectInvoice\ConfigProvider as AlternativeDirectInvoiceConfigProvider;
use Avarda\Payments\Model\Ui\DirectInvoice\ConfigProvider as DirectInvoiceConfigProvider;
use Avarda\Payments\Model\Ui\Invoice\ConfigProvider as InvoiceConfigProvider;
use Avarda\Payments\Model\Ui\Loan\ConfigProvider as LoanConfigProvider;
use Avarda\Payments\Model\Ui\PartPayment\ConfigProvider as PartPaymentConfigProvider;

class PaymentMethod
{
    const ALTERNATIVE_DIRECT_INVOICE = "DirectInvoice";
    const DIRECT_INVOICE = "DirectInvoice";
    const INVOICE = "Invoice";
    const LOAN = "Loan";
    const PART_PAYMENT = "PartPayment";
    const UNKNOWN = "Unknown";

    /**
     * PaymentMethod payment codes
     *
     * @var array
     */
    public static $codes = [
        AlternativeDirectInvoiceConfigProvider::CODE => self::ALTERNATIVE_DIRECT_INVOICE,
        DirectInvoiceConfigProvider::CODE => self::DIRECT_INVOICE,
        InvoiceConfigProvider::CODE       => self::INVOICE,
        LoanConfigProvider::CODE          => self::LOAN,
        PartPaymentConfigProvider::CODE   => self::PART_PAYMENT,
    ];

    /**
     * Get Avarda payment method code for Magento code
     *
     * @param int $paymentMethod
     * @return string
     */
    public function getPaymentMethod($paymentMethod)
    {
        if (array_key_exists($paymentMethod, self::$codes)) {
            return self::$codes[$paymentMethod];
        }

        return self::$codes[self::UNKNOWN];
    }
}
