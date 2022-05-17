<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\FlagManager;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'avarda_payments/api/active';
    const KEY_TEST_MODE = 'avarda_payments/api/test_mode';
    const KEY_CLIENT_SECRET = 'avarda_payments/api/client_secret';
    const KEY_CLIENT_ID = 'avarda_payments/api/client_id';

    const KEY_TOKEN_FLAG = 'avarda_payments_api_token';

    const KEY_AUTOMATIC_INVOICING_ACTIVE = 'automatic_invoicing_active';
    const KEY_ORDER_STATUS = 'order_status';

    const URL_TEST = 'https://avdonl-s-authorizationapi.westeurope.cloudapp.azure.com/';
    const URL_PRODUCTION = 'https://avdonl-p-authorizationapi.westeurope.cloudapp.azure.com/';
    const TOKEN_PATH = 'oauth2/token';

    /** @var ScopeConfigInterface */
    private $config;

    /** @var EncryptorInterface */
    protected $encryptor;

    /** @var FlagManager */
    protected $flagManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        FlagManager $flagManager,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->config = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->flagManager = $flagManager;
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->config->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return (bool) $this->config->getValue(self::KEY_TEST_MODE);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->encryptor->decrypt($this->config->getValue(self::KEY_CLIENT_SECRET));
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->config->getValue(self::KEY_CLIENT_ID);
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->getTestMode()) {
            return self::URL_TEST;
        }
        return self::URL_PRODUCTION;
    }

    /**
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->getApiUrl() . self::TOKEN_PATH;
    }

    /**
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->getValue(self::KEY_ORDER_STATUS);
    }

    /**
     * @return bool
     */
    public function isAutomaticInvoicingActive()
    {
        return (bool) $this->getValue(self::KEY_AUTOMATIC_INVOICING_ACTIVE);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->encryptor->decrypt($this->flagManager->getFlagData(self::KEY_TOKEN_FLAG));
    }

    /**
     * @param $token string
     */
    public function saveNewToken($token)
    {
        $this->flagManager->saveFlag(self::KEY_TOKEN_FLAG, $this->encryptor->encrypt($token));
    }
}
