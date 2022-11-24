<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */

namespace Avarda\Payments\Helper;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Model\InfoInterface;

class AuthorizationStatus
{
    const STATE_APPROVED = 0;
    const STATE_REJECTED = 1;
    const STATE_CANCELED = 2;
    const STATE_FAILED = 3;
    const STATE_WAITING = 99;

    /**
     * PurchaseState description
     *
     * @var array
     */
    public static $descriptions = [
        self::STATE_APPROVED => 'Approved',
        self::STATE_REJECTED => 'Authorization has been rejected',
        self::STATE_CANCELED => 'Authorization has been canceled',
        self::STATE_FAILED   => 'Authorization has been failed',
        self::STATE_WAITING  => 'Still waiting for strong auth',
    ];

    /** @var CommandPoolInterface */
    protected $commandPool;

    /** @var PaymentDataObjectFactoryInterface */
    protected $paymentDataObjectFactory;

    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactoryInterface $paymentDataObjectFactory
    ) {
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    public function getStateText($statusId)
    {
        return self::$descriptions[$statusId];
    }

    public function updateOrderStatus($order)
    {
        /** @var InfoInterface|null $payment */
        $payment = $order->getPayment();
        $arguments = [];
        if ($payment !== null && $payment instanceof InfoInterface) {
            $arguments['payment'] = $this->paymentDataObjectFactory
                ->create($payment);
        }

        $this->commandPool
            ->get('avarda_get_payment_status')
            ->execute($arguments);
    }
}
