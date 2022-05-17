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
class ShippingDescriptionBuilder implements BuilderInterface
{
    /**
     * Shipping description
     */
    const DESCRIPTION = 'description';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [self::DESCRIPTION => ''];
    }
}
