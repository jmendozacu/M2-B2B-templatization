<?php
namespace Dckap\ARreport\Block;
class ARreport extends \Magento\Framework\View\Element\Template 
{
  /**
  * @var \Magento\Customer\Model\Customer
  */
  protected $customer;
  /**
  * @var \Magento\Framework\App\Request\Http
  */
  protected $request;
  /**
  * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
  */
  protected $orderCollection;

  protected $orders;
  /**
  * @var \Magento\Sales\Model\Order
  */
  protected $order;
  /**
  * @var \Magento\Sales\Model\Order\Invoice
  */
  protected $invoicesCollection;
  /**
  * @var \Magento\Framework\Pricing\Helper\Data
  */
  protected $currencyFomatterHelper;
  /**
  * @var \Magento\Customer\Model\Session
  */
  protected $_customerSession;

  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Customer\Model\Customer $customer,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
    \Magento\Sales\Model\Order $order,
    \Magento\Sales\Model\Order\Invoice $invoicesCollection,
    \Magento\Framework\Pricing\Helper\Data $currencyFomatterHelper,
    array $data = array()
    ) 
  {
    $this->request = $request;
    $this->customer = $customer;
    $this->_customerSession = $customerSession;
    $this->orderCollection = $orderCollection;
    $this->order = $order;
    $this->invoicesCollection = $invoicesCollection;
    $this->currencyFomatterHelper = $currencyFomatterHelper;
    parent::__construct($context, $data);
  }

  public function _prepareLayout()
  {
    return parent::_prepareLayout();
  }
  public function getInvoiceCollection($customerId)
  {

    if (!$this->orders) {
      $this->orders = $this->orderCollection->create()->addFieldToSelect(
        '*'
        )->addFieldToFilter(
        'customer_id',
        $customerId
        )->setOrder(
        'created_at',
        'desc'
        );
      }
      $orderids = $this->orders->getAllIds();

      return $this->order->getInvoiceCollection()->addFieldToFilter('order_id', $orderids);
  }

  public function getFormattedCurrency()
  {
    return $this->currencyFomatterHelper;
  }

  public function getReports()
  {
    if( $this->_customerSession->isLoggedIn()) {
      $customerid =  $this->_customerSession->getCustomerId(); 

      $invoicesCollection = $this->getInvoiceCollection($customerid);
      $invoicesIds = $invoicesCollection->getAllIds();
      array_multisort($invoicesIds, SORT_ASC);

      $invoiceDetails = array();

      foreach ($invoicesIds as $key => $invoiceid) {
        $invoicesCollection = $this->invoicesCollection->load($invoiceid);
        $invoiceDetails[$key]['invoice_no'] = $invoicesCollection->getIncrementId();
        $invoiceDetails[$key]['invoice_date'] = $invoicesCollection->getCreatedAt();
        $invoiceDetails[$key]['original_amount'] = $invoicesCollection->getBaseGrandTotal();
        $invoiceDetails[$key]['total_due'] = $invoicesCollection->getBaseGrandTotal();
        $invoiceDetails[$key]['order_id'] = $invoicesCollection->getOrderId();

      }

      return $invoiceDetails;
    }
  }

  public function getCustomerId()
  {

    if( $this->_customerSession->isLoggedIn()) {
      $customerid =  $this->_customerSession->getCustomerId(); 
      return $customerid;
    }
    return ;
  }

  public function getInvoiceUrl($order)
  {
    return $this->getUrl('sales/order/invoice', ['order_id' => $order]);
  }

  // public function getTotalValues($ar_reports)
  // {
  //   $total = array();

  //   if(!empty($ar_reports))
  //   {
  //     $total['memo_amount'] = 0;
  //     $total['bucket1'] =0;
  //     $total['bucket2'] = 0;
  //     $total['bucket3'] = 0;
  //     $total['bucket4'] = 0;
  //     $total['total_due']= 0;
  //     foreach ($ar_reports as $key=>$value) {

  //       $total['memo_amount'] = $total['memo_amount'] + $value->memo_amount;
  //       $total['total_due'] = $total['total_due'] + $value->total_due;
  //       $total['bucket1'] = $total['bucket1'] + $value->bucket1 ;
  //       $total['bucket2'] = $total['bucket2'] + $value->bucket2 ;
  //       $total['bucket3']= $total['bucket3']+ $value->bucket3 ;
  //       $total['bucket4'] =$total['bucket4'] + $value->bucket4 ;

  //     }
  //   }
  //   return $total;
  // }

  // public function getP21CustomerId()
  // {
  //   return $this->_customerSession->getData('p21_customerid');
  // }

  }