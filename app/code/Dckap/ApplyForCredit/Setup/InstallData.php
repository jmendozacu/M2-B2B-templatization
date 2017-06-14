<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\ApplyForCredit\Setup;

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

        /* Customer Credit Line PDF file */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_line");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_line",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Credit Line",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $credit_line   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_line");

        $credit_line = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'credit_line');
        $used_in_form[]="adminhtml_customer";
        $used_in_form[]="checkout_register";
        $used_in_form[]="customer_account_create";
        $used_in_form[]="customer_account_edit";
        $used_in_form[]="adminhtml_checkout";
        $credit_line->setData("used_in_forms", $used_in_form)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 100);

        $credit_line->save();

        /* Customer Credit Limit */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_limit");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_limit",  array(
            "type"     => "decimal",
            "backend"  => "",
            "label"    => "Credit Limit",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

        ));

        $credit_limit   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "credit_limit");

        $credit_limit = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'credit_limit');
        $used_in_forms[]="adminhtml_customer";
        $credit_limit->setData("used_in_forms", $used_in_forms)
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined", 1)
            ->setData("is_visible", 1)
            ->setData("sort_order", 101);

        $credit_limit->save();
      

        $installer->endSetup();
    }
}
