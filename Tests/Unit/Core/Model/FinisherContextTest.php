<?php
namespace TYPO3\Form\Tests\Unit\Core\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for FinisherContext Domain Model
 * @covers \TYPO3\Form\Core\Model\FinisherContext
 */
class FinisherContextTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Form\Core\Runtime\FormRuntime
	 */
	protected $mockFormRuntime;

	/**
	 * @var \TYPO3\Form\Core\Model\FinisherContext
	 */
	protected $finisherContext;

	public function setUp() {
		$this->mockFormRuntime = $this->getMockBuilder('TYPO3\Form\Core\Runtime\FormRuntime')->disableOriginalConstructor()->getMock();
		$this->finisherContext = new \TYPO3\Form\Core\Model\FinisherContext($this->mockFormRuntime);
	}

	/**
	 * @test
	 */
	public function getFormRuntimeReturnsTheFormRuntime() {
		$this->assertSame($this->mockFormRuntime, $this->finisherContext->getFormRuntime());
	}

	/**
	 * @test
	 */
	public function isCancelReturnsFalseByDefault() {
		$this->assertFalse($this->finisherContext->isCancelled());
	}

	/**
	 * @test
	 */
	public function isCancelReturnsTrueIfContextHasBeenCancelled() {
		$this->finisherContext->cancel();
		$this->assertTrue($this->finisherContext->isCancelled());
	}

}
