<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\FlagManager;

class ConfigSaveObserver implements ObserverInterface
{
    /** @var FlagManager */
    protected $flagManager;

    public function __construct(
        FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    public function execute(Observer $observer)
    {
        $paths = $observer->getEvent()->getData('changed_paths');
        $changed = ['avarda_payments/api/test_mode', 'avarda_payments/api/client_secret', 'avarda_payments/api/client_id'];
        $hasChanged = false;
        foreach ($changed as $item) {
            if (in_array($item, $paths)) {
                $hasChanged = true;
                break;
            }
        }
        // If api keys or api url is changed remove current api token data
        if ($hasChanged) {
            $this->flagManager->deleteFlag('avarda_payments_api_token');
            $this->flagManager->deleteFlag('avarda_payments_token_valid');
        }
    }
}
