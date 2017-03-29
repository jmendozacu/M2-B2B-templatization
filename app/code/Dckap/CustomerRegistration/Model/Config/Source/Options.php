<?php

namespace Dckap\CustomerRegistration\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

/**
* Custom Attribute Renderer
*
* @author      Webkul Core Team <support@webkul.com>
*/
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
    * @var OptionFactory
    */
    protected $optionFactory;

    /**
    * Get all options
    *
    * @return array
    */
    public function getAllOptions()
    {
        /* your Attribute options list*/
        $categoryId = 3;
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
        $childCategories=$category->getChildrenCategories();
        $childname[0]=array('label'=>'Select Options', 'value'=>'');
        $i=0;
        foreach ($childCategories as $key => $value) { 

            if($value->getIsActive()){ 
                $childname[]=array('label'=>$value->getName(), 'value'=>$value->getName());
            }
        }

        $this->_options=$childname;

        return $this->_options;
    }

    /* Get a text for option value
    *
    * @param string|integer $value
    * @return string|bool
    */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
    * Retrieve flat column definition
    *
    * @return array
    */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}
