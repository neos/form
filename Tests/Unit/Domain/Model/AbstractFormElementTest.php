<?php
namespace TYPO3\Form\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for AbstractFormElement Domain Model
 * @covers \TYPO3\Form\Core\Model\AbstractFormElement<extended>
 */
class AbstractFormElementTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

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
			'Empty String Identifier' => array('')
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
		$formElement = $this->getFormElement(array('foo', 'TYPO3.Form:MyType'));
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

		$myFormElement->addValidator(new \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator());
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
		$mockProcessingRule->_set('validator', new \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator());

		$formDefinition = $this->getMock('TYPO3\Form\Core\Model\FormDefinition', array('getProcessingRule'), array('foo'));
		$formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->will($this->returnValue($mockProcessingRule));

		return $formDefinition;
	}
}
?>