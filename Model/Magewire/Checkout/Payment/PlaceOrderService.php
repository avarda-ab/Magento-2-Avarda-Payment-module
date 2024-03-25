<?php

namespace Avarda\Payments\Model\Magewire\Checkout\Payment;

use Avarda\Payments\Api\RedirectInterface;
use Hyva\Checkout\Model\Magewire\Payment\AbstractOrderData;
use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;

class PlaceOrderService extends AbstractPlaceOrderService
{
    protected RedirectInterface $redirect;

    public function __construct(
        CartManagementInterface $cartManagement,
        RedirectInterface $redirect,
        AbstractOrderData $orderData = null
    ) {
        parent::__construct($cartManagement,$orderData);
        $this->redirect = $redirect;
    }

    public function canPlaceOrder(): bool
    {
        return true;
    }

    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        return $this->redirect->redirect();
    }
}
