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

        $methodCode = $payment->getMethodInstance()->getCode();

        if ($methodCode == 'avarda_payments_alternative_direct_invoice') {
            $methodCode = 'avarda_payments_direct_invoice';
        }

        return [
            self::ADDITIONAL => [
                'payment_method' => str_replace(['avarda_payments_', '_'], ['', '-'], $methodCode),
                'authorization_id' => $payment->getAdditionalInformation('authorization_id')
            ]
        ];
    }
}
