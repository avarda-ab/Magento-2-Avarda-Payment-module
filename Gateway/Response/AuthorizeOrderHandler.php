<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Response;

use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class InitializePaymentHandler
 */
class AuthorizeOrderHandler implements HandlerInterface
{
    /** @var Session */
    protected $checkoutSession;

    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /** @var string The authorization id */
    const AUTHORIZATION_ID = 'authorization_id';

    /** @var string if strong auth is needed there will be a redirect url also */
    const REDIRECT_URL = 'redirect_url';

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $stateObject = SubjectReader::readStateObject($handlingSubject);

        $authorizeId = $response['authorizationId'];
        $payment->setAdditionalInformation(self::AUTHORIZATION_ID, $authorizeId);
        $this->checkoutSession->setAvardaPaymentsAuthorizeId($authorizeId);
        if (isset($response['redirectUrl'])) {
            $payment->setAdditionalInformation(self::REDIRECT_URL, $response['redirectUrl']);
            $this->checkoutSession->setAvardaPaymentsRedirectUrl($response['redirectUrl']);
        }

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(true);
    }
}
