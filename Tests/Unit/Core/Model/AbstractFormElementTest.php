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
 * Test for AbstractFormElement Domain Model
 * @covers \TYPO3\Form\Core\Model\AbstractFormElement<extended>
 */
class AbstractFormElementTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function constructorSetsIdentifierAndType() {
		$element = $this->getFormElement(array('myIdentifier', 'TYPO3.Form:MyType'));
		$this->assertSame('myIdentifier', $element->getIdentifier());
		$this->assertSame('TYPO3.Form:MyType', $element->getType());
	}

	public function invalidIdentifiers() {
		return array(
			'Null Identifier' => array(NULL),
			'Integer Identifier' => array(42),
			'Empty String Identifier' => array(''),
		);
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\IdentifierNotValidException
	 * @dataProvider invalidIdentifiers
	 */
	public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier) {
		$this->getFormElement(array($identifier, 'TYPO3.Form:MyType'));
	}

	/**
	 * @test
	 */
	public function labelCanBeSetAndGet() {
		$formElement = $this->getFormElement(array('foo', 'TYPO3.Form:MyType'));
		$this->assertSame('', $formElement->getLabel());
		$formElement->setLabel('my label');
		$this->assertSame('my label', $formElement->getLabel());
	}

	/**
	 * @test
	 */
	public function defaultValueCanBeSetAndGet() {
		$formDefinition = new FormDefinition('foo');
		$formElement = $this->getFormElement(array('foo', 'TYPO3.Form:MyType'));
		$page = new Page('page');
		$formDefinition->addPage($page);
		$page->addElement($formElement);
		$this->assertNull($formElement->getDefaultValue());
		$formElement->setDefaultValue('My Default Value');
		$this->assertSame('My Default Value', $formElement->getDefaultValue());
	}

	/**
	 * @test
	 */
	public function renderingOptionsCanBeSetAndGet() {
		$formElement = $this->getFormElement(array('foo', 'TYPO3.Form:MyType'));
		$this->assertSame(array(), $formElement->getRenderingOptions());
		$formElement->setRenderingOption('option1', 'value1');
		$this->assertSame(array('option1' => 'value1'), $formElement->getRenderingOptions());
		$formElement->setRenderingOption('option2', 'value2');
		$this->assertSame(array('option1' => 'value1', 'option2' => 'value2'), $formElement->getRenderingOptions());
	}

	/**
	 * @test
	 */
	public function rendererClassNameCanBeGetAndSet() {
		$formElement = $this->getFormElement(array('foo', 'TYPO3.Form:MyType'));
		$this->assertNull($formElement->getRendererClassName());
		$formElement->setRendererClassName('MyRendererClassName');
		$this->assertSame('MyRendererClassName', $formElement->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function getUniqueIdentifierBuildsIdentifierFromRootFormAndElementIdentifier() {
		$formDefinition = new FormDefinition('foo');
		$myFormElement = $this->getFormElement(array('bar', 'TYPO3.Form:MyType'));
		$page = new Page('asdf');
		$formDefinition->addPage($page);

		$page->addElement($myFormElement);
		$this->assertSame('foo-bar', $myFormElement->getUniqueIdentifier());
	}

	public function getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider() {
		return array(
			array('foo', 'bar', 'foo-bar'),
			array('foo.bar', 'baz', 'foo_bar-baz'),
			array('foo', 'bar?baz', 'foo-bar_baz'),
			array('SomeForm', 'SomeElement', 'someForm-SomeElement'),
		);
	}

	/**
	 * @test
	 * @dataProvider getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider
	 * @param string $formIdentifier
	 * @param string $elementIdentifier
	 * @param string $expectedResult
	 */
	public function getUniqueIdentifierReplacesSpecialCharactersByUnderscores($formIdentifier, $elementIdentifier, $expectedResult) {
		$formDefinition = new FormDefinition($formIdentifier);
		$myFormElement = $this->getFormElement(array($elementIdentifier, 'TYPO3.Form:MyType'));
		$page = new Page('somePage');
		$formDefinition->addPage($page);

		$page->addElement($myFormElement);
		$this->assertSame($expectedResult, $myFormElement->getUniqueIdentifier());
	}

	/**
	 * @test
	 */
	public function isRequiredReturnsFalseByDefault() {
		$formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
		$page = new Page('asdf');
		$formDefinition->addPage($page);

		$myFormElement = $this->getFormElement(array('bar', 'TYPO3.Form:MyType'));
		$page->addElement($myFormElement);

		$this->assertFalse($myFormElement->isRequired());
	}

	/**
	 * @test
	 */
	public function isRequiredReturnsTrueIfNotEmptyValidatorIsAdded() {
		$formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
		$page = new Page('asdf');
		$formDefinition->addPage($page);

		$myFormElement = $this->getFormElement(array('bar', 'TYPO3.Form:MyType'));
		$page->addElement($myFormElement);

		$myFormElement->addValidator(new \TYPO3\Flow\Validation\Validator\NotEmptyValidator());
		$this->assertTrue($myFormElement->isRequired());
	}

	/**
	 * @param array $constructorArguments
	 * @return \TYPO3\Form\Core\Model\AbstractFormElement
	 */
	protected function getFormElement(array $constructorArguments) {
		return $this->getMock('TYPO3\Form\Core\Model\AbstractFormElement', array('dummy'), $constructorArguments);
	}

	/**
	 * @param string $formElementIdentifier
	 * @return FormDefinition
	 */
	protected function getFormDefinitionWithProcessingRule($formElementIdentifier) {
		$mockProcessingRule = $this->getAccessibleMock('TYPO3\Form\Core\Model\ProcessingRule', array('dummy'));
		$mockProcessingRule->_set('validator', new \TYPO3\Flow\Validation\Validator\ConjunctionValidator());

		$formDefinition = $this->getMock('TYPO3\Form\Core\Model\FormDefinition', array('getProcessingRule'), array('foo'));
		$formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->will($this->returnValue($mockProcessingRule));

		return $formDefinition;
	}
}
