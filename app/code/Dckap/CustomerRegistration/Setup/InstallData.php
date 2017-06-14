<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\CustomerRegistration\Setup;

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
        
        /* Customer Attribute Title */
        
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "title");
        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "title",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Title",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

            ));

        $title   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "title");

        $title= $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'title');
        $used_in_forms[]="adminhtml_customer";
        $used_in_forms[]="checkout_register";
        $used_in_forms[]="customer_account_create";
        $used_in_forms[]="customer_account_edit";
        $used_in_forms[]="adminhtml_checkout";
        $title->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);

        $title->save();

        /* Customer Attribute Company Name */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "reg_companyname");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "reg_companyname",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Company Name",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

            ));

        $reg_companyname   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "reg_companyname");

        $reg_companyname= $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'reg_companyname');
        $used_in_forms7[]="adminhtml_customer";
        $used_in_forms7[]="customer_account_create";
        
        $reg_companyname->setData("used_in_forms", $used_in_forms7)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 900);

        $reg_companyname->save();

        /* Customer Attribute Industry */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY,'industry');

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'industry',
            [

            'type' => 'varchar',
            'backend' => '',
            'frontend' => '',
            'label' => 'Industry', 
            'input' => 'select',
            'class' => '',
            'source' => 'Dckap\CustomerRegistration\Model\Config\Source\Options',

            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,

            'visible' => true,
            'required' => false,

            'default' => '',

            'unique' => false
            ]
            );
        $industry   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "industry");

        $industry= $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'industry');
        $used_in_form1[]="adminhtml_customer";
        $used_in_form1[]="checkout_register";
        $used_in_form1[]="customer_account_create";
        $used_in_form1[]="customer_account_edit";
        $used_in_form1[]="adminhtml_checkout";
        $industry->setData("used_in_forms", $used_in_form1)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);

        $industry->save();

        /* Customer Attribute Security Question */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_question");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_question",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Security Question",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

            ));

        $sec_question   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_question");

        $sec_question = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'secu_question');
        $used_in_form4[]="adminhtml_customer";
        $used_in_form4[]="checkout_register";
        $used_in_form4[]="customer_account_create";
        $used_in_form4[]="customer_account_edit";
        $used_in_form4[]="adminhtml_checkout";
        $sec_question->setData("used_in_forms", $used_in_form4)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);

        $sec_question->save();

        /* Customer Attribute Security Answer */

        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_ans");

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_ans",  array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Security_Answer",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => false,
            "note"       => ""

            ));

        $sec_ans   = $customerSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "secu_ans");

        $sec_ans= $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'secu_ans');
        $used_in_forms5[]="adminhtml_customer";
        $used_in_forms5[]="checkout_register";
        $used_in_forms5[]="customer_account_create";
        $used_in_forms5[]="customer_account_edit";
        $used_in_forms5[]="adminhtml_checkout";
        $sec_ans->setData("used_in_forms", $used_in_forms5)
        ->setData("is_used_for_customer_segment", true)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);

        $sec_ans->save();

        $installer->endSetup();
    }
}
