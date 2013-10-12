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
 * Test for ProcessingRule Domain Model
 * @covers \TYPO3\Form\Core\Model\ProcessingRule
 */
class ProcessingRuleTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $mockPropertyMapper;

	/**
	 * @var \TYPO3\Form\Core\Model\ProcessingRule
	 */
	protected $processingRule;

	public function setUp() {
		$this->mockPropertyMapper = $this->getMockBuilder('TYPO3\Flow\Property\PropertyMapper')->getMock();
		$this->processingRule = $this->getAccessibleMock('TYPO3\Form\Core\Model\ProcessingRule', array('dummy'));
		$this->processingRule->_set('propertyMapper', $this->mockPropertyMapper);
		$this->processingRule->_set('validator', new \TYPO3\Flow\Validation\Validator\ConjunctionValidator());
		$this->processingRule->_set('processingMessages', new \TYPO3\Flow\Error\Result());
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
		$mockValidator1 = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface');
		$this->processingRule->addValidator($mockValidator1);
		$mockValidator2 = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface');
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
	public function processingMessagesCanBeModifiedBeforeProcessing() {
		$this->processingRule->getProcessingMessages()->addError(new \TYPO3\Flow\Error\Error('Test'));
		$this->processingRule->process('Some Value');
		$this->assertTrue($this->processingRule->getProcessingMessages()->hasErrors());
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
		$mockPropertyMappingConfiguration = $this->getMockBuilder('TYPO3\Flow\Property\PropertyMappingConfiguration')->getMock();
		$this->processingRule->_set('propertyMappingConfiguration', $mockPropertyMappingConfiguration);

		$this->mockPropertyMapper->expects($this->once())->method('convert')->with('Some Value', 'SomeDataType', $mockPropertyMappingConfiguration)->will($this->returnValue('Converted Value'));
		$this->mockPropertyMapper->expects($this->any())->method('getMessages')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));
		$this->assertEquals('Converted Value', $this->processingRule->process('Some Value'));
	}

}
