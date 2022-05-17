<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Observer;

use Avarda\Payments\Helper\AuthorizationStatus;
use Avarda\Payments\Helper\PaymentData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;

class OrderCancelObserver implements ObserverInterface
{
    /** @var PaymentData */
    protected $paymentDataHelper;

    /** @var CommandPoolInterface */
    protected $commandPool;

    /** @var PaymentDataObjectFactoryInterface */
    protected $paymentDataObjectFactory;

    /** @var AuthorizationStatus */
    protected $authorizationStatus;

    public function __construct(
        PaymentData $paymentDataHelper,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactoryInterface $paymentDataObjectFactory,
        AuthorizationStatus $authorizationStatus
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandPool = $commandPool;
        $this->authorizationStatus = $authorizationStatus;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $payment = $order->getPayment();
        if ($this->paymentDataHelper->isAvardaPayment($payment)) {
            $this->authorizationStatus->updateOrderStatus($order);

            $order->getPayment()->getAdditionalInformation();
            $additionalInformation = $order->getPayment()->getAdditionalInformation();

            // Only cancel online if status is approved
            if (!isset($additionalInformation['avarda_payments_status']) ||
                $additionalInformation['avarda_payments_status'] == AuthorizationStatus::STATE_APPROVED) {

                /** @var InfoInterface|null $payment */
                if ($payment !== null && $payment instanceof InfoInterface) {
                    $arguments['payment'] = $this->paymentDataObjectFactory
                        ->create($payment);
                }

                $arguments['amount'] = $payment->getAmountOrdered();
                $this->commandPool->get('avarda_cancel')->execute($arguments);
            }
        }
    }
}
