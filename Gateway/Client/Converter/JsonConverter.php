<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Client\Converter;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Psr\Http\Message\ResponseInterface;

class JsonConverter implements ConverterInterface
{
    /**
     * Converts gateway response to array structure
     *
     * @param ResponseInterface $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        try {
            $body = json_decode((string)$response->getBody(), true);
        } catch (\Exception $e) {
            throw new ConverterException(
                __('Something went wrong with Avarda payments. Please try again later.')
            );
        }
        if ($response->getStatusCode() === WebapiException::HTTP_UNAUTHORIZED) {
            throw new AuthorizationException(
                __('Failed to authorize Avarda Payments.')
            );
        } elseif (isset($body['errorMessage'])) {
            throw new WebapiException(__($body['errorMessage']));
        } elseif (isset($body['errors'])) {
            $errorMsg = '';
            foreach ($body['errors'] as $error) {
                $errorMsg .= __($error[0]) . ' ';
            }
            throw new WebapiException(__($errorMsg));
        } elseif ($response->getStatusCode() == 400) {
            throw new WebapiException(__('Something went wrong with Avarda payments. Please try again later.'));
        }
        return $body;
    }

    /**
     * @param $response ResponseInterface
     */
    public function convertErrorMessage($response)
    {

    }
}
