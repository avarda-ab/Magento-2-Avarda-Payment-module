<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Payments\Model;

use Avarda\Payments\Api\ItemStorageInterface;
use Avarda\Payments\Api\Data\ItemAdapterInterface;

class ItemStorage implements ItemStorageInterface
{
    /**
     * @var ItemAdapterInterface[]
     */
    protected $items;

    /**
     * {@inheritdoc}
     */
    public function setItems($items)
    {
        if ($items !== null) {
            $this->items = $items;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(ItemAdapterInterface $item)
    {
        $this->items = array_merge(
            $this->getItems(),
            [$item]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (isset($this->items)) {
            return $this->items;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->items = [];

        return $this;
    }
}
