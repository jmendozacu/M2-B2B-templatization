<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Requestquote\Model\ResourceModel\Requestquote;

use \Magento\Cms\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'request_id';

    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\Requestquote\Model\Requestquote', 'Dckap\Requestquote\Model\ResourceModel\Requestquote');
        $this->_map['fields']['request_id'] = 'main_table.request_id';        
    }

    
    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }

    
    
}
