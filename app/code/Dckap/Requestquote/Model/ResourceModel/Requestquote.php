<?php

namespace Dckap\Requestquote\Model\ResourceModel;

class Requestquote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
        
        
           
        protected function _construct()
        {
                $this->_init('request_quote_details', 'request_id');
        }
        
        
        
        
}