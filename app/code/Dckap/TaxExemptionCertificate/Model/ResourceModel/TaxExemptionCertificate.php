<?php
/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Dckap\TaxExemptionCertificate\Model\ResourceModel;

class TaxExemptionCertificate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_tax_exemption_certificate', 'taxexemption_id');
    }
}
