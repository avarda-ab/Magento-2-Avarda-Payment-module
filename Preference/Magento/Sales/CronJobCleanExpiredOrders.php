<?php

namespace Avarda\Payments\Preference\Magento\Sales;

use Avarda\Payments\Helper\AuthorizationStatus;
use Avarda\Payments\Helper\PaymentData;
use Exception;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\CronJob\CleanExpiredOrders;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoresConfig;
use Psr\Log\LoggerInterface;

class CronJobCleanExpiredOrders extends CleanExpiredOrders
{
    protected OrderManagementInterface $orderManagement;
    protected OrderRepositoryInterface $orderRepository;
    protected AuthorizationStatus $authorizationStatus;
    protected PaymentData $paymentData;
    protected LoggerInterface $logger;

    public function __construct(
        StoresConfig $storesConfig,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        AuthorizationStatus $authorizationStatus,
        PaymentData $paymentData,
        LoggerInterface $logger
    ) {
        parent::__construct($storesConfig, $collectionFactory, $orderManagement);
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->authorizationStatus = $authorizationStatus;
        $this->paymentData = $paymentData;
        $this->logger = $logger;
    }

    /**
     * Override to check the status from Avarda before canceling
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('sales/orders/delete_pending_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT);
            $orders->getSelect()->where(
                new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `updated_at`)) >= ' . $lifetime * 60)
            );

            foreach ($orders->getAllIds() as $entityId) {
                try {
                    $order = clone $this->orderRepository->get($entityId);

                    if ($this->paymentData->isAvardaPayment($order->getPayment())) {
                        $this->authorizationStatus->updateOrderStatus($order);
                        $additionalInformation = $order->getPayment()->getAdditionalInformation();
                        $status = $additionalInformation['avarda_payments_status'] ?? null;

                        // If the authorization is approved, don't cancel the order
                        if ($status === AuthorizationStatus::STATE_APPROVED) {
                            continue;
                        }
                    }

                    $this->orderManagement->cancel((int) $entityId);
                } catch (Exception $e) {
                    $this->logger->warning('Failed to process order ' . ($entityId) . ': ' . $e->getMessage());
                }
            }
        }
    }
}
