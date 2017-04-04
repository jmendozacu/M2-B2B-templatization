<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\Quoteproducts\Model\ResourceModel;

class Quoteproducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('request_quote_details', 'request_id');
    }
}
