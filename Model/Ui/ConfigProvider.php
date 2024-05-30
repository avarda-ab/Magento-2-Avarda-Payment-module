<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Model\Ui;

use Avarda\Payments\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'avarda_payments';
    protected Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $active = $this->config->isActive();

        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $active,
                ]
            ]
        ];
    }
}
