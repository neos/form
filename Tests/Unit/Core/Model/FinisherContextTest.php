<?php
namespace TYPO3\Form\Tests\Unit\Core\Model;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for FinisherContext Domain Model
 * @covers \TYPO3\Form\Core\Model\FinisherContext
 */
class FinisherContextTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\Form\Core\Runtime\FormRuntime
     */
    protected $mockFormRuntime;

    /**
     * @var \TYPO3\Form\Core\Model\FinisherContext
     */
    protected $finisherContext;

    public function setUp()
    {
        $this->mockFormRuntime = $this->getMockBuilder(\TYPO3\Form\Core\Runtime\FormRuntime::class)->disableOriginalConstructor()->getMock();
        $this->finisherContext = new \TYPO3\Form\Core\Model\FinisherContext($this->mockFormRuntime);
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
