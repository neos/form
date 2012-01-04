<?php
namespace TYPO3\Form\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Domain\Model\FormDefinition;
use TYPO3\Form\Domain\Model\Page;

/**
 * Test for FormDefinition Domain Model
 * @covers \TYPO3\Form\Domain\Model\FormDefinition
 * @covers \TYPO3\Form\Domain\Model\Page
 */
class FormDefinitionTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function identifierSetInConstructorCanBeReadAgain() {
		$formDefinition = new FormDefinition('foo');
		$this->assertSame('foo', $formDefinition->getIdentifier());

		$formDefinition = new FormDefinition('bar');
		$this->assertSame('bar', $formDefinition->getIdentifier());
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
		new FormDefinition($identifier);
	}

	/**
	 * @test
	 */
	public function getPagesReturnsEmptyArrayByDefault() {
		$formDefinition = new FormDefinition('foo');
		$this->assertSame(array(), $formDefinition->getPages());
		$this->assertNull($formDefinition->getPageByIndex(0));
		$this->assertNull($formDefinition->getPageByIndex(1));
	}

	/**
	 * @test
	 */
	public function addPageAddsPageToPagesArrayAndSetsBackReferenceToForm() {
		$formDefinition = new FormDefinition('foo');
		$page = new Page('bar');
		$formDefinition->addPage($page);
		$this->assertSame(array($page), $formDefinition->getPages());
		$this->assertSame($formDefinition, $page->getParentForm());

		$this->assertSame($page, $formDefinition->getPageByIndex(0));
		$this->assertNull($formDefinition->getPageByIndex(1));
	}

	/**
	 * @test
	 */
	public function addPageAddsIndexToPage() {
		$formDefinition = new FormDefinition('foo');
		$page1 = new Page('bar1');
		$formDefinition->addPage($page1);

		$page2 = new Page('bar2');
		$formDefinition->addPage($page2);

		$this->assertSame(0, $page1->getIndex());
		$this->assertSame(1, $page2->getIndex());
	}

	/**
	 * @test
	 */
	public function getElementByIdentifierReturnsElementsWhichAreAlreadyAttachedToThePage() {
		$page = new Page('bar');
		$mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
		$page->addElement($mockFormElement);

		$formDefinition = new FormDefinition('foo');
		$formDefinition->addPage($page);

		$this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
	}

	/**
	 * @test
	 */
	public function getElementByIdentifierReturnsElementsWhichAreLazilyAttachedToThePage() {
		$formDefinition = new FormDefinition('foo');

		$page = new Page('bar');
		$formDefinition->addPage($page);

		$mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
		$page->addElement($mockFormElement);
		$this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
	}

	/**
	 * @test
	 */
	public function bindReturnsBoundFormRuntime() {
		$formDefinition = new FormDefinition('foo');

		$mockRequest = $this->getMockBuilder('TYPO3\FLOW3\MVC\Web\Request')->getMock();

		$form = $formDefinition->bind($mockRequest);
		$this->assertInstanceOf('TYPO3\Form\Domain\Model\FormRuntime', $form);
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\DuplicateFormElementException
	 */
	public function attachingTwoElementsWithSameIdentifierToFormThrowsException1() {
		$mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
		$mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

		$page = new Page('bar');
		$page->addElement($mockFormElement1);
		$page->addElement($mockFormElement2);

		$formDefinition = new FormDefinition('foo');
		$formDefinition->addPage($page);
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\DuplicateFormElementException
	 */
	public function attachingTwoElementsWithSameIdentifierToFormThrowsException2() {
		$mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
		$mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

		$page = new Page('bar');
		$page->addElement($mockFormElement1);

		$formDefinition = new FormDefinition('foo');
		$formDefinition->addPage($page);

		$page->addElement($mockFormElement2);
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
	 */
	public function aPageCanOnlyBeAttachedToASingleFormDefinition() {
		$page = new Page('bar');

		$formDefinition1 = new FormDefinition('foo1');
		$formDefinition2 = new FormDefinition('foo2');

		$formDefinition1->addPage($page);
		$formDefinition2->addPage($page);
	}

	/**
	 * @param string $identifier
	 * @return \TYPO3\Form\Domain\Model\FormElementInterface
	 */
	protected function getMockFormElement($identifier) {
		$mockFormElement = $this->getMockBuilder('TYPO3\Form\Domain\Model\FormElementInterface')->getMock();
		$mockFormElement->expects($this->any())->method('getIdentifier')->will($this->returnValue($identifier));

		return $mockFormElement;
	}
}
?>