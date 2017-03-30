<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Dckap\ApplyForCredit\Block\Adminhtml\Edit\Tab;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
/**
* Customer account form block
*/
class Credit  extends \Magento\Framework\View\Element\Template implements TabInterface
{
    /**
    * Core registry
    *
    * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;
    /**
    * @param \Magento\Backend\Block\Template\Context $context
    * @param \Magento\Framework\Registry $registry
    * @param array $data
    */
    protected $urlModel;

    protected $_credit;

    public function __construct(
        \Dckap\ApplyForCredit\Block\Credit $credit,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
        ) {
        $this->_credit = $credit;
        $this->urlModel = $urlFactory->create();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getCreditFileUrl()
    {
        return $this->urlModel->getBaseUrl().'pub/media/dckap/customer_credit_line'; 

    }
    public function getCreditInformation()
    {

        return  $this->_credit->getCreditCollections($this->getCustomerId());
    }

    /**
    * @return string|null
    */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    /**
    * @return \Magento\Framework\Phrase
    */
    public function getTabLabel()
    {
        return __('CreditLine Information');
    }
    /**
    * @return \Magento\Framework\Phrase
    */
    public function getTabTitle()
    {
        return __('Credit Line Information');
    }
    /**
    * @return bool
    */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
    * @return bool
    */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
    /**
    * Tab class getter
    *
    * @return string
    */
    public function getTabClass()
    {
        return '';
    }
    /**
    * Return URL link to Tab content
    *
    * @return string
    */
    public function getTabUrl()
    {
        return $this->getUrl('credit/*/credit', ['_current' => true]);
    }
    /**
    * Tab should be loaded trough Ajax call
    *
    * @return bool
    */
    public function isAjaxLoaded()
    {
        return true;
    }
}
