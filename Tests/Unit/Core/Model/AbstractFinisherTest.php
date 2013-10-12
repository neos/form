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
 * Test for AbstractFinisher
 * @covers \TYPO3\Form\Core\Model\AbstractFinisher<extended>
 * @covers \TYPO3\Form\Core\Model\FinisherContext<extended>
 * @covers \TYPO3\Form\Core\Runtime\FormRuntime<extended>
 * @covers \TYPO3\Form\Core\Runtime\FormState<extended>
 */
class AbstractFinisherTest extends \TYPO3\Flow\Tests\UnitTestCase {

	protected $formRuntime = NULL;

	/**
	 * @test
	 */
	public function executeSetsFinisherContextAndCallsExecuteInternal() {
		$finisher = $this->getAbstractFinisher();
		$finisher->expects($this->once())->method('executeInternal');

		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);
		$this->assertSame($finisherContext, $finisher->_get('finisherContext'));
	}

	/**
	 * @test
	 */
	public function parseOptionReturnsPreviouslySetOption() {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$finisher->setOptions(array('foo' => 'bar'));
		$this->assertSame('bar', $finisher->_call('parseOption', 'foo'));
	}

	/**
	 * @test
	 */
	public function parseOptionReturnsNumbersAndSimpleTypesWithoutModification() {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$obj = new \stdClass();
		$finisher->setOptions(array('foo' => 42, 'baz' => $obj));
		$this->assertSame(42, $finisher->_call('parseOption', 'foo'));
		$this->assertSame($obj, $finisher->_call('parseOption', 'baz'));
	}

	public function dataProviderForDefaultOptions() {
		$defaultOptions = array(
			'overridden1' => 'Overridden1Default',
			'nullOption' => 'NullDefault',
			'emptyStringOption' => 'EmptyStringDefault',
			'nonExisting' => 'NonExistingDefault'
		);

		$options = array(
			'overridden1' => 'MyString',
			'nullOption' => NULL,
			'emptyStringOption' => '',
			'someOptionWithoutDefault' => ''
		);

		return array(
			'Empty String is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'emptyStringOption',
				'expected' => 'EmptyStringDefault'
			),
			'null is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'nullOption',
				'expected' => 'NullDefault'
			),
			'non-existing key is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'nonExisting',
				'expected' => 'NonExistingDefault'
			),
			'empty string is unified to NULL if no default value exists' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'someOptionWithoutDefault',
				'expected' => NULL
			)
		);
	}

	/**
	 * @dataProvider dataProviderForDefaultOptions
	 * @test
	 */
	public function parseOptionReturnsDefaultOptionIfNecessary($defaultOptions, $options, $optionKey, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$finisher->setOptions($options);
		$finisher->_set('defaultOptions', $defaultOptions);
		$this->assertSame($expected, $finisher->_call('parseOption', $optionKey));
	}

	public function dataProviderForPlaceholderReplacement() {
		$formValues = array(
			'foo' => 'My Value',
			'bar.baz' => 'Trst'
		);

		return array(
			'Simple placeholder' => array(
				'formValues' => $formValues,
				'optionValue' => 'test {foo} baz',
				'expected' => 'test My Value baz'
			),
			'Property Path' => array(
				'formValues' => $formValues,
				'optionValue' => 'test {bar.baz} baz',
				'expected' => 'test Trst baz'
			),
		);
	}

	/**
	 * @dataProvider dataProviderForPlaceholderReplacement
	 * @test
	 */
	public function placeholdersAreReplacedWithFormRuntimeValues($formValues, $optionValue, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$formState = new \TYPO3\Form\Core\Runtime\FormState();
		foreach ($formValues as $key => $value) {
			$formState->setFormValue($key, $value);
		}

		$this->formRuntime->_set('formState', $formState);
		$finisher->execute($finisherContext);

		$finisher->setOptions(array('key1' => $optionValue));
		$this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
	}

	/**
	 * @dataProvider dataProviderForPlaceholderReplacement
	 * @test
	 */
	public function placeholdersInsideDefaultsReplacedWithFormRuntimeValues($formValues, $optionValue, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$formState = new \TYPO3\Form\Core\Runtime\FormState();
		foreach ($formValues as $key => $value) {
			$formState->setFormValue($key, $value);
		}

		$this->formRuntime->_set('formState', $formState);
		$finisher->execute($finisherContext);

		$finisher->_set('defaultOptions', array('key1' => $optionValue));
		$this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
	}

	/**
	 * @test
	 */
	public function cancelCanBeSetOnFinisherContext() {
		$finisherContext = $this->getFinisherContext();
		$this->assertFalse($finisherContext->isCancelled());
		$finisherContext->cancel();
		$this->assertTrue($finisherContext->isCancelled());
	}

	/**
	 * @return \TYPO3\Form\Core\Model\AbstractFinisher
	 */
	protected function getAbstractFinisher() {
		return $this->getAccessibleMock('TYPO3\Form\Core\Model\AbstractFinisher', array('executeInternal'));
	}

	/**
	 * @return \TYPO3\Form\Core\Model\FinisherContext
	 */
	protected function getFinisherContext() {
		$this->formRuntime = $this->getAccessibleMock('TYPO3\Form\Core\Runtime\FormRuntime', array('dummy'), array(), '', FALSE);
		return new \TYPO3\Form\Core\Model\FinisherContext($this->formRuntime);
	}
}
