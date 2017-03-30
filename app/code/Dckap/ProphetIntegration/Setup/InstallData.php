<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\ProphetIntegration\Setup;

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
        
        /* P21 Customer Id */
        
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $customerSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_customerid");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_customerid",  array(
            "type"     => "int",
            "backend"  => "",
            "label"    => "P21 CustomerId",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $p21_customerid   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_customerid");

        $p21_customerid = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'p21_customerid');
        $used_in_form[]="adminhtml_customer";

        $p21_customerid->setData("used_in_forms", $used_in_form)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 1000);

        $p21_customerid->save();

        /* P21 Contact Id */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_contactid");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_contactid",  array(
            "type"     => "int",
            "backend"  => "",
            "label"    => "P21 ContactId",
            "input"    => "text",
            "source"   => "",
            "visible"  => false,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $p21_contactid   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "p21_contactid");

        $p21_contactid= $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'p21_contactid');
        $used_in_forms[]="adminhtml_customer";
                $p21_contactid->setData("used_in_forms", $used_in_forms)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 1001);

        $p21_contactid->save();

        /* Already a P21 Customer */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "is_p21_customer");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 
            "is_p21_customer",  array(
            "type"     => "int",
            "backend"  => "",
            "label"    => "Is already p21 customer?",
            "input"    => "boolean",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $is_p21_customer   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "is_p21_customer");

        $is_p21_customer = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'is_p21_customer');
        $used_in_form6[]="adminhtml_customer";
        $used_in_form6[]="customer_account_create";
        $is_p21_customer->setData("used_in_forms", $used_in_form6)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 800);

        $is_p21_customer->save();

        $installer->endSetup();
    }
}
