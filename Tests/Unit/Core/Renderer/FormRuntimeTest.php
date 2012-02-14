<?php
namespace TYPO3\Form\Tests\Unit\Core\Runtime;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Runtime\FormRuntime;
use TYPO3\Form\Core\Model\Page;

require_once(__DIR__ . '/Fixture/DummyFinisher.php');
/**
 * Test for Form Runtime
 * @covers \TYPO3\Form\Core\Runtime\FormRuntime<extended>
 */
class FormRuntimeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function valuesSetInConstructorCanBeReadAgain() {
		$formDefinition = new FormDefinition('foo');
		$request = new \TYPO3\FLOW3\MVC\Web\Request();
		$response = new \TYPO3\FLOW3\MVC\Web\Response();

		$formRuntime = $this->createFormRuntime($formDefinition, $request, $response);

		$this->assertSame($request, $formRuntime->getRequest());
		$this->assertSame($response, $formRuntime->getResponse());
		$this->assertSame($formDefinition, $formRuntime->_get('formDefinition'));
	}

	/**
	 * @test
	 */
	public function getTypeReturnsTypeOfFormDefinition() {
		$formDefinition = new FormDefinition('foo');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('TYPO3.Form:Form', $formRuntime->getType());
	}

	/**
	 * @test
	 */
	public function getIdentifierReturnsIdentifierOfFormDefinition() {
		$formDefinition = new FormDefinition('foo');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('foo', $formRuntime->getIdentifier());
	}

	/**
	 * @test
	 */
	public function getRenderingOptionsReturnsRenderingOptionsOfFormDefinition() {
		$formDefinition = new FormDefinition('foo');
		$formDefinition->setRenderingOption('asdf', 'test');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame(array('asdf' => 'test'), $formRuntime->getRenderingOptions());
	}

	/**
	 * @test
	 */
	public function getRendererClassNameReturnsRendererClassNameOfFormDefinition() {
		$formDefinition = new FormDefinition('foo');
		$formDefinition->setRendererClassName('MyRendererClassName');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('MyRendererClassName', $formRuntime->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function getLabelReturnsLabelOfFormDefinition() {
		$formDefinition = new FormDefinition('foo');
		$formDefinition->setLabel('my cool label');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('my cool label', $formRuntime->getLabel());
	}

	/**
	 * @test
	 */
	public function invokeFinishersInvokesFinishersInCorrectOrder() {
		$formDefinition = new FormDefinition('foo');

		$finisherCalls = array();

		$finisher1 = $this->getMockFinisher(function() use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
		});
		$finisher2 = $this->getMockFinisher(function($finisherContext) use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
			$finisherContext->cancel();
		});
		$finisher3 = $this->getMockFinisher(function($finisherContext) use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
		});
		$formDefinition->addFinisher($finisher1);
		$formDefinition->addFinisher($finisher2);
		$formDefinition->addFinisher($finisher3);

		$formRuntime = $this->createFormRuntime($formDefinition);
		$formRuntime->_call('invokeFinishers');

		$this->assertSame(2, count($finisherCalls));
		$this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
		$this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
	}

	/**
	 * @return \TYPO3\Form\Core\Model\FinisherInterface
	 */
	protected function getMockFinisher(\Closure $closureToExecute) {
		$finisher = new Renderer\Fixture\DummyFinisher();
		$finisher->cb = $closureToExecute;
		return $finisher;
	}

	/**
	 * @test
	 */
	public function pageNavigationWorks() {
		$formDefinition = new FormDefinition('foo');
		$page1 = new Page('p1');
		$page2 = new Page('p2');
		$page3 = new Page('p3');
		$formDefinition->addPage($page1);
		$formDefinition->addPage($page2);
		$formDefinition->addPage($page3);

		$formRuntime = $this->createFormRuntime($formDefinition);

		$formRuntime->overrideCurrentPage(0);
		$this->assertSame(NULL, $formRuntime->getPreviousPage());
		$this->assertSame($page1, $formRuntime->getCurrentPage());
		$this->assertSame($page2, $formRuntime->getNextPage());

		$formRuntime->overrideCurrentPage(1);
		$this->assertSame($page1, $formRuntime->getPreviousPage());
		$this->assertSame($page2, $formRuntime->getCurrentPage());
		$this->assertSame($page3, $formRuntime->getNextPage());

		$formRuntime->overrideCurrentPage(2);
		$this->assertSame($page2, $formRuntime->getPreviousPage());
		$this->assertSame($page3, $formRuntime->getCurrentPage());
		$this->assertSame(NULL, $formRuntime->getNextPage());
	}

	/**
	 * @param FormDefinition $formDefinition
	 * @param \TYPO3\FLOW3\MVC\Web\Request $request
	 * @param \TYPO3\FLOW3\MVC\Web\Response $response
	 * @return \TYPO3\Form\Core\Runtime\FormRuntime
	 */
	protected function createFormRuntime(FormDefinition $formDefinition, \TYPO3\FLOW3\MVC\Web\Request $request = NULL, \TYPO3\FLOW3\MVC\Web\Response $response = NULL) {
		if ($request === NULL) {
			$request = new \TYPO3\FLOW3\MVC\Web\Request();
		}
		if ($response === NULL) {
			$response = new \TYPO3\FLOW3\MVC\Web\Response();
		}
		return $this->getAccessibleMock('TYPO3\Form\Core\Runtime\FormRuntime', array('dummy'), array($formDefinition, $request, $response));
	}
}
?>