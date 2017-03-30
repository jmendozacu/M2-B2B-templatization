<?php

namespace Dckap\PaymentFilter\Observer;


use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Dckap\ApplyForCredit\Block\Credit;

class ExcludeDisallowedPaymentMethod implements ObserverInterface
{
    protected $credit;
    protected $logger; 
    /**
    * Constructor
    *
    * @param FilterInterface[] $filterList
    */
    public function __construct(Credit $credit,\Psr\Log\LoggerInterface $logger)
    {   
        $this->credit = $credit;

        $this->logger = $logger;

    }

    /**
    * @param Observer $observer
    * @return void
    */
    public function execute(Observer $observer)
    {
        if (!$observer || !($observer instanceof Observer)) {
            return;
        }

        $event = $observer->getEvent();

        if (!$event || !($event instanceof Event)) {
            return;
        }

        $result = $event->getResult();

        if (!$result || !($result instanceof DataObject) || !$result->getIsAvailable()) {
            return;
        }

        $paymentMethod = $event->getMethodInstance();

        if (!$paymentMethod || !($paymentMethod instanceof MethodInterface)) {
            return;
        }

        $quote = $event->getQuote();

        if (!$quote || !($quote instanceof Quote)) {
            return;
        }

        $credit = $this->credit->getCreditInfo();

        $result->setData('is_available', true);

        if($credit == false)
        {
            if($paymentMethod->getCode() == 'chargeinaccount')
            {
                $result->setData('is_available', false);
            }
        }
    }
}