<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Payments\Plugin\PrepareItems;

use Magento\Quote\Api\Data\CartInterface;

class QuoteCollectTotalsPrepareItems extends AbstractPrepareItems
{

    /**
     * @param CartInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollectTotals($subject, $result)
    {
        try {
            if (!array_key_exists(md5($subject->toJson()), $this->collectTotalsFlag)&&
                $subject->getItemsCount() > 0 &&
                $subject->getItems() !== null
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
     * @param CartInterface $subject
     *
     * @return void
     */
    public function prepareItemStorage($subject)
    {
        $this->itemStorage->reset();
        $this->prepareItems($subject);
        $this->prepareShipment($subject);
        $this->prepareGiftCards($subject);
    }

    /**
     * Create item data objects from quote items
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareItems(CartInterface $subject)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($subject->getItemsCollection() as $item) {
            if ($item->getData('product_id') === null ||
                $item->getData('parent_item_id') !== null ||
                $item->isDeleted()
            ) {
                continue;
            }

            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount());
            $itemAdapter->setDescription($item->getName());
            $itemAdapter->setTaxAmount($item->getBaseTaxAmount());
            $itemAdapter->setNotes($item->getSku());
            $itemAdapter->setTaxCode($item->getTaxPercent());

            $this->itemStorage->addItem($itemAdapter);
        }
    }

    /**
     * Create item data object from shipment information
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareShipment(CartInterface $subject)
    {
        if ($subject->isVirtual()) {
            return;
        }

        $shippingAddress = $subject->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getBaseShippingInclTax() > 0) {
            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($shippingAddress->getBaseShippingInclTax());
            $itemAdapter->setTaxAmount($shippingAddress->getBaseShippingTaxAmount());
            $itemAdapter->setDescription($shippingAddress->getShippingDescription());
            $itemAdapter->setNotes($shippingAddress->getShippingMethod());
            $this->itemStorage->addItem($itemAdapter);
        }
    }

    /**
     * Create item data object from gift card information
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareGiftCards(CartInterface $subject)
    {
        $giftCardsAmountUsed = $subject->getData('gift_cards_amount_used');
        if ($giftCardsAmountUsed !== null && $giftCardsAmountUsed > 0) {
            $itemAdapter = $this->itemAdapterFactory->create();
            $itemAdapter->setAmount($giftCardsAmountUsed * -1);
            $itemAdapter->setTaxAmount(0);
            $itemAdapter->setDescription(__('Gift Card'));
            $itemAdapter->setNotes('giftcard');
            $itemAdapter->setTaxCode(0);
            $this->itemStorage->addItem($itemAdapter);
        }
    }
}
