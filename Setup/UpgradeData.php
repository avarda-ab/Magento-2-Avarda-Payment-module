<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /** @var CustomerSetupFactory */
    protected $customerSetupFactory;

    /** @var AttributeSetFactory */
    protected $attributeSetFactory;

    /** @var SalesSetupFactory */
    protected $salesSetupFactory;

    /**
     * UpgradeData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $salesSetup->addAttribute(
            Order::ENTITY,
            'avarda_payments_authorize_id',
            ['type' => 'text', 'visible' => false, 'required' => false]
        );

        $setup->endSetup();
    }
}
