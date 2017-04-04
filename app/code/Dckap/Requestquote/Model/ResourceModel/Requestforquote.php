<?php

namespace Dckap\Requestquote\Model\ResourceModel;

class Requestforquote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected function _construct()
	{
		$this->_init('dckap_requestquote', 'requestquote_id');
	}
}