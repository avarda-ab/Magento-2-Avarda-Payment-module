<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Payments\Api;

use Avarda\Payments\Api\Data\ItemAdapterInterface;

/**
 * Interface for storing Avarda item information
 * @api
 */
interface ItemStorageInterface
{
    /**
     * @param ItemAdapterInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * @param ItemAdapterInterface $item
     * @return $this
     */
    public function addItem(ItemAdapterInterface $item);

    /**
     * @return ItemAdapterInterface[]
     */
    public function getItems();

    /**
     * @return $this
     */
    public function reset();
}
