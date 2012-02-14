<?php
namespace TYPO3\Form\Tests\Unit\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for ProcessingRule Domain Model
 * @covers \TYPO3\Form\Core\Model\ProcessingRule
 */
class ProcessingRuleTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\FLOW3\Property\PropertyMapper
	 */
	protected $mockPropertyMapper;

	/**
	 * @var \TYPO3\Form\Core\Model\ProcessingRule
	 */
	protected $processingRule;

	public function setUp() {
		$this->mockPropertyMapper = $this->getMockBuilder('TYPO3\FLOW3\Property\PropertyMapper')->getMock();
		$this->processingRule = $this->getAccessibleMock('TYPO3\Form\Core\Model\ProcessingRule', array('dummy'));
		$this->processingRule->_set('propertyMapper', $this->mockPropertyMapper);
		$this->processingRule->_set('validator', new \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator());
		$this->processingRule->_set('processingMessages', new \TYPO3\FLOW3\Error\Result());
	}

	/**
	 * @test
	 */
	public function getDataTypeReturnsNullByDefault() {
		$this->assertNull($this->processingRule->getDataType());
	}

	/**
	 * @test
	 */
	public function getDataTypeReturnsSpecifiedDataType() {
		$this->processingRule->setDataType('SomeDataType');
		$this->assertSame('SomeDataType', $this->processingRule->getDataType());
	}

	/**
	 * @test
	 */
	public function getValidatorsReturnsAnEmptyCollectionByDefault() {
		$this->assertSame(0, count($this->processingRule->getValidators()));
	}

	/**
	 * @test
	 */
	public function getValidatorsReturnsPreviouslyAddedValidators() {
		$mockValidator1 = $this->getMock('TYPO3\FLOW3\Validation\Validator\ValidatorInterface');
		$this->processingRule->addValidator($mockValidator1);
		$mockValidator2 = $this->getMock('TYPO3\FLOW3\Validation\Validator\ValidatorInterface');
		$this->processingRule->addValidator($mockValidator2);

		$validators = $this->processingRule->getValidators();
		$this->assertTrue($validators->contains($mockValidator1));
		$this->assertTrue($validators->contains($mockValidator2));
	}

	/**
	 * @test
	 */
	public function processReturnsTheUnchangedValueByDefault() {
		$actualResult = $this->processingRule->process('Some Value');
		$this->assertEquals('Some Value', $actualResult);
	}

	/**
	 * @test
	 */
	public function processResetsProcessingMessages() {
		$testProcessingMessages = new \TYPO3\FLOW3\Error\Result();
		$testProcessingMessages->addError(new \TYPO3\FLOW3\Error\Error('Test'));
		$this->processingRule->_set('processingMessages', $testProcessingMessages);

		$this->assertTrue($this->processingRule->getProcessingMessages()->hasErrors());
		$this->processingRule->process('Some Value');
		$this->assertFalse($this->processingRule->getProcessingMessages()->hasErrors());
	}

	/**
	 * @test
	 */
	public function processDoesNotConvertValueIfTargetTypeIsNotSpecified() {
		$this->mockPropertyMapper->expects($this->never())->method('convert');
		$this->processingRule->process('Some Value');
	}

	/**
	 * @test
	 */
	public function processConvertsValueIfDataTypeIsSpecified() {
		$this->processingRule->setDataType('SomeDataType');
		$mockPropertyMappingConfiguration = $this->getMockBuilder('TYPO3\FLOW3\Property\PropertyMappingConfiguration')->getMock();
		$this->processingRule->_set('propertyMappingConfiguration', $mockPropertyMappingConfiguration);

		$this->mockPropertyMapper->expects($this->once())->method('convert')->with('Some Value', 'SomeDataType', $mockPropertyMappingConfiguration)->will($this->returnValue('Converted Value'));
		$this->mockPropertyMapper->expects($this->any())->method('getMessages')->will($this->returnValue(new \TYPO3\FLOW3\Error\Result()));
		$this->assertEquals('Converted Value', $this->processingRule->process('Some Value'));
	}

}
?>