<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Observer;

use Avarda\Payments\Helper\PaymentData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class OrderPlaceAfter implements ObserverInterface
{
    /** @var PaymentData */
    protected $paymentDataHelper;

    /** @var OrderResource */
    protected $orderResource;

    /** @var CartRepositoryInterface */
    protected $cartRepository;

    public function __construct(
        PaymentData $paymentDataHelper,
        OrderResource $orderResource,
        CartRepositoryInterface $cartRepository
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
        $this->orderResource = $orderResource;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $payment = $order->getPayment();
        if ($this->paymentDataHelper->isAvardaPayment($payment)) {
            // remove ssn from payment information
            $payment->unsAdditionalInformation('avarda_payments_ssn');
            // remove ssn also from quote
            $quote = $this->cartRepository->get($order->getQuoteId());
            $quote->getPayment()->unsAdditionalInformation('avarda_payments_ssn');

            $additionalInfo = $payment->getAdditionalInformation();
            $order->setData('avarda_payments_authorize_id', $additionalInfo['authorization_id']);
            $this->orderResource->saveAttribute($order, 'avarda_payments_authorize_id');
        }
    }
}
