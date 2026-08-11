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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
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
 */
#[CoversClass(AbstractFormElement::class)]
class AbstractFormElementTest extends UnitTestCase
{
    #[Test]
    public function constructorSetsIdentifierAndType()
    {
        $element = $this->getFormElement(['myIdentifier', 'Neos.Form:MyType']);
        Assert::assertSame('myIdentifier', $element->getIdentifier());
        Assert::assertSame('Neos.Form:MyType', $element->getType());
    }

    public static function invalidIdentifiers()
    {
        return [
            'Null Identifier' => [null],
            'Integer Identifier' => [42],
            'Empty String Identifier' => [''],
        ];
    }

    #[DataProvider('invalidIdentifiers')]
    #[Test]
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        $this->expectException(IdentifierNotValidException::class);

        $this->getFormElement([$identifier, 'Neos.Form:MyType']);
    }

    #[Test]
    public function labelCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        Assert::assertSame('', $formElement->getLabel());
        $formElement->setLabel('my label');
        Assert::assertSame('my label', $formElement->getLabel());
    }

    #[Test]
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

    #[Test]
    public function renderingOptionsCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        Assert::assertSame([], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option1', 'value1');
        Assert::assertSame(['option1' => 'value1'], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option2', 'value2');
        Assert::assertSame(['option1' => 'value1', 'option2' => 'value2'], $formElement->getRenderingOptions());
    }

    #[Test]
    public function rendererClassNameCanBeGetAndSet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $this->assertNull($formElement->getRendererClassName());
        $formElement->setRendererClassName('MyRendererClassName');
        Assert::assertSame('MyRendererClassName', $formElement->getRendererClassName());
    }

    #[Test]
    public function getUniqueIdentifierBuildsIdentifierFromRootFormAndElementIdentifier()
    {
        $formDefinition = new FormDefinition('foo');
        $myFormElement = $this->getFormElement(['bar', 'Neos.Form:MyType']);
        $page = new Page('asdf');
        $formDefinition->addPage($page);

        $page->addElement($myFormElement);
        Assert::assertSame('foo-bar', $myFormElement->getUniqueIdentifier());
    }

    public static function getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider()
    {
        return [
            ['foo', 'bar', 'foo-bar'],
            ['foo.bar', 'baz', 'foo_bar-baz'],
            ['foo', 'bar?baz', 'foo-bar_baz'],
            ['SomeForm', 'SomeElement', 'someForm-SomeElement'],
        ];
    }

    /**
     * @param string $formIdentifier
     * @param string $elementIdentifier
     * @param string $expectedResult
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    #[DataProvider('getUniqueIdentifierReplacesSpecialCharactersByUnderscoresProvider')]
    #[Test]
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
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    #[Test]
    public function isRequiredReturnsFalseByDefault()
    {
        $formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
        $page = new Page('asdf');
        $formDefinition->addPage($page);

        $myFormElement = $this->getFormElement(['bar', 'Neos.Form:MyType']);
        $page->addElement($myFormElement);

        $this->assertFalse($myFormElement->isRequired());
    }

    #[Test]
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
        return $this->getMockBuilder(AbstractFormElement::class)->addMethods(['dummy'])->setConstructorArgs($constructorArguments)->getMock();
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

        $formDefinition = $this->getMockBuilder(FormDefinition::class)->onlyMethods(['getProcessingRule'])->setConstructorArgs(['foo'])->getMock();
        $formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->willReturn($mockProcessingRule);

        return $formDefinition;
    }
}
