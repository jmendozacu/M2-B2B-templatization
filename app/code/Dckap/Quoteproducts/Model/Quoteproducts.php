<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\Quoteproducts\Model;

class Quoteproducts extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Dckap\Quoteproducts\Model\ResourceModel\Quoteproducts');
    }
}
