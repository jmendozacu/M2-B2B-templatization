<?php
namespace Dckap\Quickorder\Block\Widget;

class QuickorderWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	public function _toHtml() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $layout = $objectManager->get('Magento\Framework\View\LayoutInterface');   
       // return $layout->createBlock('Dckap\Quickorder\Block\Quickorder')->setTemplate('quickorder.phtml')->toHtml();
    }
}
