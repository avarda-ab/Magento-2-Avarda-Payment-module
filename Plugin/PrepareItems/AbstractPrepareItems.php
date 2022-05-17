<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Plugin\PrepareItems;

use Avarda\Payments\Helper\PaymentData;
use Avarda\Payments\Api\ItemStorageInterface;
use Avarda\Payments\Gateway\Request\Item\ItemAdapterFactory;
use Psr\Log\LoggerInterface;

abstract class AbstractPrepareItems
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var ItemStorageInterface */
    protected $itemStorage;

    /** @var ItemAdapterFactory */
    protected $itemAdapterFactory;

    /** @var PaymentData */
    protected $paymentDataHelper;

    /** @var bool[] */
    protected $collectTotalsFlag = [];

    public function __construct(
        LoggerInterface $logger,
        ItemStorageInterface $itemStorage,
        ItemAdapterFactory $itemAdapterFactory,
        PaymentData $paymentDataHelper
    ) {
        $this->logger = $logger;
        $this->itemStorage = $itemStorage;
        $this->itemAdapterFactory = $itemAdapterFactory;
        $this->paymentDataHelper = $paymentDataHelper;
    }

    abstract public function afterCollectTotals($subject, $result);

    abstract public function prepareItemStorage($subject);
}
