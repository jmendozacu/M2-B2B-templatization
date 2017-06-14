<?php
 
namespace Dckap\Chargeinaccount\Model;
 
/**
 * Pay In Store payment method model
 */
class AddPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
 
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'chargeinaccount';
}