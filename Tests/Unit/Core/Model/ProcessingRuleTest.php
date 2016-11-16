<?php
namespace TYPO3\Form\Tests\Unit\Core\Model;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Form\Core\Model\FormDefinition;
use TYPO3\Form\Core\Model\Page;

/**
 * Test for ProcessingRule Domain Model
 * @covers \TYPO3\Form\Core\Model\ProcessingRule
 */
class ProcessingRuleTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\Flow\Property\PropertyMapper
     */
    protected $mockPropertyMapper;

    /**
     * @var \TYPO3\Form\Core\Model\ProcessingRule
     */
    protected $processingRule;

    public function setUp()
    {
        $this->mockPropertyMapper = $this->getMockBuilder(\TYPO3\Flow\Property\PropertyMapper::class)->getMock();
        $this->processingRule = $this->getAccessibleMock(\TYPO3\Form\Core\Model\ProcessingRule::class, array('dummy'));
        $this->processingRule->_set('propertyMapper', $this->mockPropertyMapper);
        $this->processingRule->_set('validator', new \TYPO3\Flow\Validation\Validator\ConjunctionValidator());
        $this->processingRule->_set('processingMessages', new \TYPO3\Flow\Error\Result());
    }

    /**
     * @test
     */
    public function getDataTypeReturnsNullByDefault()
    {
        $this->assertNull($this->processingRule->getDataType());
    }

    /**
     * @test
     */
    public function getDataTypeReturnsSpecifiedDataType()
    {
        $this->processingRule->setDataType('SomeDataType');
        $this->assertSame('SomeDataType', $this->processingRule->getDataType());
    }

    /**
     * @test
     */
    public function getValidatorsReturnsAnEmptyCollectionByDefault()
    {
        $this->assertSame(0, count($this->processingRule->getValidators()));
    }

    /**
     * @test
     */
    public function getValidatorsReturnsPreviouslyAddedValidators()
    {
        $mockValidator1 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $this->processingRule->addValidator($mockValidator1);
        $mockValidator2 = $this->createMock(\TYPO3\Flow\Validation\Validator\ValidatorInterface::class);
        $this->processingRule->addValidator($mockValidator2);

        $validators = $this->processingRule->getValidators();
        $this->assertTrue($validators->contains($mockValidator1));
        $this->assertTrue($validators->contains($mockValidator2));
    }

    /**
     * @test
     */
    public function processReturnsTheUnchangedValueByDefault()
    {
        $actualResult = $this->processingRule->process('Some Value');
        $this->assertEquals('Some Value', $actualResult);
    }

    /**
     * @test
     */
    public function processingMessagesCanBeModifiedBeforeProcessing()
    {
        $this->processingRule->getProcessingMessages()->addError(new \TYPO3\Flow\Error\Error('Test'));
        $this->processingRule->process('Some Value');
        $this->assertTrue($this->processingRule->getProcessingMessages()->hasErrors());
    }

    /**
     * @test
     */
    public function processDoesNotConvertValueIfTargetTypeIsNotSpecified()
    {
        $this->mockPropertyMapper->expects($this->never())->method('convert');
        $this->processingRule->process('Some Value');
    }

    /**
     * @test
     */
    public function processConvertsValueIfDataTypeIsSpecified()
    {
        $this->processingRule->setDataType('SomeDataType');
        $mockPropertyMappingConfiguration = $this->getMockBuilder(\TYPO3\Flow\Property\PropertyMappingConfiguration::class)->getMock();
        $this->processingRule->_set('propertyMappingConfiguration', $mockPropertyMappingConfiguration);

        $this->mockPropertyMapper->expects($this->once())->method('convert')->with('Some Value', 'SomeDataType', $mockPropertyMappingConfiguration)->will($this->returnValue('Converted Value'));
        $this->mockPropertyMapper->expects($this->any())->method('getMessages')->will($this->returnValue(new \TYPO3\Flow\Error\Result()));
        $this->assertEquals('Converted Value', $this->processingRule->process('Some Value'));
    }
}
