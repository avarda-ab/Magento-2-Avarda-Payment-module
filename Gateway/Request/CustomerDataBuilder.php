<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CustomerDataBuilder
 */
class CustomerDataBuilder implements BuilderInterface
{
    /** @var string */
    const IDENTIFICATION_NUMBER = 'identificationNumber';

    /** @var int
     * 208 = Dk
     * 246 = Fi
     * 578 = No
     * 752 = Se
     * 826 = En (default)
     */
    const USER_LANGUAGE_CODE = 'userLanguageCode';

    /** The phone value must be less than or equal to 15 characters. */
    const PHONE = 'phone';

    /** The email value must be less than or equal to 60 characters. */
    const MAIL = 'email';

    /** @var Resolver */
    protected $localeResolver;

    public function __construct(
        Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        $billingAddress = $order->getBillingAddress();

        return ['customer' => [
                self::IDENTIFICATION_NUMBER => $payment->getAdditionalInformation('avarda_payments_ssn'),
                self::USER_LANGUAGE_CODE => $this->getUserLanguageCode(),
                self::PHONE => $billingAddress->getTelephone(),
                self::MAIL => $billingAddress->getEmail(),
            ]
        ];
    }

    public function getUserLanguageCode()
    {
        $recognizedLocales = [
            'dk' => 208,
            'fi' => 246,
            'no' => 578,
            'sv' => 752,
            'en' => 826
        ];

        $localeCode = $this->localeResolver->getLocale();
        $parts = explode('_', $localeCode);
        $firstPart = reset($parts);
        if (in_array($firstPart, array_keys($recognizedLocales))) {
            return $recognizedLocales[$firstPart];
        }

        // Use english as default
        return 826;
    }
}
