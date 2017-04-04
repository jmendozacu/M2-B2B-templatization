<?php

namespace Dckap\Requestquote\Block\Adminhtml\Requestquote\Edit\Tab;

use Dckap\Requestquote\Model\RequestquoteFactory;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $requestquoteCollectionFactory;

    /**
     * Contact factory
     *
     * @var ContactFactory
     */
    protected $contactFactory;

    /**
     * @var  \Magento\Framework\Registry
     */
    protected $registry;

    protected $_objectManager = null;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param ContactFactory $attachmentFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $requestquoteCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RequestquoteFactory $contactFactory,
        \Dckap\Requestquote\Model\ResourceModel\Requestquote\CollectionFactory $requestquoteCollectionFactory,
        array $data = []
    ) {
        $this->contactFactory = $contactFactory;
        $this->requestquoteCollectionFactory = $requestquoteCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('request_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * add Column Filter To Collection
     */
    protected function _addColumnFilterToCollection($column)
    {   
        if ($column->getId() == 'in_product') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->requestquoteCollectionFactory->create();
        $quote_id = $this->getRequest()->getParam('requestquote_id');
        $collection->addFieldToFilter('requestquote_id',$quote_id);
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        /* @var $model \Webspeaks\ProductsGrid\Model\Slide */
        $model = $this->_objectManager->get('\Dckap\Requestquote\Model\Requestquote');

        $this->addColumn(
            'request_id',
            [
                'header' => __('Id'),
                'header_css_class' => 'a-center',
                'name' => 'request_id',
                'align' => 'center',
                'index' => 'request_id',
            ]
        );
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product Id'),
                'header_css_class' => 'a-center',
                'name' => 'product_id',
                'align' => 'center',
                'index' => 'product_id',
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'header_css_class' => 'a-center',
                'name' => 'product_name',
                'align' => 'center',
                'index' => 'product_name',
            ]
        );

        $this->addColumn(
            'product_qty',
            [
                'header' => __('Product Qty'),
                'header_css_class' => 'a-center',
                'name' => 'product_qty',
                'align' => 'center',
                'index' => 'product_qty',
            ]
        );

        
        

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }

    protected function _getSelectedProducts()
    {
        $contact = $this->getContact();
        return $contact->getProducts($contact);
    }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $contact = $this->getContact();
        $selected = $contact->getProducts($contact);

        if (!is_array($selected)) {
            $selected = [];
        }
        return $selected;
    }

    protected function getContact()
    {
        $contactId = $this->getRequest()->getParam('request_id');
        $contact   = $this->contactFactory->create();
        if ($contactId) {
            $contact->load($contactId);
        }
        return $contact;
    }

}
