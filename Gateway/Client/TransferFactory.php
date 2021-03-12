<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Client;

use Avarda\Payments\Gateway\Config\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    const BEARER_AUTHENTICATION_FORMAT = 'Bearer %s';

    /** @var EncryptorInterface */
    protected $encryptor;

    /** @var TransferBuilder */
    protected $transferBuilder;

    /** @var Config */
    protected $config;

    /** @var string */
    protected $method;

    /** @var string */
    protected $uri;

    public function __construct(
        EncryptorInterface $encryptor,
        TransferBuilder $transferBuilder,
        Config $config,
        $method = \Zend_Http_Client::POST,
        $uri = ''
    ) {
        $this->encryptor = $encryptor;
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->method = $method;
        $this->uri = $uri;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    public function create(array $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->getAuthorization(),
        ];

        return $this->transferBuilder
            ->setMethod($this->method)
            ->setUri($this->getUri())
            ->setHeaders($headers)
            ->setBody($request)
            ->build();
    }

    /**
     * Generate basic authorization string
     *
     * @return string
     */
    protected function getAuthorization()
    {
        $token = $this->config->getToken();
        return sprintf(
            self::BEARER_AUTHENTICATION_FORMAT,
            $token
        );
    }

    /**
     * Get URI for the request to call
     *
     * @return string
     */
    public function getUri()
    {
        return $this->config->getApiUrl() . $this->uri;
    }
}
