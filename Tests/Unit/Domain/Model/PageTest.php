<?php
namespace TYPO3\Form\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Domain\Model\Form;
use TYPO3\Form\Domain\Model\Page;

/**
 * Test for Page Domain Model
 * @covers \TYPO3\Form\Domain\Model\Page
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
	 */
	public function addElementAddsElementAndSetsBackReferenceToPage() {
		$page = new Page('bar');
		$element = $this->getMockBuilder('TYPO3\Form\Domain\Model\AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$page->addElement($element);
		$this->assertSame(array($element), $page->getElements());
		$this->assertSame($page, $element->getParentPage());
	}
}
?>