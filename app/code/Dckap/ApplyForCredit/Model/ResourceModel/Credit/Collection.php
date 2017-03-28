<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\ApplyForCredit\Model\ResourceModel\Credit;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection

{
    protected function _construct()
    {
        $this->_init('Dckap\ApplyForCredit\Model\Credit','Dckap\ApplyForCredit\Model\ResourceModel\Credit');
        //$this->_map['fields']['page_id'] = 'main_table.page_id';
    }
 
    
}