<?php

namespace Dckap\Requestquote\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{



    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){


        $installer = $setup;                
        $installer->startSetup();


    /**
    * Create table 'requestquote'
    */

    $table = $installer->getConnection()->newTable($installer->getTable('dckap_requestquote'))
        ->addColumn('requestquote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Requestquote ID'
            )
        ->addColumn('company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Company'
            )
        ->addColumn('contact_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Contact Name'
            )
        ->addColumn('email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
            )
        ->addColumn('phone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Phone'
            )
        ->addColumn('status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'New'],
            'Status'
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

