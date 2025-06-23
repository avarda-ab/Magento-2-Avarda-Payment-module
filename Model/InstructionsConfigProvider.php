<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;

class InstructionsConfigProvider implements ConfigProviderInterface
{
    protected string $methodCode;
    protected MethodInterface $methodInstance;
    protected Escaper $escaper;
    protected ScopeConfigInterface $scopeConfig;
    protected UrlInterface $url;
    protected GetAprWidgetHtml $getAprWidgetHtml;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        GetAprWidgetHtml $getAprWidgetHtml,
        $methodCode = ''
    ) {
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->methodCode = $methodCode;
        $this->getAprWidgetHtml = $getAprWidgetHtml;
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
                'terms_text' => $this->getIsAprWidgetEnabled($this->methodCode) ? '' : $this->getConfigValue($this->methodCode, 'terms_text'),
                'terms_link' => $this->getIsAprWidgetEnabled($this->methodCode) ? '' : $this->getConfigValue($this->methodCode, 'terms_link'),
                'apr_widget' => array_merge(
                    ['enabled' => $this->getIsAprWidgetEnabled($this->methodCode)],
                    ($this->getIsAprWidgetEnabled($this->methodCode) ? $this->getAprWidgetHtml->getScriptInfo() : [])
                )
            ];
        }
        return $config;
    }

    /**
     * @param $code
     * @return bool
     */
    protected function getIsAprWidgetEnabled($code)
    {
        return $this->scopeConfig->isSetFlag('payment/' . $code . '/apr_widget_active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        if ($this->getIsAprWidgetEnabled($code)) {
            return $this->getAprWidgetHtml->execute($code);
        } else {
            return nl2br($this->escaper->escapeHtml($this->scopeConfig->getValue('payment/' . $code . '/instructions', ScopeInterface::SCOPE_STORE)));
        }
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
