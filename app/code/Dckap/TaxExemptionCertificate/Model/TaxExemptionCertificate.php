<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\TaxExemptionCertificate\Model;

class TaxExemptionCertificate extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Dckap\TaxExemptionCertificate\Model\ResourceModel\TaxExemptionCertificate');
    }
}
