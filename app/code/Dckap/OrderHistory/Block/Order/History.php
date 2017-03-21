<?php
/**
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Dckap\OrderHistory\Block\Order;

/**
* Sales order history block
*/
class History extends \Magento\Framework\View\Element\Template
{
    /**
    * @var string
    */
    protected $_template = 'order/history.phtml';

    /**
    * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
    */
    protected $_orderCollectionFactory;

    /**
    * @var \Magento\Customer\Model\Session
    */
    protected $_customerSession;

    /**
    * @var \Magento\Sales\Model\Order\Config
    */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;

    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Magento\Sales\Model\Order\Config $orderConfig
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }
    public function getOrders()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (isset($_POST["search_id"])){
            $id = $_POST['search_id'];
            if (!$this->orders) 
            {
                $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
                    '*'
                )->addAttributeToFilter(
                    'increment_id',array('like' => '%'.$id.'%'))->addFieldToFilter(
                    'customer_id',$customerId          
                )->setOrder(
                    'created_at',
                    'desc'
                );
            }
        }

        if (!$this->orders) {
            $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter(
                'status',
                ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }
    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', ['order_id' => $order->getId()]);
    }
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    public function getInvoiceUrl($order)
    {
        return $this->getUrl('sales/order/invoice', ['order_id' => $order->getId()]);
    }
    public function getShipmentUrl($order)
    {
        return $this->getUrl('sales/order/shipment', ['order_id' => $order->getId()]);
    }
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
