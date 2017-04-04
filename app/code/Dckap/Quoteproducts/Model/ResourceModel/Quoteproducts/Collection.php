<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\Quoteproducts\Model\ResourceModel\Quoteproducts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\Quoteproducts\Model\Quoteproducts', 'Dckap\Quoteproducts\Model\ResourceModel\Quoteproducts');
    }
}
