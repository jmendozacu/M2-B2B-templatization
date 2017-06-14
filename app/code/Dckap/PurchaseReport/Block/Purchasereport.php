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
		array $data = array()) 
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
		if($rolling_year == 'Rolling Year')
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
			$this->orders = $this->_orderCollectionFactory->create($customer_id)
			->addAttributeToFilter('main_table.created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->getSelect()
			->join(['items'=>('sales_order_item')],'main_table.entity_id = items.order_id')
			->reset(\Zend_Db_Select::COLUMNS)
			->columns(array('items.sku as 0','items.name as 1',"SUM(items.qty_ordered) as 2","SUM(items.row_total) as 3"))		
			->group(array('items.sku','items.name'));
			$this->orders = $this->orders->getConnection()->fetchAll($this->orders);

			foreach ($this->orders as $key => $value) {
				$this->orders[$key][2] = number_format($value[2]);
				$this->orders[$key][3] = $this->getCurrencySymbol().number_format($value[3],2);
			}
		}
		usort($this->orders, array($this,'sortByOrder'));
		return json_encode(array_values($this->orders));
	}
	private static function sortByOrder($a, $b) 
	{
		if(is_numeric($a) && !is_numeric($b))
			return 1;
		else if(!is_numeric($a) && is_numeric($b))
			return -1;
		else
			return ($a < $b) ? -1 : 1;
	}

	public function getOrdersByLocation($rolling_year)
	{
		$months = array();
		if($rolling_year == 'Rolling Year')
		{
			for($i = 1; $i <= 13; $i++){
				$mnth = 13-$i;
				$timestamp = strtotime("-$mnth month");
				$value =  date('n', strtotime("-$mnth month"));
				$text  =  date('M', strtotime("-$mnth month"));
				$year  =  date('Y', strtotime("-$mnth month"));
				$months[$i] =  $text." ".$year;
			}
		}
		$shipping_address = array();

		if($rolling_year == 'Year to Date')
		{
			$fromDate =  (date('Y-01-01'));
			$toDate= $this->date->gmtDate();

			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}else{
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
			$this->orders = $this->_orderCollectionFactory->create()
			->addFieldToSelect('*')
			->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->addFieldToFilter('customer_id',$customer_id)
			->setOrder('created_at','desc');
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
			$order_detail[$_order->getId()]['monthyr'] = $month." ".$year;
			$order_detail[$_order->getId()]['amount'] = $_order->getGrandTotal();
			$order_detail[$_order->getId()]['shipping_address']= $shipping_address_data_new;
			$shipping_address[$i]=$shipping_address_data_new;
			$i++;
		}

		$shipping_address = array_map("unserialize", array_unique(array_map("serialize", $shipping_address)));

		return $this->getMonthDetails($order_detail,count($shipping_address),$shipping_address,$months);
	}


	function getMonthDetails($order_detail,$count_shipping_address,$shipping_address,$months)
	{
		$new_orders = array();
		$result = array();
		$tmp = array();

		foreach($order_detail as $arg)
		{
			$tmp[$arg['monthyr']][] = $arg['amount'];
			$tmp[$arg['monthyr']][] = $arg['shipping_address'];
		}
		$output = array();

		foreach($tmp as $type => $labels)
		{
			$output[] = array(
				'monthyr' => $type,
				'amount' => $labels
				);
		}
		if($count_shipping_address == 1)
		{
			$i = 0;
			foreach ($output as $outorder) {
				foreach ($outorder as  $final) {
					if(is_array($final))
					{
						$result[$i]['monthyr'] = $outorder['monthyr'];
						$result[$i]['amount'] = array_sum($final);
						$result[$i]['shipping_address'] = $shipping_address[0];
						$i++;
					}
				}
			}
		}else{
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
						if(is_array($n))
						{
							if($address == $n)
							{
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
					if($odd < $count)
					{
						$temp_key = $new_order[$odd];
						if($odd == 1)
						{
							$temp_array[$key][$temp_key] = $new_order[$odd-1];
						}else{
							if(isset($temp_array[$key][$temp_key]))
							{
								$temp_array[$key][$temp_key] = $temp_array[$key][$temp_key] + $new_order[$odd-1];
							}else
							{
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
					$result[$final_i]['amount'] = $final_v; 
					$result[$final_i]['shipping_address'] = $shipping_address[$final_k]; 
					$final_i++;
				}
			}
		}
		$temp_array1 = array();
		$temp_array2 = array();
		foreach ($result as $key => $value) {
			if(!isset($temp_array1[$value['monthyr'].'/split/'.implode(',', $value['shipping_address'])])){
				$temp_array1[$value['monthyr'].'/split/'.implode(',', $value['shipping_address'])] = $value['amount']; 
			}
			else{
				$temp_array1[$value['monthyr'].'/split/'.implode(',', $value['shipping_address'])] += $value['amount'];   
			}
		}
		foreach ($temp_array1 as $key => $value) {
			$split = explode('/split/',$key);
			if(!isset($temp_array2[$split[1]])){
				$temp_array2[$split[1]] = array_combine(array($split[0]), array($value));
			}else{
				$temp_array2[$split[1]] = array_merge($temp_array2[$split[1]], array_combine(array($split[0]), array($value)));
			}
		}
		return $this->pdfMonthsLocation($temp_array2,$result,$months);
	}

	function pdfMonthsLocation($location,$result,$months)
	{
		$totalAmount = array();
		$dateyr =  date('Y-01-01');
		$year  =  date('Y', strtotime($dateyr));
		if(empty($months))
		{
			$monthss[0] = array(0=>"Shipping Address" , 1 => "Jan $year", 2 => "Feb $year", 3 => "Mar $year", 4 => "Apr $year", 5 => "May $year", 6 => "Jun $year", 7 => "Jul $year", 8 => "Aug $year", 9 => "Sep $year", 10 => "Oct $year", 11 => "Nov $year", 12 => "Dec $year", 13 => "Total Amount");
		}  else {
			$months[0] = 'Shipping Address';
			$months[14] = 'Total Amount';
			ksort($months);
			$monthss[0]    = $months;
		}
		$k = 1;
		$locationCount = count($location);
		$sortthemonth =array_combine($monthss[0], $monthss[0]);
		$sortthemonth_count = count($sortthemonth);
		foreach ($location as $key1 => $value1) {
			$j = 0;
			$monthss[$k][$sortthemonth_count-1] = 0;
			foreach ($sortthemonth as $key => $value) {
				if($j == 0){
					$monthss[$k][$j] = $key1;
				}
				elseif(array_key_exists($key, $value1)){
					$monthss[$k][$j] = $this->getCurrencySymbol().number_format($value1[$key],2);
					$monthss[$k][$sortthemonth_count-1] += $value1[$key];
				}
				elseif($j!=($sortthemonth_count-1)){
					$monthss[$k][$j] = "-"; 
				}elseif($j ==($sortthemonth_count-1)){
					$monthss[$k][$sortthemonth_count-1] = $this->getCurrencySymbol().number_format($monthss[$k][$sortthemonth_count-1],2);
				}
				$j++;
			}
			$k++;
		}
		$sum = 0;
		foreach ($result as $key => $value) {   
			if (!isset($totalAmount[$value['monthyr']])) {
				$totalAmount[$value['monthyr']] = $value['amount'];
			}
			else {
				$totalAmount[$value['monthyr']] += $value['amount'];
			}
			$sum += $value[ 'amount' ];
		}
		$locationCount = count($result);
		$monthss[$locationCount+1][0]= "Total Amount";
		$sortthemonth =array_combine($monthss[0], $monthss[0]);
		$j =1;
		foreach ($sortthemonth as $key => $value) {
			if($key == "Shipping Address"){
				continue;
			}
			if(array_key_exists($key, $totalAmount)){
				$monthss[$locationCount+1][$j] = $this->getCurrencySymbol().number_format($totalAmount[$key],2);
			}
			else{
				$monthss[$locationCount+1][$j] = "-"; 
			}
			$j++;
		}
		$monthss[$locationCount+1][$sortthemonth_count-1] = $this->getCurrencySymbol().number_format($sum,2);
		return json_encode(array_values($monthss));
	}

	public function getOrdersByProductCategory($rolling_year) 
	{
		$months = array();
		if($rolling_year == 'Rolling Year')
		{
			for($i = 1; $i <= 13; $i++){
				$mnth = 13-$i;
				$timestamp = strtotime("-$mnth month");
				$value =  date('n', strtotime("-$mnth month"));
				$text  =  date('M', strtotime("-$mnth month"));
				$year  =  date('Y', strtotime("-$mnth month"));   
				$months[$i] =  $text." ".$year;
			}
		}
		if($rolling_year == 'Year to Date')
		{
			$fromDate =  (date('Y-01-01'));
			$toDate= $this->date->gmtDate();

			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}else{
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
			$this->orders = $this->_orderCollectionFactory->create()
			->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->addFieldToSelect('*')
			->addFieldToFilter('customer_id',$customer_id)
			->setOrder('created_at','desc');
		}
		$order_detail = array();
		foreach ($this->orders as $_order) {
			foreach ($_order->getItems() as $item) { 
				$created_date = $_order->getCreatedAt();
				$month = date("M", strtotime($created_date));
				$year = date("Y", strtotime($created_date));
				$order_detail[$_order->getId()][$item->getItemId()]['month'] = $month;
				$order_detail[$_order->getId()][$item->getItemId()]['monthyr'] = $month." ".$year;
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
		$temp_array1 = array();
		$temp_array2 = array();
		$temp_array3 = array();
		foreach ($temp_array as $key => $value) {
			if(!isset($temp_array1[$value['monthyr'].'/split/'.$value['category']])){
				$temp_array1[$value['monthyr'].'/split/'.$value['category']] = $value['row_total']; 
			}
			else{
				$temp_array1[$value['monthyr'].'/split/'.$value['category']] += $value['row_total'];  
			}
		}
		foreach ($temp_array1 as $key => $value) {
			$split = explode('/split/',$key);
			if(!isset($temp_array2[$split[1]])){
				$temp_array2[$split[1]] = array_combine(array($split[0]), array($value));
			}else{
				$temp_array2[$split[1]] = array_merge($temp_array2[$split[1]], array_combine(array($split[0]), array($value)));
			}
		}
		foreach ($temp_array1 as $key => $value) {
			$split = explode('/split/',$key);
			$temp = array($split[0],$split[1],$value);
			$temp_key = array('monthyr','category','row_total');
			$temp_array3[] = array_combine($temp_key, $temp);
		}
		return $this->pdfForMonths($temp_array2,$temp_array3,$months );
	}

	function pdfForMonths($category_cal,$temp_array3,$months)
	{
		$totalAmount = array();
		$dateyr =  date('Y-01-01');
		$year  =  date('Y', strtotime($dateyr));
		if(empty($months))
		{
			$monthss[0] = array(0=>"Subcategory Name" , 1 => "Jan $year", 2 => "Feb $year", 3 => "Mar $year", 4 => "Apr $year", 5 => "May $year", 6 => "Jun $year", 7 => "Jul $year", 8 => "Aug $year", 9 => "Sep $year", 10 => "Oct $year", 11 => "Nov $year", 12 => "Dec $year", 13 => "Total Amount");
		}  else {
			$months[0] = 'Subcategory Name';
			$months[14] = 'Total Amount';
			ksort($months);
			$monthss[0]    = $months;
		}
		$k = 1;
		$sortthemonth =array_combine($monthss[0], $monthss[0]);
		$sortthemonth_count = count($sortthemonth);
		foreach ($category_cal as $key1 => $value1) {
			$j = 0;
			$monthss[$k][$sortthemonth_count-1] = 0;
			foreach ($sortthemonth as $key => $value) {
				if($j == 0){
					$monthss[$k][$j] = $key1;
				}
				elseif(array_key_exists($key, $value1)){
					$monthss[$k][$j] = $this->getCurrencySymbol().number_format($value1[$key],2);
					$monthss[$k][$sortthemonth_count-1] += $value1[$key];
				}
				elseif($j!=($sortthemonth_count-1)){
					$monthss[$k][$j] = "-"; 
				}elseif($j ==($sortthemonth_count-1)){
					$monthss[$k][$sortthemonth_count-1] = $this->getCurrencySymbol().number_format($monthss[$k][$sortthemonth_count-1],2);
				}
				$j++;
			}
			$k++;
		}
		$categoryCalCount = count($category_cal);
		$monthss[$categoryCalCount+1][0]= "Total Amount";
		$sum = 0;

		foreach ($temp_array3 as $key => $value) {   
			if (!isset($totalAmount[$value['monthyr']])) {
				$totalAmount[$value['monthyr']] = $value['row_total'];
			}
			else {
				$totalAmount[$value['monthyr']] += $value['row_total'];
			}
			$sum += $value[ 'row_total' ];
		}
		$q =1;
		foreach ($sortthemonth as $key => $value) {
			if($key == "Subcategory Name"){
				continue;
			}
			if(array_key_exists($key, $totalAmount)){
				$monthss[$categoryCalCount+1][$q] = $this->getCurrencySymbol().number_format($totalAmount[$key],2);
			}
			else{
				$monthss[$categoryCalCount+1][$q] = "-";  
			}
			$q++;
		}
		$monthss[$categoryCalCount+1][$sortthemonth_count-1] = $this->getCurrencySymbol().number_format($sum,2);
		return json_encode(array_values($monthss));
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
}