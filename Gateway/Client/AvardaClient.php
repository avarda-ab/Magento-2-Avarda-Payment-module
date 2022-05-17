<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Client;

use Avarda\Payments\Gateway\Config\Config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Http\Request;
use Magento\Framework\FlagManager;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Model\Method\Logger;
use Psr\Http\Message\ResponseInterface;

class AvardaClient
{
    /** @var Config */
    protected $config;

    /** @var FlagManager */
    protected $flagManager;

    /** @var ResponseInterface */
    protected $lastResponse;

    /** @var Logger */
    protected $logger;

    public function __construct(
        Config $config,
        FlagManager $flagManager,
        Logger $logger = null
    ) {
        $this->config = $config;
        $this->flagManager = $flagManager;
        $this->logger = $logger;
    }

    /**
     * @param $url string
     * @param $payload array
     * @param $headers array
     * @param $payloadType string
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post($url, $payload, $headers, $payloadType = 'json'): ResponseInterface
    {
        $client = new Client();

        $response = $client->request(
            Request::METHOD_POST,
            $url,
            [
                $payloadType => $payload,
                'headers' => $headers,
                'http_errors' => false
            ]
        );

        $this->handleErrors($response);

        return $response;
    }

    /**
     * @param $url
     * @param array $json
     * @param array $additionalParameters
     * @return string
     * @throws GuzzleException
     */
    public function get($url, $headers, $additionalParameters = []): string
    {
        $client = new Client();

        $response = $client->request(
            Request::METHOD_GET,
            $url,
            [
                'headers' => $headers,
                'query' => $additionalParameters
            ]
        );

        $this->handleErrors($response);

        return $response->getBody()->getContents();
    }

    /**
     * @param ResponseInterface $response
     * @return bool|null
     * @throws \RuntimeException
     */
    public function handleErrors(ResponseInterface $response): ?bool
    {
        $this->lastResponse = $response;
        switch ($response->getStatusCode()) {
            case 200:
            case 201:
            case 202:
            case 203:
            case 400:
            case 401:
            case 422:
                return true;
            case 403:
            case 404:
            case 405:
                $this->logger->debug([$response->getStatusCode(), $response->getBody()]);
                throw new \RuntimeException('Error in request');
            case 500:
                $this->logger->debug([$response->getStatusCode(), $response->getBody()]);
                throw new \RuntimeException('Avarda server error');
            default:
                throw new \RuntimeException('Unhandled response status code');
        }
    }

    /**
     * @param bool $json
     * @return array
     */
    public function buildHeader($json = true, $token = true): array
    {
        $header = [
            'Date' => date('r')
        ];

        if ($json) {
            $header['Accept'] = 'application/json';
            $header['Content-Type'] = 'application/json';
        }

        if ($token) {
            $header['Authorization'] = sprintf('Bearer %s', $this->getToken());
        }

        return $header;
    }

    /**
     * @return ResponseInterface
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        $tokenValid = $this->flagManager->getFlagData('avarda_payments_token_valid');
        if (!$tokenValid || $tokenValid < time()) {
            $authUrl = $this->config->getTokenUrl();
            $authParam = [
                'client_id'     => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
                'grant_type'    => 'client_credentials'
            ];

            $headers = [
                'content-type' => 'application/x-www-form-urlencoded'
            ];
            $response = $this->post($authUrl, $authParam, $headers, 'form_params');
            $responseArray = json_decode((string)$response->getBody(), true);
            if (!is_array($responseArray)) {
                throw new ClientException(__('Authentication with avarda responded with invalid response'));
            } elseif (isset($responseArray['error_description'])) {
                throw new ClientException(__('Authentication error, check avarda credentials'));
            } else {
                $this->flagManager->saveFlag('avarda_payments_token_valid', $responseArray['expires_on']);
                $this->config->saveNewToken($responseArray['access_token']);
            }
        }

        return $this->config->getToken();
    }
}
