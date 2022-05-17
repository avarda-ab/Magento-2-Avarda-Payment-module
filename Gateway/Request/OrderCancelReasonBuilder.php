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
class OrderCancelReasonBuilder implements BuilderInterface
{
    /**
     * Shipping description
     */
    const REASON = 'reason';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [self::REASON => 'Order canceled in Magento'];
    }
}
