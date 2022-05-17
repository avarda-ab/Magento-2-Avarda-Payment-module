<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Avarda\Payments\Api\Data\PaymentDetailsInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class ExternalIdDataBuilder
 */
class ExternalIdDataBuilder implements BuilderInterface
{
    /**
     * The purchase ID (external ID) of request
     */
    const EXTERNAL_ID = 'ExternalId';

    /**
     * Helper for reading payment info instances, e.g. getting purchase ID
     * from quote payment.
     *
     * @var \Avarda\Payments\Helper\PaymentData
     */
    protected $paymentDataHelper;

    /**
     * ExternalIdDataBuilder constructor.
     *
     * @param \Avarda\Payments\Helper\PaymentData $paymentDataHelper
     */
    public function __construct(
        \Avarda\Payments\Helper\PaymentData $paymentDataHelper
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment   = $paymentDO->getPayment();

        $purchaseId = $this->paymentDataHelper->getAuthorizeId($payment);
        if (!$purchaseId) {
            return [];
        }

        return [self::EXTERNAL_ID => $purchaseId];
    }
}
