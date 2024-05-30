<?php

namespace Avarda\Payments\Model;

use Avarda\Payments\Gateway\Client\AvardaClient;
use Avarda\Payments\Gateway\Config\Config;
use Avarda\Payments\Helper\PaymentMethod;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Store\Model\ScopeInterface;

class GetAprWidgetHtml
{
    const STAGE_WIDGET_JS_URL = 'https://payment-widget.stage.avarda.com/cdn/payment-widget.js';
    const PROD_WIDGET_JS_URL = 'https://payment-widget.avarda.com/cdn/payment-widget.js';
    const STAGE_WIDGET_INIT_URL = 'https://stage.checkout-api.avarda.com/api/paymentwidget/partner/init';
    const PROD_WIDGET_INIT_URL = 'https://checkout-api.avarda.com/api/paymentwidget/partner/init';

    protected ScopeConfigInterface $config;
    protected Session $checkoutSession;
    protected Resolver $localeResolver;
    protected AvardaClient $avardaClient;
    protected ConfigInterface $paymentConfig;
    protected PaymentMethod $paymentMethod;
    protected Escaper $escaper;

    public function __construct(
        ScopeConfigInterface $config,
        Session $checkoutSession,
        Resolver $localeResolver,
        AvardaClient $avardaClient,
        ConfigInterface $paymentConfig,
        PaymentMethod $paymentMethod,
        Escaper $escaper
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->avardaClient = $avardaClient;
        $this->paymentConfig = $paymentConfig;
        $this->paymentMethod = $paymentMethod;
        $this->escaper = $escaper;
    }

    public function execute($paymentCode)
    {
        $total = $this->checkoutSession->getQuote()->getGrandTotal();
        $accountClass = $this->getAccountClass($paymentCode);
        return '<avarda-apr-widget
                    price="' . $total . '"
                    lang="' . $this->getLang() . '"
                    payment-method="' . $this->getPaymentMethod($paymentCode) . '"
                    '. ($accountClass ? 'account-class="' . $accountClass . '"' : '') .
                    '></avarda-apr-widget>';
    }

    public function getScriptInfo()
    {
        $url = $this->config->getValue('avarda_payments/api/test_mode', ScopeInterface::SCOPE_STORE) ? self::STAGE_WIDGET_JS_URL : self::PROD_WIDGET_JS_URL;
        $url .= '?ts=' . rand();
        $initData = $this->initAprWidget();
        return [
            'url' => $url,
            'paymentId' => $initData['paymentId'],
            'widgetJwt' => $initData['widgetJwt'],
            'styles' => $this->getStyles()
        ];
    }

    public function getLang()
    {
        $localeCode = $this->localeResolver->getLocale();
        $parts = explode('_', $localeCode);
        return reset($parts);
    }

    /**
     * @return array
     * @throws ClientException
     * @throws GuzzleException
     */
    public function initAprWidget()
    {
        $url = $this->paymentConfig->getTestMode() ? self::STAGE_WIDGET_INIT_URL : self::PROD_WIDGET_INIT_URL;
        $headers = $this->avardaClient->buildHeader();
        $response = $this->avardaClient->get($url, $headers);
        $responseArray = json_decode($response, true);
        if (!is_array($responseArray)) {
            throw new ClientException(__('Apr Widget init failed'));
        } elseif (isset($responseArray['error_description'])) {
            throw new ClientException(__('Apr widget init failed with error: %1', $responseArray['error_description']));
        }

        return $responseArray;
    }

    /**
     * @param $paymentCode
     * @return int|string
     */
    public function getAccountClass($paymentCode)
    {
        return $this->config->getValue('payment/' . $paymentCode . '/apr_account_class', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $paymentMethod
     * @return string
     */
    public function getPaymentMethod($paymentMethod)
    {
        $paymentMethod = $this->paymentMethod->getPaymentMethod($paymentMethod);
        return strtolower(preg_replace(
            '/(?<=[a-z])([A-Z]+)/',
            '-$1',
            lcfirst($paymentMethod)
        ));
    }

    /**
     * Takes from config rows and parses them to json to be used in init
     * buttons.primary.fontSize='22'
     * buttons.primary.base.backgroundColor='#fff'
     *
     * @return string
     */
    public function getStyles()
    {
        $customCss = $this->config->getValue('avarda_payments/api/custom_css', ScopeInterface::SCOPE_STORE);
        $styles = [];
        if ($customCss && count(explode("\n", $customCss)) > 0) {
            foreach (explode("\n", $customCss) as $row) {
                if (!trim($row) && strpos($row, '=') === false) {
                    continue;
                }
                [$path, $value] = explode('=', $row);
                $value = trim($value, " \t\n\r\0\x0B;'" . '"');

                if (!$value || !$path) {
                    continue;
                }

                $pathParts = explode('.', $path);
                $prevKey = false;
                foreach ($pathParts as $part) {
                    if ($prevKey === false) {
                        if (!isset($styles[$part])) {
                            $styles[$part] = [];
                        }
                        $prevKey = &$styles[$part];
                    } else {
                        if (!isset($prevKey[$part])) {
                            $prevKey[$part] = [];
                        }
                        $prevKey = &$prevKey[$part];
                    }
                }
                $prevKey = is_numeric($value) ? floatval($value) : $value;

                if (strpos($value, '[') !== false && strpos($value, ']') !== false) {
                    $value = json_decode($value, true);
                    if ($value) {
                        $prevKey = $value;
                    }
                }

                unset($prevKey);
            }
        }
        $stylesJson = json_encode($styles);
        if (!$stylesJson) {
            $stylesJson = '[]';
        }

        return $stylesJson;
    }
}
