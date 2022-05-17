<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Helper;

use Avarda\Payments\Api\Data\PaymentDetailsInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class PaymentData
 */
class PaymentData
{
    /**
     * Payment additional information field name for state ID
     */
    const STATE_ID = 'authorization_id';

    /**
     * Get purchase ID from payment info
     *
     * @param InfoInterface $payment
     * @return string|bool
     */
    public function getAuthorizeId(InfoInterface $payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        if (is_array($additionalInformation) &&
            array_key_exists(
                'authorization_id',
                $additionalInformation
            )
        ) {
            return $additionalInformation['authorization_id'];
        }

        return false;
    }

    /**
     * Get state ID from payment info
     *
     * @param InfoInterface $payment
     * @return int
     */
    public function getStateId(InfoInterface $payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        if (is_array($additionalInformation) &&
            array_key_exists('authorization_id', $additionalInformation)
        ) {
            $stateId = $additionalInformation['authorization_id'];
            return $stateId;
        }
    }

    /**
     * Check if payment is an Avarda payment, simply by searching for the authorize_id
     *
     * @param InfoInterface $payment
     * @return bool
     */
    public function isAvardaPayment(InfoInterface $payment)
    {
        $paymentCode = '';
        try {
            $paymentCode = $payment->getMethod();
        } catch (\Exception $e) {
            // pass
        }

        return is_string($this->getAuthorizeId($payment)) && ($paymentCode == '' || strpos($paymentCode, 'avarda_payments') !== false);
    }

    /**
     * Generate a GUID v4 transaction ID
     *
     * @see http://php.net/manual/en/function.com-create-guid.php
     * @return string
     */
    public function getTransactionId()
    {
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}
