<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Model;

use Avarda\Payments\Model\Ui\Loan\ConfigProvider as LoanConfigProvider;
use Avarda\Payments\Model\Ui\PartPayment\ConfigProvider as PartPaymentConfigProvider;
use Avarda\Payments\Model\Ui\Invoice\ConfigProvider as InvoiceConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
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
    protected Repository $assetRepo;

    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        GetAprWidgetHtml $getAprWidgetHtml,
        Repository $assetRepo,
        $methodCode = '',
    ) {
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->getAprWidgetHtml = $getAprWidgetHtml;
        $this->assetRepo = $assetRepo;
        $this->methodCode = $methodCode;
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
                ),
                'show_loan_warning' => $this->scopeConfig->isSetFlag('avarda_payments/api/show_loan_warning'),
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
            $html = $this->getAprWidgetHtml->execute($code);
        } else {
            $html = nl2br($this->escaper->escapeHtml($this->scopeConfig->getValue('payment/' . $code . '/instructions', ScopeInterface::SCOPE_STORE)));
        }
        if ($this->scopeConfig->isSetFlag('avarda_payments/api/show_loan_warning')) {
            $html .= $this->getLoanWarningHtml();
        }
        return $html;
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

    /**
     * @return string
     */
    public function getLoanWarningHtml(): string
    {
        if (!in_array($this->methodCode, [LoanConfigProvider::CODE, PartPaymentConfigProvider::CODE, InvoiceConfigProvider::CODE])) {
            return '';
        }
        $imgUrl = $this->assetRepo->getUrlWithParams('Avarda_Payments::image/avarda_alert.png', []);
        return '<div><img style="float:left; margin-right: 10px" src="' . $this->escaper->escapeHtmlAttr($imgUrl) .'" alt="Att låna kostar pengar!"/>' .
            'Att låna kostar pengar!' .
            'Om du inte kan betala tillbaka skulden i tid riskerar du en betalningsanmärkning. Det kan leda till svårigheter att få hyra bostad, teckna abonnemang och få nya lån.' .
            'För stöd, vänd dig till budget- och skuldrådgivningen i din kommun. Kontaktuppgifter finns på <a href="https://www.konsumentverket.se/" target="_blank">konsumentverket.se</a>.</div>';
    }
}
