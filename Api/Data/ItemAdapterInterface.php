<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Api\Data;

interface ItemAdapterInterface
{
    /** String (max. 35 characters) */
    const DESCRIPTION = 'description';

    /** String (max. 35 characters) */
    const NOTES = 'notes';

    /** @var float decimal */
    const AMOUNT = 'amount';

    /** @var string tax percent */
    const TAX_CODE = 'taxCode';

    /** @var float decimal */
    const TAX_AMOUNT = 'taxAmount';

    /** @var string ?? */
    const PRODUCT_GROUP = 'productGroup';

    public function getDescription();

    public function setDescription($description);

    public function getNotes();

    public function setNotes($notes);

    public function getAmount();

    public function setAmount($amount);

    public function getTaxCode();

    public function setTaxCode($taxCode);

    public function getTaxAmount();

    public function setTaxAmount($taxAmount);

    public function getProductGroup();

    public function setProductGroup($productGroup);
}
