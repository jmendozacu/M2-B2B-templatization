<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\CustomerAttributeCreate\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * Init
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * Installs DB schema for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();

        
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
         $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "custom_product_price");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "custom_product_price",  array(
            "type"     => "text",
            "backend"  => "",
            "label"    => "Custom Product Price",
            "input"    => "textarea",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default"  => "",
            "frontend" => "",
            "unique"   => false,
            "note"     => "{'Product Sku':'Product Price','Next product Sku':'Product Price'}"

        ));

        $custom_product_price   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "custom_product_price");

        $custom_product_price = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'custom_product_price');
        $used_in_form[]="adminhtml_customer";
        $custom_product_price->setData("used_in_forms", $used_in_form)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 500);

        $custom_product_price->save();
		$installer->endSetup();
    }
}
