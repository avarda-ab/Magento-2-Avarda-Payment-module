<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request\Item;

use Avarda\Payments\Api\Data\ItemAdapterInterface;

class ItemAdapter implements ItemAdapterInterface
{
    /** String (max. 35 characters) */
    protected $description;

    /** String (max. 35 characters) */
    protected $notes;

    /** @var float decimal */
    protected $amount;

    /** @var string tax percent */
    protected $taxCode;

    /** @var float decimal */
    protected $taxAmount;

    /** @var string ?? */
    protected $productGroup;

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * @param string $taxCode
     */
    public function setTaxCode($taxCode): void
    {
        $this->taxCode = $taxCode;
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param float $taxAmount
     */
    public function setTaxAmount($taxAmount): void
    {
        $this->taxAmount = $taxAmount;
    }

    /**
     * @return string
     */
    public function getProductGroup()
    {
        return $this->productGroup;
    }

    /**
     * @param string $productGroup
     */
    public function setProductGroup($productGroup): void
    {
        $this->productGroup = $productGroup;
    }
}
