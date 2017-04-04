<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Requestquote\Model\Config\Source\GoogleCaptcha;

class Theme implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Email Identity options
     *
     * @var array
     */
    protected $_options = null;

  
    /**
     * Retrieve list of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            
            $section = array('clean' => 'Clean',
                             'red'  => 'Red',
                             'white'    => 'White',
                             'blackglass' => 'Black Glass');
            

            foreach ($section as $key=>$label) {
                
                $this->_options[] = [
                    'value' => $key,
                    'label' => $label,
                ];
            }
           
        }
        return $this->_options;
    }
}
