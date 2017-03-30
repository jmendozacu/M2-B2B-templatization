<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\ApplyForCredit\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'customer_creditline'
         */
        $table = $installer->getConnection()->newTable(
        $installer->getTable('customer_creditline')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'    
        )->addForeignKey(
            $installer->getFkName(
                'customer_creditline',
                'customer_id',
                'customer_entity',
                'entity_id'
                ),
            'customer_id',
            $installer->getTable('customer_entity'), 
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
       )->addColumn(
            'credit_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            [],
            'Credit Amount'    
        )->addColumn(
            'credit_file',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Credit File'
        )->setComment(
            'Apply for Credit'
        );
        $installer->getConnection()->createTable($table);
             
        $installer->endSetup();

    }
}
