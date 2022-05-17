<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Url;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;

class InstructionsConfigProvider implements ConfigProviderInterface
{
    protected $methodCode;

    /** @var array */
    protected $methodInstance = [];

    /** @var Escaper */
    protected $escaper;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var Url */
    protected $url;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        Url $url,
        $methodCode = ''
    ) {
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->url = $url;
        $this->methodInstance = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        if ($this->methodInstance->isAvailable()) {
            $config['payment']['instructions'][$this->methodCode] = [
                'instructions' => $this->getInstructions($this->methodCode),
                'image' => $this->getImageSrc($this->methodCode),
                'terms_text' => $this->getConfigValue($this->methodCode, 'terms_text'),
                'terms_link' => $this->getConfigValue($this->methodCode, 'terms_link'),
            ];
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->scopeConfig->getValue('payment/' . $code . '/instructions', ScopeInterface::SCOPE_STORE)));
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getConfigValue($code, $conf)
    {
        return nl2br($this->escaper->escapeHtml($this->scopeConfig->getValue('payment/' . $code . '/' . $conf, ScopeInterface::SCOPE_STORE)));
    }

    /**
     * Get image url
     *
     * @param string $code
     * @return string
     */
    protected function getImageSrc($code)
    {
        $image = $this->scopeConfig->getValue('payment/' . $code . '/image', ScopeInterface::SCOPE_STORE);
        if ($image) {
            $pub = $this->url->getConfigData('base_url');
            return $pub . 'media/payment/' . $image;
        }
        return '';
    }
}
