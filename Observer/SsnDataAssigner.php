<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class SsnDataAssigner extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        if (isset($additionalData['avarda_payments_ssn'])) {
            $payment = $this->readPaymentModelArgument($observer);
            $payment->setAdditionalInformation('avarda_payments_ssn', $additionalData['avarda_payments_ssn']);
        }
    }
}
