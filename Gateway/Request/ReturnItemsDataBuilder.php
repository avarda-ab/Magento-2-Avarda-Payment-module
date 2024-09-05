<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Checkout3
 */

namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment;

class ReturnItemsDataBuilder implements BuilderInterface
{
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
            "amount" => $creditMemo->getBaseGrandTotal(),
            "taxCode" => "0.00",
            "taxAmount" => $creditMemo->getBaseTaxAmount(),
            "quantity" => 1
        ]];

        return $items;
    }
}
