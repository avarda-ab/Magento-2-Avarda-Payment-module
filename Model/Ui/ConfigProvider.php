<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
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

    /** @var Config */
    protected $config;

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

        $config = [
            'payment' => [
                self::CODE => [
                    'isActive' => $active,
                ]
            ]
        ];

        return $config;
    }
}
