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

use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\Core\Model\FinisherContext;
use Neos\Form\Core\Runtime\FormRuntime;
use PHPUnit\Framework\Assert;

/**
 * Test for FinisherContext Domain Model
 * @covers \Neos\Form\Core\Model\FinisherContext
 */
class FinisherContextTest extends UnitTestCase
{
    /**
     * @var FormRuntime
     */
    protected $mockFormRuntime;

    /**
     * @var FinisherContext
     */
    protected $finisherContext;

    public function setUp(): void
    {
        $this->mockFormRuntime = $this->getMockBuilder(FormRuntime::class)->disableOriginalConstructor()->getMock();
        $this->finisherContext = new FinisherContext($this->mockFormRuntime);
    }

    /**
     * @test
     */
    public function getFormRuntimeReturnsTheFormRuntime()
    {
        Assert::assertSame($this->mockFormRuntime, $this->finisherContext->getFormRuntime());
    }

    /**
     * @test
     */
    public function isCancelReturnsFalseByDefault()
    {
        Assert::assertFalse($this->finisherContext->isCancelled());
    }

    /**
     * @test
     */
    public function isCancelReturnsTrueIfContextHasBeenCancelled()
    {
        $this->finisherContext->cancel();
        Assert::assertTrue($this->finisherContext->isCancelled());
    }
}
