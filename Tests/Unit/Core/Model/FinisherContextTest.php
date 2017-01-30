<?php

namespace Neos\Form\Tests\Unit\Core\Model;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Test for FinisherContext Domain Model.
 *
 * @covers \Neos\Form\Core\Model\FinisherContext
 */
class FinisherContextTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @var \Neos\Form\Core\Runtime\FormRuntime
     */
    protected $mockFormRuntime;

    /**
     * @var \Neos\Form\Core\Model\FinisherContext
     */
    protected $finisherContext;

    public function setUp()
    {
        $this->mockFormRuntime = $this->getMockBuilder(\Neos\Form\Core\Runtime\FormRuntime::class)->disableOriginalConstructor()->getMock();
        $this->finisherContext = new \Neos\Form\Core\Model\FinisherContext($this->mockFormRuntime);
    }

    /**
     * @test
     */
    public function getFormRuntimeReturnsTheFormRuntime()
    {
        $this->assertSame($this->mockFormRuntime, $this->finisherContext->getFormRuntime());
    }

    /**
     * @test
     */
    public function isCancelReturnsFalseByDefault()
    {
        $this->assertFalse($this->finisherContext->isCancelled());
    }

    /**
     * @test
     */
    public function isCancelReturnsTrueIfContextHasBeenCancelled()
    {
        $this->finisherContext->cancel();
        $this->assertTrue($this->finisherContext->isCancelled());
    }
}
