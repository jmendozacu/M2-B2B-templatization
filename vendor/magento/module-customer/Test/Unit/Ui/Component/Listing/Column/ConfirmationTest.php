<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Ui\Component\Listing\Column\Confirmation;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;

class ConfirmationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Confirmation
     */
    protected $confirmation;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $processor;

    public function setup()
    {
        $this->processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();

        $this->context->expects($this->once())
            ->method('getProcessor')
            ->willReturn($this->processor);

        $this->uiComponentFactory = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->confirmation = new Confirmation(
            $this->context,
            $this->uiComponentFactory,
            $this->scopeConfig,
            [],
            []
        );
    }

    /**
     * @param int $isConfirmationRequired
     * @param string|null $confirmation
     * @param \Magento\Framework\Phrase $expected
     * @dataProvider dataProviderPrepareDataSource
     */
    public function testPrepareDataSource(
        $isConfirmationRequired,
        $confirmation,
        $expected
    ) {
        $websiteId = 1;

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'confirmation' => $confirmation,
                        'website_id' => [
                            $websiteId,
                        ],
                    ],
                ],
            ],
        ];

        $this->processor->expects($this->any())
            ->method('register')
            ->with($this->confirmation)
            ->willReturnSelf();

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(AccountManagement::XML_PATH_IS_CONFIRM, ScopeInterface::SCOPE_WEBSITES, $websiteId)
            ->willReturn($isConfirmationRequired);
        
        $this->confirmation->setData('name', 'confirmation');
        $result = $this->confirmation->prepareDataSource($dataSource);

        $this->assertEquals($result['data']['items'][0]['confirmation'], $expected);
    }

    /**
     * @return array
     */
    public function dataProviderPrepareDataSource()
    {
        return [
            [false, 'confirmation', __('Confirmation Not Required')],
            [true, 'confirmation', __('Confirmation Required')],
            [true, null, __('Confirmed')],
        ];
    }
}
