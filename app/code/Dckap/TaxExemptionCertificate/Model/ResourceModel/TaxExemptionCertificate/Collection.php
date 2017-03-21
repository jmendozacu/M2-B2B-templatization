<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\TaxExemptionCertificate\Model\ResourceModel\TaxExemptionCertificate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\TaxExemptionCertificate\Model\TaxExemptionCertificate', 'Dckap\TaxExemptionCertificate\Model\ResourceModel\TaxExemptionCertificate');
    }
}
