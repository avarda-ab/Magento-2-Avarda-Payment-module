<?php

namespace Avarda\Payments\Model\Magewire\Checkout\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Hyva\Checkout\Model\Magewire\Payment\PlaceOrderServiceProcessor;
use Magento\Checkout\Model\Session as SessionCheckout;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magewirephp\Magewire\Component;
use Rakit\Validation\Validator;

class AvardaPayments extends Component\Form implements EvaluationInterface
{
    public ?string $socialSecurityNumber = null;

    protected $loader = [
        'socialSecurityNumber' => 'Saving SSN',
    ];

    protected $rules = [
        'socialSecurityNumber' => 'required',
    ];

    protected $messages = [
        'socialSecurityNumber:required' => 'The SSN is a required field.',
    ];

    protected SessionCheckout $sessionCheckout;

    protected CartRepositoryInterface $quoteRepository;

    protected CartManagementInterface $quoteManagement;

    protected CartRepositoryInterface $cartRepository;

    protected PlaceOrderServiceProcessor $placeOrderServiceProcessor;

    public function __construct(
        Validator $validator,
        SessionCheckout $sessionCheckout,
        CartRepositoryInterface $quoteRepository,
        QuoteRepositoryInterface $cartRepository,
        QuoteManagement $quoteManagement,
        PlaceOrderServiceProcessor $placeOrderServiceProcessor
    ) {
        parent::__construct($validator);

        $this->sessionCheckout = $sessionCheckout;
        $this->quoteRepository = $quoteRepository;
        $this->cartRepository = $cartRepository;
        $this->quoteManagement = $quoteManagement;
        $this->placeOrderServiceProcessor = $placeOrderServiceProcessor;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $additionalData = $this->sessionCheckout->getQuote()->getPayment()->getAdditionalInformation('avarda_payments_ssn');
        $this->socialSecurityNumber = $additionalData ?? null;
    }

    /**
     * Listen for the Purchase Order Number been updated.
     */
    public function updatedSocialSecurityNumber(string $value): ?string
    {
        $value = empty($value) ? null : $value;

        try {
            $quote = $this->sessionCheckout->getQuote();
            $quote->getPayment()->setAdditionalInformation('avarda_payments_ssn', $value);

            $this->quoteRepository->save($quote);
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage($exception->getMessage());
        }

        return $value;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        if ($this->socialSecurityNumber === null) {
            return $resultFactory->createErrorMessageEvent()
                ->withCustomEvent('payment:method:error')
                ->withMessage('Social security number is a required field.');
        }

        return $resultFactory->createSuccess();
    }
}
