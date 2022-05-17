<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Avarda\Payments\Model\Ui\Loan\ConfigProvider as LoanConfigProvider;
use Avarda\Payments\Model\Ui\PartPayment\ConfigProvider as PartPaymentConfigProvider;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class PaymentTermsDataBuilder implements BuilderInterface
{
    /**
     * A unique transaction ID
     */
    const PAYMENT_TERMS = 'paymentTerms';

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $paymentMethod = $paymentDO->getPayment()->getMethodInstance()->getCode();

        if (in_array($paymentMethod, [LoanConfigProvider::CODE, PartPaymentConfigProvider::CODE])) {
            return [self::PAYMENT_TERMS => '36'];
        } else {
            return [];
        }
    }
}
