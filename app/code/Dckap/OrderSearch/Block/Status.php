<?php

namespace Dckap\OrderSearch\Block;

use Magento\Framework\Registry;

class Status extends \Magento\Framework\View\Element\Template
{
    protected $registry;

    protected $order;

    protected $address;

    protected $orderFactory;

    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Address $address,
        Registry $registry, 
        array $data = []
        ) {
        $this->order = $order;
        $this->address = $address;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
    * @return void
    */
    protected function _construct()
    {
        parent::_construct();
    }


    public function getOrder()
    {

        return $this->registry->registry('current_order');

    }
}
