<?php

namespace Dckap\Requestquote\Model;


class Requestforquote extends \Magento\Framework\Model\AbstractModel
{

  protected function _construct()
  {
    $this->_init('Dckap\Requestquote\Model\ResourceModel\Requestforquote');
  }

  public function getAvailableStatuses(){

    $availableOptions = array('New' => 'New',
      'Processing' => 'Processing',
      'Approved' => 'Approved');

    return $availableOptions;
  }

  public function getBudgetStatuses(){

    $options = array('Approved' => 'Approved',
      'Approval Pending' => 'Approval Pending',
      'Open' => 'Open',
      'No Approval' => 'No Approval');

    return $options;
  }

  public function getIdentities()
  {
    return [self::CACHE_TAG . '_' . $this->getId()];
  }

}