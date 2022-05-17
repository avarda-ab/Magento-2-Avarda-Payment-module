<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Model;

use Avarda\Payments\Api\RedirectInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;

class Redirect implements RedirectInterface
{
    /** @var Session */
    protected $checkoutSession;

    /** @var UrlInterface */
    protected $url;

    public function __construct(
        Session $checkoutSession,
        UrlInterface $url
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->url = $url;
    }

    /**
     * Returns redirect URL if strong auth is needed
     *
     * @return string redirect URL
     * @api
     */
    public function redirect()
    {
        $url = $this->checkoutSession->getAvardaPaymentsRedirectUrl();
        if ($url) {
            return $url;
        }

        return $this->url->getUrl(
            'avarda_payments/redirect',
            ['_query' => [
                'authorizationId' => $this->checkoutSession->getAvardaPaymentsAuthorizeId()
            ]]
        );
    }
}
