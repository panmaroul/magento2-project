<?php

namespace Atwix\TestAttribute\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;

    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){

        $setup->startSetup();
        if (version_compare($context->getVersion(), '3.0.0')){
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        }
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'Manufacturer', [
        'type' => 'text',
        'label' => 'Manufacturer',
        'input' => 'textarea',
        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
        'required' => false,
        'visible' => true,
        'default' => 0,
        'position' => 334,
        'system' => false,
        'backend' => ''
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute('product', 'Manufacturer')->addData(['used_in_forms' => [
        'adminhtml_product',
        ]]);
        $attribute->save();
        $setup->endSetup();
    }
    
    
}
