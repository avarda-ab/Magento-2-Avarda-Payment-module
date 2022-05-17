<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class OrderReferenceDataBuilder
 */
class OrderReferenceDataBuilder implements BuilderInterface
{
    /**
     * A unique identifier for Avarda to find the order
     */
    const ORDER_REFERENCE = 'orderReference';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order     = $paymentDO->getOrder();

        return [self::ORDER_REFERENCE => $order->getOrderIncrementId()];
    }
}
