<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Dckap\OrderSearch\Block\Sales\Order;

use Magento\Sales\Model\Order;

class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    
    protected function _initGrandTotal()
    {
        $store = $this->getStore();
        $parent = $this->getParentBlock();
        $grandototal = $parent->getTotal('grand_total');
        if (!$grandototal || !(double)$this->_source->getGrandTotal()) {
            return $this;
        }

        if ($this->_config->displaySalesTaxWithGrandTotal($store)) {
            $grandtotal = $this->_source->getGrandTotal();
            $baseGrandtotal = $this->_source->getBaseGrandTotal();
            $grandtotalExcl = $grandtotal - $this->_source->getTaxAmount();
            $baseGrandtotalExcl = $baseGrandtotal - $this->_source->getBaseTaxAmount();
            $grandtotalExcl = max($grandtotalExcl, 0);
            $baseGrandtotalExcl = max($baseGrandtotalExcl, 0);
            $totalExcl = new \Magento\Framework\DataObject(
                [
                'code' => 'grand_total',
                'strong' => true,
                'value' => $grandtotalExcl,
                'base_value' => $baseGrandtotalExcl,
                'label' => __('Grand Total (Excl.Tax)'),
                ]
                );
            $totalIncl = new \Magento\Framework\DataObject(
                [
                'code' => 'grand_total_incl',
                'strong' => true,
                'value' => $grandtotal,
                'base_value' => $baseGrandtotal,
                'label' => __('Grand Total (Incl.Tax)'),
                ]
                );
            // $parent->addTotal($totalExcl, 'grand_total');
            $this->_addTax('shipping');
            // $parent->addTotal($totalIncl, 'discount');
        }
        return $this;
    }
}
