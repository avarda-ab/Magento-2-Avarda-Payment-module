<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TranIdDataBuilder
 */
class PosIdDataBuilder implements BuilderInterface
{
    /**
     * A unique transaction ID
     */
    const TRAN_ID = 'posId';

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        return [self::TRAN_ID => '0'];
    }
}
