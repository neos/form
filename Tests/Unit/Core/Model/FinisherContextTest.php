<?php
namespace TYPO3\Form\Tests\Unit\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for FinisherContext Domain Model
 * @covers \TYPO3\Form\Core\Model\FinisherContext
 */
class FinisherContextTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

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
?>