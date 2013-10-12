<?php
namespace TYPO3\Form\Tests\Unit\Core\Runtime;

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
use TYPO3\Form\Core\Runtime\FormRuntime;
use TYPO3\Form\Core\Model\Page;

require_once(__DIR__ . '/Fixture/DummyFinisher.php');
/**
 * Test for Form Runtime
 * @covers \TYPO3\Form\Core\Runtime\FormRuntime<extended>
 */
class FormRuntimeTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function valuesSetInConstructorCanBeReadAgain() {
		$formDefinition = new FormDefinition('foo');
		$mockActionRequest = $this->getMockBuilder('TYPO3\Flow\Mvc\ActionRequest')->disableOriginalConstructor()->getMock();
		$mockHttpResponse = $this->getMockBuilder('TYPO3\Flow\Http\Response')->disableOriginalConstructor()->getMock();

		$formRuntime = $this->createFormRuntime($formDefinition, $mockActionRequest, $mockHttpResponse);

		$this->assertSame($mockActionRequest, $formRuntime->getRequest()->getParentRequest());
		$this->assertSame($mockHttpResponse, $formRuntime->getResponse());
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
		$this->assertSame(array($page1, $page2, $page3), $formRuntime->getPages());

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
	 * @test
	 */
	public function arrayAccessReturnsDefaultValuesIfSet() {
		$formDefinition = new FormDefinition('foo');
		$page1 = new Page('p1');
		$formDefinition->addPage($page1);
		$element1 = new \TYPO3\Form\FormElements\GenericFormElement('foo', 'Bar');
		$page1->addElement($element1);

		$element1->setDefaultValue('My Default');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$formState = new \TYPO3\Form\Core\Runtime\FormState();
		$formRuntime->_set('formState', $formState);
		$this->assertSame($formState, $formRuntime->getFormState());

		$this->assertSame('My Default', $formRuntime['foo']);
		$formRuntime['foo'] = 'Overridden';
		$this->assertSame('Overridden', $formRuntime['foo']);
		$formRuntime['foo'] = NULL;
		$this->assertSame('My Default', $formRuntime['foo']);

		$formRuntime['foo'] = 'Overridden2';
		$this->assertSame('Overridden2', $formRuntime['foo']);

		unset($formRuntime['foo']);
		$this->assertSame('My Default', $formRuntime['foo']);

		$this->assertSame(NULL, $formRuntime['nonExisting']);
	}

	/**
	 * @param FormDefinition $formDefinition
	 * @param \TYPO3\Flow\Mvc\ActionRequest $request
	 * @param \TYPO3\Flow\Http\Response $response
	 * @return \TYPO3\Form\Core\Runtime\FormRuntime
	 */
	protected function createFormRuntime(FormDefinition $formDefinition, \TYPO3\Flow\Mvc\ActionRequest $request = NULL, \TYPO3\Flow\Http\Response $response = NULL) {
		if ($request === NULL) {
			$httpRequest = \TYPO3\Flow\Http\Request::create(new \TYPO3\Flow\Http\Uri('foo'));
			$request = $httpRequest->createActionRequest();
		}
		if ($response === NULL) {
			$response = new \TYPO3\Flow\Http\Response();
		}
		return $this->getAccessibleMock('TYPO3\Form\Core\Runtime\FormRuntime', array('dummy'), array($formDefinition, $request, $response));
	}
}
