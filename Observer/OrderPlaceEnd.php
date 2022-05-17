<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Observer;

use Avarda\Payments\Helper\PaymentData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class OrderPlaceEnd implements ObserverInterface
{
    /** @var PaymentData */
    protected $paymentDataHelper;

    public function __construct(
        PaymentData $paymentDataHelper
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
    }

    public function execute(Observer $observer)
    {
        /** @var Order\Payment $order */
        $payment = $observer->getEvent()->getData('payment');
        if ($this->paymentDataHelper->isAvardaPayment($payment) &&
            $payment->getOrder()->getState() == Order::STATE_PENDING_PAYMENT) {
            // prevent email sending before authorization is completed
            $payment->getOrder()->setCanSendNewEmailFlag(false);
        }
    }
}
