<?php

namespace Avarda\Payments\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AccountClassCodeDataBuilder implements BuilderInterface
{

    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    const ACCOUNT_CLASS_CODE = 'accountClassCode';

    public function build(array $buildSubject)
    {
        $payment = SubjectReader::readPayment($buildSubject)->getPayment();

        $accountClassCode = $this->scopeConfig->getValue('payment/' . $payment->getMethod() . '/account_class_code');

        return [self::ACCOUNT_CLASS_CODE => $accountClassCode];
    }
}
