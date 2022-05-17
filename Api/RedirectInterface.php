<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Api;

interface RedirectInterface
{
    /**
     * Returns redirect URL
     *
     * @api
     * @return string redirect URL
     */
    public function redirect();
}
