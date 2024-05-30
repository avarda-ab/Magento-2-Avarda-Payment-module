<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Avarda\Payments\Helper\PaymentData;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TranIdDataBuilder
 */
class TranIdDataBuilder implements BuilderInterface
{
    /**
     * A unique transaction ID
     */
    const TRAN_ID = 'tranId';

    protected PaymentData $paymentDataHelper;

    public function __construct(
        PaymentData $paymentDataHelper
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO     = SubjectReader::readPayment($buildSubject);
        $transactionId = $this->paymentDataHelper->getTransactionId();
        $paymentDO->getPayment()->setTransactionId($transactionId);

        return [self::TRAN_ID => $transactionId];
    }
}
