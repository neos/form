<?php
namespace TYPO3\Form\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Domain\Model\FormDefinition;
use TYPO3\Form\Domain\Model\Page;

/**
 * Test for Page Domain Model
 * @covers \TYPO3\Form\Domain\Model\Page
 * @covers \TYPO3\Form\Domain\Model\AbstractFormElement
 */
class PageTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function identifierSetInConstructorCanBeReadAgain() {
		$page = new Page('foo');
		$this->assertSame('foo', $page->getIdentifier());

		$page = new Page('bar');
		$this->assertSame('bar', $page->getIdentifier());
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
		new Page($identifier);
	}

	/**
	 * @test
	 */
	public function getElementsReturnsEmptyArrayByDefault() {
		$page = new Page('foo');
		$this->assertSame(array(), $page->getElements());
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
	 */
	public function aFormElementCanOnlyBeAttachedToASinglePage() {
		$element = $this->getMockBuilder('TYPO3\Form\Domain\Model\AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

		$page1 = new Page('bar1');
		$page2 = new Page('bar2');

		$page1->addElement($element);
		$page2->addElement($element);
	}

	/**
	 * @test
	 */
	public function addElementAddsElementAndSetsBackReferenceToPage() {
		$page = new Page('bar');
		$element = $this->getMockBuilder('TYPO3\Form\Domain\Model\AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$page->addElement($element);
		$this->assertSame(array($element), $page->getElements());
		$this->assertSame($page, $element->getParentPage());
	}

	/**
	 * @test
	 */
	public function createElementCreatesElementAndAddsItToForm() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'TYPO3.Form:MyElementType');

		$this->assertSame('myElement', $element->getIdentifier());
		$this->assertInstanceOf('TYPO3\Form\Domain\Model\GenericFormElement', $element);
		$this->assertSame('TYPO3.Form:MyElementType', $element->getType());
		$this->assertSame(array($element), $page->getElements());
	}

	/**
	 * @test
	 */
	public function createElementSetsAdditionalPropertiesInElement() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithAdditionalProperties');

		$this->assertSame('my label', $element->getLabel());
		$this->assertSame('This is the default value', $element->getDefaultValue());
		$this->assertSame(array('property1' => 'val1', 'property2' => 'val2'), $element->getProperties());
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
	 */
	public function createElementThrowsExceptionIfPageIsNotAttachedToParentForm() {
		$page = new Page('id');
		$page->createElement('myElement', 'TYPO3.Form:MyElementType');
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\TypeDefinitionNotFoundException
	 */
	public function createElementThrowsExceptionIfImplementationClassNameNotFound() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithoutImplementationClassName');
	}


	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\TypeDefinitionNotValidException
	 */
	public function createElementThrowsExceptionIfUnknownPropertyFoundInTypeDefinition() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithUnknownProperties');
	}



	protected function getDummyFormDefinition() {
		return new FormDefinition('myForm', array(
			'formElementTypes' => array(
				'TYPO3.Form:Page' => array(
					'implementationClassName' => 'TYPO3\Form\Domain\Model\Page'
				),
				'TYPO3.Form:MyElementType' => array(
					'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement'
				),
				'TYPO3.Form:MyElementTypeWithAdditionalProperties' => array(
					'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement',
					'label' => 'my label',
					'defaultValue' => 'This is the default value',
					'properties' => array(
						'property1' => 'val1',
						'property2' => 'val2'
					)
				),
				'TYPO3.Form:MyElementTypeWithoutImplementationClassName' => array(),
				'TYPO3.Form:MyElementTypeWithUnknownProperties' => array(
					'implementationClassName' => 'TYPO3\Form\Domain\Model\GenericFormElement',
					'unknownProperty' => 'foo'
				),

			)
		));
	}
}
?>