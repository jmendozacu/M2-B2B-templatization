<?php

namespace Dckap\Requestquote\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{



    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {


        if (version_compare($context->getVersion(), '1.0.1', '<')) {


            $installer = $setup;                
            $installer->startSetup();


            /**
            * update column 'overview'
            */
            
            $table = $installer->getTable('dckap_requestquote');                

            $installer->getConnection()->addColumn(
                $table,
                'customer_id',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Customer Id'
                ]                                                
                );
            $installer->getConnection()->addColumn(
                $table,
                'zipcode',
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'LENGTH' =>255,
                'comment' => 'Zip Code'
                ]                                                
                );

            
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {


            $installer = $setup;                
            $installer->startSetup();
            
            
            /**
            * Create table 'request quote details'
            */

            $table = $installer->getConnection()->newTable($installer->getTable('request_quote_details'))
                ->addColumn('request_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Request ID'
                    )
                ->addColumn('requestquote_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Requestquote Id'
                    )
                ->addColumn('product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Product ID'
                    )
                ->addColumn('product_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    500,
                    ['nullable' => true, 'default' => null],
                    'Product Name'
                    )
                ->addColumn('product_qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Product QTY'
                    )
                ->addColumn('product_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Product Price'
                    )
                ->addColumn('product_request',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Request Quote'
                    )
                ->addColumn('create_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Creation Date'
                    )
                ->addColumn('update_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update Date'
                    );


            $installer->getConnection()->createTable($table);

            
            $installer->endSetup();
        }

    }





}

