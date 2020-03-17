<?php
namespace Neos\Form\Tests\Unit\Core\Model;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Error;
use Neos\Error\Messages\Result;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Flow\Validation\Validator\ConjunctionValidator;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Form\Core\Model\ProcessingRule;
use PHPUnit\Framework\Assert;

/**
 * Test for ProcessingRule Domain Model
 * @covers \Neos\Form\Core\Model\ProcessingRule
 */
class ProcessingRuleTest extends UnitTestCase
{
    /**
     * @var PropertyMapper
     */
    protected $mockPropertyMapper;

    /**
     * @var ProcessingRule
     */
    protected $processingRule;

    public function setUp(): void
    {
        $this->mockPropertyMapper = $this->getMockBuilder(PropertyMapper::class)->getMock();

        $this->processingRule = new ProcessingRule();

        $this->inject($this->processingRule, 'propertyMapper', $this->mockPropertyMapper);
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
        Assert::assertSame('SomeDataType', $this->processingRule->getDataType());
    }

    /**
     * @test
     */
    public function getValidatorsReturnsAnEmptyCollectionByDefault()
    {
        Assert::assertSame(0, count($this->processingRule->getValidators()));
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function getValidatorsReturnsPreviouslyAddedValidators()
    {
        /** @var ValidatorInterface $mockValidator1 */
        $mockValidator1 = $this->createMock(ValidatorInterface::class);
        $this->processingRule->addValidator($mockValidator1);
        /** @var ValidatorInterface $mockValidator2 */
        $mockValidator2 = $this->createMock(ValidatorInterface::class);
        $this->processingRule->addValidator($mockValidator2);

        $validators = $this->processingRule->getValidators();
        Assert::assertTrue($validators->contains($mockValidator1));
        Assert::assertTrue($validators->contains($mockValidator2));
    }

    /**
     * @test
     */
    public function processReturnsTheUnchangedValueByDefault()
    {
        $actualResult = $this->processingRule->process('Some Value');
        Assert::assertEquals('Some Value', $actualResult);
    }

    /**
     * @test
     */
    public function processingMessagesCanBeModifiedBeforeProcessing()
    {
        $this->processingRule->getProcessingMessages()->addError(new Error('Test'));
        $this->processingRule->process('Some Value');
        Assert::assertTrue($this->processingRule->getProcessingMessages()->hasErrors());
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
        $propertyMappingConfiguration = $this->processingRule->getPropertyMappingConfiguration();

        $this->mockPropertyMapper->expects($this->once())->method('convert')->with('Some Value', 'SomeDataType', $propertyMappingConfiguration)->will($this->returnValue('Converted Value'));
        $this->mockPropertyMapper->expects($this->any())->method('getMessages')->will($this->returnValue(new Result()));
        Assert::assertEquals('Converted Value', $this->processingRule->process('Some Value'));
    }
}
