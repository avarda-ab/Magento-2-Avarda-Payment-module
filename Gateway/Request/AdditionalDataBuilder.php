<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

/**
 * Class OrderReferenceDataBuilder
 */
class AdditionalDataBuilder implements BuilderInterface
{
    /**
     * These data for GatewayClient to build the url correctly
     */
    const ADDITIONAL = 'additional';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        return [
            self::ADDITIONAL => [
                'payment_method' => str_replace(['avarda_payments_', '_'], ['', '-'], $payment->getMethodInstance()->getCode()),
                'authorization_id' => $payment->getAdditionalInformation('authorization_id')
            ]
        ];
    }
}
