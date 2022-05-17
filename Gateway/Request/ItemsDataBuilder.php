<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Avarda\Payments\Api\ItemStorageInterface;
use Avarda\Payments\Gateway\Request\Item\ItemAdapterFactory;
use Avarda\Payments\Gateway\Request\Item\ProductDataBuilder;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

class ItemsDataBuilder implements BuilderInterface
{
    use Formatter;

    const ITEMS = 'items';

    /** @var ProductDataBuilder */
    protected $itemBuilder;

    /** @var ItemAdapterFactory */
    protected $itemAdapterFactory;

    /** @var ItemStorageInterface */
    protected $itemStorage;

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
        return [self::ITEMS => $items];
    }
}
