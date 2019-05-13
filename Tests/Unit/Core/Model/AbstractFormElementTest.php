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

use Neos\Flow\Tests\UnitTestCase;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Validator\ConjunctionValidator;
use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;
use Neos\Form\Core\Model\ProcessingRule;
use Neos\Form\Exception\FormDefinitionConsistencyException;
use Neos\Form\Exception\IdentifierNotValidException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;

/**
 * Test for AbstractFormElement Domain Model
 * @covers \Neos\Form\Core\Model\AbstractFormElement<extended>
 */
class AbstractFormElementTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructorSetsIdentifierAndType()
    {
        $element = $this->getFormElement(['myIdentifier', 'Neos.Form:MyType']);
        Assert::assertSame('myIdentifier', $element->getIdentifier());
        Assert::assertSame('Neos.Form:MyType', $element->getType());
    }

    public function invalidIdentifiers()
    {
        return [
            'Null Identifier' => [null],
            'Integer Identifier' => [42],
            'Empty String Identifier' => [''],
        ];
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     */
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        $this->expectException(IdentifierNotValidException::class);

        $this->getFormElement([$identifier, 'Neos.Form:MyType']);
    }

    /**
     * @test
     */
    public function labelCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        Assert::assertSame('', $formElement->getLabel());
        $formElement->setLabel('my label');
        Assert::assertSame('my label', $formElement->getLabel());
    }

    /**
     * @test
     */
    public function defaultValueCanBeSetAndGet()
    {
        $formDefinition = new FormDefinition('foo');
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $page = new Page('page');
        $formDefinition->addPage($page);
        $page->addElement($formElement);
        $this->assertNull($formElement->getDefaultValue());
        $formElement->setDefaultValue('My Default Value');
        Assert::assertSame('My Default Value', $formElement->getDefaultValue());
    }

    /**
     * @test
     */
    public function renderingOptionsCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        Assert::assertSame([], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option1', 'value1');
        Assert::assertSame(['option1' => 'value1'], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option2', 'value2');
        Assert::assertSame(['option1' => 'value1', 'option2' => 'value2'], $formElement->getRenderingOptions());
    }

    /**
     * @test
     */
    public function rendererClassNameCanBeGetAndSet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $this->assertNull($formElement->getRendererClassName());
        $formElement->setRendererClassName('MyRendererClassName');
        Assert::assertSame('MyRendererClassName', $formElement->getRendererClassName());
    }

    /**
     * @test
     */
    public function getUniqueIdentifierBuildsIdentifierFromRootFormAndElementIdentifier()
    {
        $formDefinition = new FormDefinition('foo');
        $myFormElement = $this->getFormElement(['bar', 'Neos.Form:MyType']);
        $page = new Page('asdf');
        $formDefinition->addPage($page);

        $page->addElement($myFormElement);
        Assert::assertSame('foo-bar', $myFormElement->getUniqueIdentifier());
    }

    public function getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider()
    {
        return [
            ['foo', 'bar', 'foo-bar'],
            ['foo.bar', 'baz', 'foo_bar-baz'],
            ['foo', 'bar?baz', 'foo-bar_baz'],
            ['SomeForm', 'SomeElement', 'someForm-SomeElement'],
        ];
    }

    /**
     * @test
     * @dataProvider getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider
     * @param string $formIdentifier
     * @param string $elementIdentifier
     * @param string $expectedResult
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function getUniqueIdentifierReplacesSpecialCharactersByUnderscores($formIdentifier, $elementIdentifier, $expectedResult)
    {
        $formDefinition = new FormDefinition($formIdentifier);
        $myFormElement = $this->getFormElement([$elementIdentifier, 'Neos.Form:MyType']);
        $page = new Page('somePage');
        $formDefinition->addPage($page);

        $page->addElement($myFormElement);
        Assert::assertSame($expectedResult, $myFormElement->getUniqueIdentifier());
    }

    /**
     * @test
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function isRequiredReturnsFalseByDefault()
    {
        $formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
        $page = new Page('asdf');
        $formDefinition->addPage($page);

        $myFormElement = $this->getFormElement(['bar', 'Neos.Form:MyType']);
        $page->addElement($myFormElement);

        $this->assertFalse($myFormElement->isRequired());
    }

    /**
     * @test
     */
    public function isRequiredReturnsTrueIfNotEmptyValidatorIsAdded()
    {
        $formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
        $page = new Page('asdf');
        $formDefinition->addPage($page);

        $myFormElement = $this->getFormElement(['bar', 'Neos.Form:MyType']);
        $page->addElement($myFormElement);

        $myFormElement->addValidator(new NotEmptyValidator());
        $this->assertTrue($myFormElement->isRequired());
    }

    /**
     * @param array $constructorArguments
     * @return AbstractFormElement
     * @throws ReflectionException
     */
    protected function getFormElement(array $constructorArguments)
    {
        return $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->setConstructorArgs($constructorArguments)->getMock();
    }

    /**
     * @param string $formElementIdentifier
     * @return MockObject
     * @throws InvalidValidationOptionsException
     * @throws ReflectionException
     */
    protected function getFormDefinitionWithProcessingRule($formElementIdentifier)
    {
        $mockProcessingRule = $this->getAccessibleMock(ProcessingRule::class, ['dummy']);
        $mockProcessingRule->_set('validator', new ConjunctionValidator());

        $formDefinition = $this->getMockBuilder(FormDefinition::class)->setMethods(['getProcessingRule'])->setConstructorArgs(['foo'])->getMock();
        $formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->will($this->returnValue($mockProcessingRule));

        return $formDefinition;
    }
}
