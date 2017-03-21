<?php

namespace Dckap\TaxExemptionCertificate\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
        
        
        
        public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
                
                
                $installer = $setup;                
                $installer->startSetup();
                
                
                /**
                        * Create table 'customer_tax_cert'
                */
                
                $table = $installer->getConnection()->newTable($installer->getTable('customer_tax_exemption_certificate'))
                                        ->addColumn('taxexemption_id',
                                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                                    null,
                                                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                                    'Taxexemption ID'
                                                    )
                                        ->addColumn('customer_id',
                                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                                    255,
                                                    [],
                                                    'Customer ID'
                                                    )
                                        ->addColumn('state_name',
                                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                                    255,
                                                    [],
                                                    'State Name'
                                                    )
                                        ->addColumn('file_name',
                                                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                                    255,
                                                    [],
                                                    'Tax Cert name'
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

