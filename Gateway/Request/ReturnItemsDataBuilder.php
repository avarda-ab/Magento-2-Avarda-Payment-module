<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */

namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment;

class ReturnItemsDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        /** @var Creditmemo $creditMemo */
        $creditMemo = $payment->getCreditmemo();

        $items['Items'] = [[
            "description" => "Return",
            "notes" => "Return from Magento",
            "amount" => $this->formatPrice(round($creditMemo->getBaseGrandTotal(), 2)),
            "taxCode" => "0.00",
            "taxAmount" => $this->formatPrice(round($creditMemo->getBaseTaxAmount(), 2)),
            "quantity" => 1
        ]];

        return $items;
    }
}
