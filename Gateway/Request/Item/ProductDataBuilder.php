<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request\Item;

use Avarda\Payments\Api\Data\ItemAdapterInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class ProductDataBuilder
 */
class ProductDataBuilder
{
    use Formatter;

    /** String (max. 35 characters) */
    const DESCRIPTION = 'description';

    /** String (max. 35 characters) */
    const NOTES = 'notes';

    /** @var float decimal */
    const AMOUNT = 'amount';

    /** @var string tax percent */
    const TAX_CODE = 'taxCode';

    /** @var string decimal */
    const TAX_AMOUNT = 'taxAmount';

    /** @var string ?? */
    const PRODUCT_GROUP = 'productGroup';

    /**
     * @inheritdoc
     */
    public function build(ItemAdapterInterface $item)
    {
        return [
            self::DESCRIPTION => mb_substr($item->getDescription(), 0, 35),
            self::NOTES => mb_substr($item->getNotes(), 0, 35),
            self::AMOUNT => $this->formatPrice($item->getAmount()),
            self::TAX_CODE => $item->getTaxCode(),
            self::TAX_AMOUNT => $this->formatPrice($item->getTaxAmount()),
            self::PRODUCT_GROUP => $item->getProductGroup(),
        ];
    }
}
