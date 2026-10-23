<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Avarda\Payments\Api\ItemStorageInterface;
use Avarda\Payments\Gateway\Request\Item\ItemAdapterFactory;
use Avarda\Payments\Gateway\Request\Item\ProductDataBuilder;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

class ItemsDataBuilder implements BuilderInterface
{
    use Formatter;

    const ITEMS = 'items';

    protected ProductDataBuilder $itemBuilder;
    protected ItemAdapterFactory $itemAdapterFactory;
    protected ItemStorageInterface $itemStorage;

    public function __construct(
        ProductDataBuilder $itemBuilder,
        ItemAdapterFactory $itemAdapterFactory,
        ItemStorageInterface $itemStorage
    ) {
        $this->itemBuilder = $itemBuilder;
        $this->itemAdapterFactory = $itemAdapterFactory;
        $this->itemStorage = $itemStorage;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $items = [];
        foreach ($this->itemStorage->getItems() as  $item) {
            $items[] = $this->itemBuilder->build($item);
        }

        $roundingItem = $this->buildRoundingItem($items, $buildSubject);
        if ($roundingItem !== null) {
            $items[] = $roundingItem;
        }

        return [self::ITEMS => $items];
    }

    public function buildRoundingItem(array $items, array $buildSubject): ?array
    {
        $targetTotal = $this->getTargetTotal($buildSubject);
        if ($targetTotal === null) {
            return null;
        }

        $itemTotalCents = 0;
        foreach ($items as $item) {
            $itemTotalCents += (int) round((float) $item[ProductDataBuilder::AMOUNT] * 100);
        }

        $diffCents = (int) round($targetTotal * 100) - $itemTotalCents;
        // Rounding drift is at most a cent per line
        if ($diffCents === 0 || abs($diffCents) > count($items)) {
            return null;
        }

        $itemAdapter = $this->itemAdapterFactory->create();
        $itemAdapter->setAmount($diffCents / 100);
        $itemAdapter->setTaxAmount(0);
        $itemAdapter->setDescription(__('Rounding'));
        $itemAdapter->setNotes('rounding');
        $itemAdapter->setTaxCode(0);

        return $this->itemBuilder->build($itemAdapter);
    }

    public function getTargetTotal(array $buildSubject): ?float
    {
        if (isset($buildSubject['amount'])) {
            return (float) SubjectReader::readAmount($buildSubject);
        }

        if (!isset($buildSubject['payment'])) {
            return null;
        }

        $grandTotal = SubjectReader::readPayment($buildSubject)->getOrder()->getGrandTotalAmount();

        return $grandTotal === null ? null : (float) $grandTotal;
    }
}
