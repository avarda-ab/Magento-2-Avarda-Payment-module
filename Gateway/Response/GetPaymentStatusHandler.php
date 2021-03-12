<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class GetPaymentStatusHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        // still waiting for strong auth
        $status = 99;
        if (isset($response['status'])) {
            $status = $response['status'];
        }

        $payment->setAdditionalInformation('avarda_payments_status', $status);
    }
}
