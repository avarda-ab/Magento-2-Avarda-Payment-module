<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Payments\Plugin\PrepareItems;

use Magento\Sales\Api\Data\CreditmemoInterface;

class CreditmemoCollectTotalsPrepareItems extends AbstractPrepareItems
{
    /**
     * @param CreditmemoInterface $subject
     * @param CreditmemoInterface $result
     * @return CreditmemoInterface
     */
    public function afterCollectTotals($subject, $result)
    {
        try {
            $payment = $subject->getOrder()->getPayment();
            if (!array_key_exists(md5($subject->toJson()), $this->collectTotalsFlag) &&
                $this->paymentDataHelper->isAvardaPayment($payment)
            ) {
                $this->prepareItemStorage($subject);
                $this->collectTotalsFlag[md5($subject->toJson())] = true;
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $result;
    }

    /**
     * Populate the item storage with Avarda items needed for request building
     *
     * @param CreditmemoInterface $subject
     */
    public function prepareItemStorage($subject)
    {
        $this->itemStorage->reset();
        $this->prepareItems($subject);
        $this->prepareShipment($subject);
        $this->prepareGiftCards($subject);
    }

    /**
     * Create item data objects from invoice items
     *
     * @param CreditmemoInterface|\Magento\Sales\Model\Order\Creditmemo $subject
     */
    protected function prepareItems(CreditmemoInterface $subject)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($subject->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            if (!$orderItem->getProductId() ||
                $orderItem->getData('parent_item_id') !== null ||
                $item->isDeleted()
            ) {
                continue;
            }

            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount());
            $itemAdapter->setDescription($item->getName());
            $itemAdapter->setTaxAmount($item->getBaseTaxAmount());
            $itemAdapter->setNotes($item->getSku());
            $itemAdapter->setTaxCode($orderItem->getTaxPercent());

            $this->itemStorage->addItem($itemAdapter);
        }
    }

    /**
     * Create item data object from shipment information
     *
     * @param CreditmemoInterface|\Magento\Sales\Model\Order\Creditmemo $subject
     */
    protected function prepareShipment(CreditmemoInterface $subject)
    {
        $shippingAmount = $subject->getBaseShippingAmount();
        if ($shippingAmount > 0) {
            $order = $subject->getOrder();
            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($shippingAmount);
            $itemAdapter->setTaxAmount($subject->getBaseShippingTaxAmount());
            $itemAdapter->setDescription($order->getShippingDescription());
            $itemAdapter->setNotes($order->getShippingMethod());
            $this->itemStorage->addItem($itemAdapter);
        }
    }

    /**
     * Create item data object from gift card information
     *
     * @param CreditmemoInterface|\Magento\Sales\Model\Order\Creditmemo $subject
     */
    protected function prepareGiftCards(CreditmemoInterface $subject)
    {
        $giftCardsAmount = $subject->getData('gift_cards_amount');
        if ($giftCardsAmount !== null && $giftCardsAmount > 0) {
            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($giftCardsAmount * -1);
            $itemAdapter->setTaxAmount(0);
            $itemAdapter->setDescription(__('Gift Card'));
            $itemAdapter->setNotes('giftcard');
            $itemAdapter->setTaxCode(0);
            $this->itemStorage->addItem($itemAdapter);
        }
    }
}
