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

use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;

/**
 * Test for AbstractFormElement Domain Model.
 *
 * @covers \Neos\Form\Core\Model\AbstractFormElement<extended>
 */
class AbstractFormElementTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function constructorSetsIdentifierAndType()
    {
        $element = $this->getFormElement(['myIdentifier', 'Neos.Form:MyType']);
        $this->assertSame('myIdentifier', $element->getIdentifier());
        $this->assertSame('Neos.Form:MyType', $element->getType());
    }

    public function invalidIdentifiers()
    {
        return [
            'Null Identifier'         => [null],
            'Integer Identifier'      => [42],
            'Empty String Identifier' => [''],
        ];
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\IdentifierNotValidException
     * @dataProvider invalidIdentifiers
     */
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        $this->getFormElement([$identifier, 'Neos.Form:MyType']);
    }

    /**
     * @test
     */
    public function labelCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $this->assertSame('', $formElement->getLabel());
        $formElement->setLabel('my label');
        $this->assertSame('my label', $formElement->getLabel());
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
        $this->assertSame('My Default Value', $formElement->getDefaultValue());
    }

    /**
     * @test
     */
    public function renderingOptionsCanBeSetAndGet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $this->assertSame([], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option1', 'value1');
        $this->assertSame(['option1' => 'value1'], $formElement->getRenderingOptions());
        $formElement->setRenderingOption('option2', 'value2');
        $this->assertSame(['option1' => 'value1', 'option2' => 'value2'], $formElement->getRenderingOptions());
    }

    /**
     * @test
     */
    public function rendererClassNameCanBeGetAndSet()
    {
        $formElement = $this->getFormElement(['foo', 'Neos.Form:MyType']);
        $this->assertNull($formElement->getRendererClassName());
        $formElement->setRendererClassName('MyRendererClassName');
        $this->assertSame('MyRendererClassName', $formElement->getRendererClassName());
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
        $this->assertSame('foo-bar', $myFormElement->getUniqueIdentifier());
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
     *
     * @param string $formIdentifier
     * @param string $elementIdentifier
     * @param string $expectedResult
     */
    public function getUniqueIdentifierReplacesSpecialCharactersByUnderscores($formIdentifier, $elementIdentifier, $expectedResult)
    {
        $formDefinition = new FormDefinition($formIdentifier);
        $myFormElement = $this->getFormElement([$elementIdentifier, 'Neos.Form:MyType']);
        $page = new Page('somePage');
        $formDefinition->addPage($page);

        $page->addElement($myFormElement);
        $this->assertSame($expectedResult, $myFormElement->getUniqueIdentifier());
    }

    /**
     * @test
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

        $myFormElement->addValidator(new \Neos\Flow\Validation\Validator\NotEmptyValidator());
        $this->assertTrue($myFormElement->isRequired());
    }

    /**
     * @param array $constructorArguments
     *
     * @return \Neos\Form\Core\Model\AbstractFormElement
     */
    protected function getFormElement(array $constructorArguments)
    {
        return $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->setConstructorArgs($constructorArguments)->getMock();
    }

    /**
     * @param string $formElementIdentifier
     *
     * @return FormDefinition
     */
    protected function getFormDefinitionWithProcessingRule($formElementIdentifier)
    {
        $mockProcessingRule = $this->getAccessibleMock(\Neos\Form\Core\Model\ProcessingRule::class, ['dummy']);
        $mockProcessingRule->_set('validator', new \Neos\Flow\Validation\Validator\ConjunctionValidator());

        $formDefinition = $this->getMockBuilder(\Neos\Form\Core\Model\FormDefinition::class)->setMethods(['getProcessingRule'])->setConstructorArgs(['foo'])->getMock();
        $formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->will($this->returnValue($mockProcessingRule));

        return $formDefinition;
    }
}
