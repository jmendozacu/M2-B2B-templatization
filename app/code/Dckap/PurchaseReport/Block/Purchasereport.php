<?php

namespace Dckap\PurchaseReport\Block;

class Purchasereport extends \Magento\Framework\View\Element\Template 
{
	protected $_orderCollectionFactory;
	protected $_categoryCollection;
	protected $_productRepository;
	protected $orders;
	protected $customerSession;
	protected $keys_map;
	protected $_categoryFactory;
	protected $date;
	protected $currency;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Directory\Model\Currency $currency,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		array $data = array()
	) 
	{
		$this->customerSession = $customerSession;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_categoryCollection = $categoryCollection;
		$this->_productRepository = $productRepository;
		$this->_categoryFactory = $categoryFactory;  
		$this->date = $date;
		$this->currency = $currency;
		parent::__construct($context, $data);
	}

	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	public function getCustomerSession()
	{
		if ($this->customerSession->isLoggedIn()) {
			return $this->customerSession;
		}
	}
	public function getCustomerLoggedin()
	{
		if ($this->customerSession->isLoggedIn()) {
			return $customer_id = $this->customerSession->getId();
		}  
		return;
	}
	public function getCurrencySymbol()
	{
		return $this->currency->getCurrencySymbol();
	}
	public function getCategory($categoryId) 
	{
		$this->_category = $this->_categoryFactory->create();
		$this->_category->load($categoryId);    
		return $this->_category;
	}
	public function getOrdersByItem($rolling_year)
	{
		$months = array();
		if($rolling_year == 'Rolling year')
		{
			for($i = 1; $i <= 13; $i++){
				$timestamp = strtotime("-$i month");
				$value =  date('n', strtotime("-$i month"));
				$text  =  date('M', strtotime("-$i month"));  
				$months[$value] =  $text;
			}
		}

		if($rolling_year == 'Year to Date')
		{
			$fromDate =  (date('Y-01-01'));
			$toDate= $this->date->gmtDate();

			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		} else {
			$time = strtotime("-1 year", time());
			$fromDate = date("Y-m-d", $time);
			$fromDate = date('Y-m-d H:i:s', strtotime('-1 day',strtotime($fromDate)));

			$toDate= $date = $this->date->gmtDate();
			$toDate = date('Y-m-d H:i:s', strtotime('-2 day',strtotime($toDate)));
		}
		if ($this->customerSession->isLoggedIn()) {
			$customer_id = $this->customerSession->getId();
		}  
		if (!$this->orders) {
			$this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
			'*'
			) ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->addFieldToFilter(
			'customer_id',
			$customer_id
			)->setOrder(
			'created_at',
			'desc'
			);
		}

		$item_details = array();
		$i = 0;
		foreach ($this->orders as $_order) {
			foreach ($_order->getItems() as $item) { 
				if($this->search_array($item->getSku(), $item_details)) {  

					$item_details[$item->getSku()]['item_id'] = $item->getSku();
					$item_details[$item->getSku()]['name'] = $item->getName();
					$item_details[$item->getSku()]['row_total']  =  number_format($item_details[$item->getSku()]['row_total'] + $item->getRowTotal(),2);
					$item_details[$item->getSku()]['qty_ordered'] = $item_details[$item->getSku()]['qty_ordered'] + $item->getQtyOrdered();
				} else {
					$item_details[$item->getSku()]['item_id'] = $item->getSku();
					$item_details[$item->getSku()]['name'] = $item->getName();
					$item_details[$item->getSku()]['row_total'] = number_format($item->getRowTotal(),2);
					$item_details[$item->getSku()]['qty_ordered'] = number_format($item->getQtyOrdered());
				}
			}
		}
		$item_details = $this->arrayForPdf($item_details,array('item_id','name','row_total','qty_ordered'));
		return json_encode($item_details);
	}

	public function getOrdersByLocation($rolling_year)
	{
		$months = array();
		if($rolling_year == 'Rolling year')	{
			for($i = 1; $i <= 13; $i++){
				$mnth = 13-$i;
				$timestamp = strtotime("-$mnth month");
				$value =  date('n', strtotime("-$mnth month"));
				$text  =  date('M', strtotime("-$mnth month"));
				$year  =  date('Y', strtotime("-$mnth month"));
				$months[$i] =  $text."(".$year.")";
			}
		}
		$shipping_address = array();

		if($rolling_year == 'Year to Date')	{
			$fromDate =  (date('Y-01-01'));
			$toDate= $this->date->gmtDate();

			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}else {
			$time = strtotime("-1 year", time());
			$fromDate = date("Y-m-d", $time);
			$fromDate = date('Y-m-d H:i:s', strtotime('-1 day',strtotime($fromDate)));

			$toDate= $date = $this->date->gmtDate();
			$toDate = date('Y-m-d H:i:s', strtotime('-2 day',strtotime($toDate)));
		}

		if ($this->customerSession->isLoggedIn()) {
			$customer_id = $this->customerSession->getId();
		}  
		if (!$this->orders) {
			$this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
			'*'
			)->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))->addFieldToFilter(
			'customer_id',
			$customer_id
			)->setOrder(
			'created_at',
			'desc'
			);
		}


		$i = 0;
		$order_detail = array();
		foreach ($this->orders as $_order) {
			$shipping_address_data  = $_order->getShippingAddress()->getData();
			$shipping_address_data_new = array();  
			$order_by=array('street','city','region','postcode','country_id');
			foreach ($order_by as $key => $value) {
				$shipping_address_data_new[$value] = $shipping_address_data[$value];
			}                  
			$created_date = $_order->getCreatedAt();
			$month = date("M", strtotime($created_date));
			$year = date("Y", strtotime($created_date));
			$order_detail[$_order->getId()]['month'] = $month;
			$order_detail[$_order->getId()]['monthyr'] = $month."(".$year.")";
			$order_detail[$_order->getId()]['amount'] = $_order->getGrandTotal();
			$order_detail[$_order->getId()]['shipping_address']= $shipping_address_data_new;
			$shipping_address[$i]=$shipping_address_data_new;
			$i++;
		}
		$shipping_address = array_map("unserialize", array_unique(array_map("serialize", $shipping_address)));

		return $this->getMonthDetails($order_detail,count($shipping_address),$shipping_address,$months);
	}

	public function getOrdersByProductCategory($rolling_year) 
	{
		$months = array();
		if($rolling_year == 'Rolling year')
		{
			for($i = 1; $i <= 13; $i++){
				$mnth = 13-$i;
				$timestamp = strtotime("-$mnth month");
				$value =  date('n', strtotime("-$mnth month"));
				$text  =  date('M', strtotime("-$mnth month"));
				$year  =  date('Y', strtotime("-$mnth month"));   
				$months[$i] =  $text."(".$year.")";
			}
		}
		if($rolling_year == 'Year to Date')
		{
			$fromDate =  (date('Y-01-01'));
			$toDate= $this->date->gmtDate();

			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}else {

			$time = strtotime("-1 year", time());
			$fromDate = date("Y-m-d", $time);
			$fromDate = date('Y-m-d H:i:s', strtotime('-1 day',strtotime($fromDate)));

			$toDate= $date = $this->date->gmtDate();
			$toDate = date('Y-m-d H:i:s', strtotime('-2 day',strtotime($toDate)));
		}
		$final = array();
		if ($this->customerSession->isLoggedIn()) {
			$customer_id = $this->customerSession->getId();
		}  
		if (!$this->orders) {
			$this->orders = $this->_orderCollectionFactory->create()->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))->addFieldToSelect(
			'*'
			)->addFieldToFilter(
			'customer_id',
			$customer_id
			)->setOrder(
			'created_at',
			'desc'
			);
		}

		$order_detail = array();
		foreach ($this->orders as $_order) {
			foreach ($_order->getItems() as $item) { 
				$created_date = $_order->getCreatedAt();
				$month = date("M", strtotime($created_date));
				$year = date("Y", strtotime($created_date));
				$order_detail[$_order->getId()][$item->getItemId()]['month'] = $month;
				$order_detail[$_order->getId()][$item->getItemId()]['monthyr'] = $month."(".$year.")";
				$sku = $item->getSKU();
				$order_detail[$_order->getId()][$item->getItemId()]['row_total']= $item->getRowTotal();
				$order_detail[$_order->getId()][$item->getItemId()]['category'] =$this->getCategoryCollection($sku);
			}
		}

		$temp_array = array();
		foreach ($order_detail as $key => $arr) {
			foreach ($arr as $k => $ar) {
				$temp_array[] = $ar;
			}
		}
		$cnt = 0;
		foreach ($temp_array as $fkey => $fvalue) {
			foreach ($temp_array as $skey => $svalue) {
				if($fkey != $skey) {
					if( ($fvalue['month'] == $svalue['month']) && ($fvalue['category'] == $svalue['category']) )
					{
						$final[$cnt]['row_total'] = $svalue['row_total'] + $fvalue['row_total'];
						$final[$cnt]['month'] =$svalue['month'];
						$final[$cnt]['monthyr'] =$svalue['monthyr'];
						$final[$cnt]['category'] = $svalue['category'];
					}elseif((!$this->search_array($svalue['category'], $final)))
					{
						$final[$cnt]['row_total'] =  $svalue['row_total'];
						$final[$cnt]['month'] =$svalue['month'];
						$final[$cnt]['monthyr'] =$svalue['monthyr'];
						$final[$cnt]['category'] = $svalue['category'];
					}
					$cnt++;
				}
			}
		}

		$category_cal = array_unique($final, SORT_REGULAR);
		return $this->pdfForMonths($category_cal,$months );
	}

	function pdfForMonths($category_cal,$months)
	{
		$dateyr =  date('Y-01-01');
		$year  =  date('Y', strtotime($dateyr));
		if(empty($months))
		{
				$monthss[0] = array(0=>"Category Name" , 1 => "Jan($year)", 2 => "Feb($year)", 3 => "Mar($year)", 4 => "Apr($year)", 5 => "May($year)", 6 => "Jun($year)", 7 => "Jul($year)", 8 => "Aug($year)", 9 => "Sep($year)", 10 => "Oct($year)", 11 => "Nov($year)", 12 => "Dec($year)");
		}  else {
			$months[0] = 'Category Name';
			ksort($months);
			$monthss[0]    = $months;
		}

		$i = 1;
		foreach ($category_cal as $key => $value) {   
			$key = array_search($value['monthyr'], $monthss[0]);
			$monthss[$i] =array_fill($key+1, 13 - $key, '-');
			$monthss[$i][0] = $value['category'];
			$monthss[$i][$key] = $this->getCurrencySymbol().number_format($value['row_total'],2);
			$this->cleanArray($monthss[$i]);
			ksort($monthss[$i]);
			$i++;
		}
		return json_encode($monthss);

	}
	function pdfMonthsLocation($location,$months)
	{
		$location = array_reverse($location);
		$dateyr =  date('Y-01-01');
		$year  =  date('Y', strtotime($dateyr));
		if(empty($months))
		{
			$monthss[0] = array(0=>"Shipping Address" , 1 => "Jan($year)", 2 => "Feb($year)", 3 => "Mar($year)", 4 => "Apr($year)", 5 => "May($year)", 6 => "Jun($year)", 7 => "Jul($year)", 8 => "Aug($year)", 9 => "Sep($year)", 10 => "Oct($year)", 11 => "Nov($year)", 12 => "Dec($year)");
		}  else {
			$months[0] = 'Shipping Address';
			ksort($months);
			$monthss[0]    = $months;
		}

		$i = 1;$j = 0;
		foreach ($location as $key => $value) {   
			$key = array_search($value['monthyr'], $monthss[0]);

			$monthss[$i] =array_fill($key+1, 13 - $key, '-');

			$monthss[$i][0] = implode(', ',$value['shipping_address']);
			$monthss[$i][$key] = $value['amount']; 
			$this->cleanArray($monthss[$i]);
			ksort($monthss[$i]);
			$i++;
		}
		return json_encode($monthss);
	}
	public function getCategoryCollection($sku)
	{
		$category_name = '';
		$productCollection = $this->getProductBySku($sku);
		$categoryId = $productCollection->getCategoryId();
		$categoryName = $productCollection->getCategoryCollection();
		foreach($categoryName as $category) {
			$category_name = $this->getCategory($category->getId())->getName();

		}
		return $category_name;
	}  

	public function getProductBySku($sku)
	{
		return $this->_productRepository->get($sku);
	}
	function getMonthDetails($order_detail,$count_shipping_address,$shipping_address,$months)
	{
		$new_orders = array();
		$result = array();
		$tmp = array();
		foreach($order_detail as $arg)	{
			$tmp[$arg['monthyr']][] = $arg['amount'];
			$tmp[$arg['monthyr']][] = $arg['shipping_address'];
		}
		$output = array();

		foreach($tmp as $type => $labels){
			$output[] = array(
			'monthyr' => $type,
			'amount' => $labels
			);
		}
		if($count_shipping_address == 1){
			$i = 0;
			foreach ($output as $outorder) {
				foreach ($outorder as  $final) {
					if(is_array($final)){
						$result[$i]['monthyr'] = $outorder['monthyr'];
						$result[$i]['amount'] = $this->getCurrencySymbol().number_format(array_sum($final),2);
						$result[$i]['shipping_address'] = $shipping_address[0];
						$i++;
					}
				}
			}
		}else {
			foreach ($output as $orders) {
				foreach ($orders as $order) {
					if(!is_array($order))
						$mon = $order;
				}
				$new_orders[$mon] = $order;
			}
			foreach ($new_orders as $key => $n_o) {
				foreach ($n_o as $k => $n) {
					foreach ($shipping_address as $s_key => $address) {
						if(is_array($n)){
							if($address == $n){
								$new_orders[$key][$k] = $s_key;
								break;
							}
						}

					}
				}
			}

			$temp_array = array();
			foreach ($new_orders as $key => $new_order) {
				$odd = 1;
				$count = count($new_order);
				foreach ($new_order as $inner_key => $order) {
					if($odd < $count){
						$temp_key = $new_order[$odd];
						if($odd == 1){
							$temp_array[$key][$temp_key] = $new_order[$odd-1];
						}else{
							if(isset($temp_array[$key][$temp_key])){
								$temp_array[$key][$temp_key] = $temp_array[$key][$temp_key] + $new_order[$odd-1];
							} else {
								$temp_array[$key][$temp_key] = $new_order[$odd-1];
							}
						}
					}
					$odd = $odd +2;
				}
			}
			$final_i = 0;
			foreach ($temp_array as $final_key => $final_value) {
				foreach ($final_value as $final_k => $final_v) {
					$result[$final_i]['monthyr'] = $final_key;
					$result[$final_i]['amount'] = $this->getCurrencySymbol().number_format($final_v,2); 
					$result[$final_i]['shipping_address'] = $shipping_address[$final_k]; 
					$final_i++;
				}
			}
		}
		return $this->pdfMonthsLocation($result,$months);
	}

	function search_array_inside($order_detail, $shipping_address)
	{
		print_r($order_detail);
		foreach ($order_detail as $order) {
			/*if($order['shipping_address'] == $shipping_address){
				return $order;
			}else{
				return false;
			}*/
		}
	}

	function cleanArray(&$array)
	{
		end($array);
		$max = key($array); //Get the final key as max!
		for($i = 0; $i < $max; $i++){
			if(!isset($array[$i])){
				$array[$i] = '-';
			}
		}
	}

	function arrayForPdf($array, $keys) {
		$newarray = array();
		foreach ($array as $key => $value) {      
			$newarray[] = array_values($value);
		}
		foreach($newarray as $key1 => $value1){
			$newarray[$key1][2] = $this->getCurrencySymbol().$value1[2];
		}
		return $newarray;
	}

	function search_array($needle, $haystack) {
		if(in_array($needle, $haystack)) {
			return true;
		}
		foreach($haystack as $element) {
			if(is_array($element) && $this->search_array($needle, $element))
				return true;
		}
		return false;
	}
}