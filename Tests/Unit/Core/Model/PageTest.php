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

use Neos\Form\Core\Model\Page;

/**
 * Test for Page Domain Model.
 *
 * @covers \Neos\Form\Core\Model\Page<extended>
 * @covers \Neos\Form\Core\Model\AbstractFormElement<extended>
 */
class PageTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function identifierSetInConstructorCanBeReadAgain()
    {
        $page = new Page('foo');
        $this->assertSame('foo', $page->getIdentifier());

        $page = new Page('bar');
        $this->assertSame('bar', $page->getIdentifier());
    }

    /**
     * @test
     */
    public function defaultTypeIsCorrect()
    {
        $page = new Page('foo');
        $this->assertSame('Neos.Form:Page', $page->getType());
    }

    /**
     * @test
     */
    public function typeCanBeOverridden()
    {
        $page = new Page('foo', 'TYPO3.Foo:Bar');
        $this->assertSame('TYPO3.Foo:Bar', $page->getType());
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
     * @expectedException Neos\Form\Exception\IdentifierNotValidException
     * @dataProvider invalidIdentifiers
     */
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        new Page($identifier);
    }

    /**
     * @test
     */
    public function getElementsReturnsEmptyArrayByDefault()
    {
        $page = new Page('foo');
        $this->assertSame([], $page->getElements());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsEmptyArrayByDefault()
    {
        $page = new Page('foo');
        $this->assertSame([], $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsFirstLevelFormElements()
    {
        $page = new Page('foo');
        $element1 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element2 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $page->addElement($element1);
        $page->addElement($element2);
        $this->assertSame([$element1, $element2], $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsRecursiveFormElementsInCorrectOrder()
    {
        $page = new Page('foo');
        $element1 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element2 = $this->getMockBuilder(\Neos\Form\FormElements\Section::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element21 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element22 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element2->addElement($element21);
        $element2->addElement($element22);
        $element3 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

        $page->addElement($element1);
        $page->addElement($element2);
        $page->addElement($element3);
        $this->assertSame([$element1, $element2, $element21, $element22, $element3], $page->getElementsRecursively());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function aFormElementCanOnlyBeAttachedToASinglePage()
    {
        $element = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

        $page1 = new Page('bar1');
        $page2 = new Page('bar2');

        $page1->addElement($element);
        $page2->addElement($element);
    }

    /**
     * @test
     */
    public function addElementAddsElementAndSetsBackReferenceToPage()
    {
        $page = new Page('bar');
        $element = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $page->addElement($element);
        $this->assertSame([$element], $page->getElements());
        $this->assertSame($page, $element->getParentRenderable());
    }

    /**
     * @test
     */
    public function createElementCreatesElementAndAddsItToForm()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementType');

        $this->assertSame('myElement', $element->getIdentifier());
        $this->assertInstanceOf(\Neos\Form\FormElements\GenericFormElement::class, $element);
        $this->assertSame('Neos.Form:MyElementType', $element->getType());
        $this->assertSame([$element], $page->getElements());
    }

    /**
     * @test
     */
    public function createElementSetsAdditionalPropertiesInElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementTypeWithAdditionalProperties');

        $this->assertSame('my label', $element->getLabel());
        $this->assertSame('This is the default value', $element->getDefaultValue());
        $this->assertSame(['property1' => 'val1', 'property2' => 'val2'], $element->getProperties());
        $this->assertSame(['ro1' => 'rv1', 'ro2' => 'rv2'], $element->getRenderingOptions());
        $this->assertSame('MyRendererClassName', $element->getRendererClassName());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function createElementThrowsExceptionIfPageIsNotAttachedToParentForm()
    {
        $page = new Page('id');
        $page->createElement('myElement', 'Neos.Form:MyElementType');
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotFoundException
     */
    public function createElementThrowsExceptionIfImplementationClassNameNotFound()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementTypeWithoutImplementationClassName');
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotValidException
     */
    public function createElementThrowsExceptionIfImplementationClassNameDoesNotImplementFormElementInterface()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementTypeWhichDoesNotImplementFormElementInterface');
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotValidException
     */
    public function createElementThrowsExceptionIfUnknownPropertyFoundInTypeDefinition()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementTypeWithUnknownProperties');
    }

    /**
     * @test
     */
    public function moveElementBeforeMovesElementBeforeReferenceElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element1 = $page->createElement('myElement', 'Neos.Form:MyElementType');
        $element2 = $page->createElement('myElement2', 'Neos.Form:MyElementType');

        $this->assertSame([$element1, $element2], $page->getElements());
        $page->moveElementBefore($element2, $element1);
        $this->assertSame([$element2, $element1], $page->getElements());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function moveElementBeforeThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $page2 = $formDefinition->createPage('myPage2');

        $element1 = $page1->createElement('myElement', 'Neos.Form:MyElementType');
        $element2 = $page2->createElement('myElement2', 'Neos.Form:MyElementType');

        $page1->moveElementBefore($element1, $element2);
    }

    /**
     * @test
     */
    public function moveElementAfterMovesElementAfterReferenceElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element1 = $page->createElement('myElement', 'Neos.Form:MyElementType');
        $element2 = $page->createElement('myElement2', 'Neos.Form:MyElementType');

        $this->assertSame([$element1, $element2], $page->getElements());
        $page->moveElementAfter($element1, $element2);
        $this->assertSame([$element2, $element1], $page->getElements());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function moveElementAfterThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $page2 = $formDefinition->createPage('myPage2');

        $element1 = $page1->createElement('myElement', 'Neos.Form:MyElementType');
        $element2 = $page2->createElement('myElement2', 'Neos.Form:MyElementType');

        $page1->moveElementAfter($element1, $element2);
    }

    /**
     * @test
     */
    public function removeElementRemovesElementFromCurrentPageAndUnregistersItFromForm()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $element1 = $page1->createElement('myElement', 'Neos.Form:MyElementType');

        $page1->removeElement($element1);

        $this->assertSame([], $page1->getElements());
        $this->assertNull($formDefinition->getElementByIdentifier('myElement'));

        $this->assertNull($element1->getParentRenderable());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function removeElementThrowsExceptionIfElementIsNotOnCurrentPage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $element1 = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

        $page1->removeElement($element1);
    }

    /**
     * @test
     */
    public function validatorKeyCorrectlyAddsValidator()
    {
        $formDefinition = $this->getDummyFormDefinition();

        $mockProcessingRule = $this->getAccessibleMock(\Neos\Form\Core\Model\ProcessingRule::class, ['dummy']);
        $mockProcessingRule->_set('validator', new \Neos\Flow\Validation\Validator\ConjunctionValidator());
        $formDefinition->expects($this->any())->method('getProcessingRule')->with('asdf')->will($this->returnValue($mockProcessingRule));

        $page1 = $formDefinition->createPage('myPage1');
        $el = $page1->createElement('asdf', 'Neos.Form:MyElementWithValidator');
        $this->assertTrue($el->isRequired());
        $validators = $el->getValidators();
        $validators = iterator_to_array($validators);
        $this->assertSame(2, count($validators));
        $this->assertInstanceOf(\Neos\Flow\Validation\Validator\StringLengthValidator::class, $validators[0]);
        $this->assertSame(['minimum' => 10, 'maximum' => PHP_INT_MAX], $validators[0]->getOptions());
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\ValidatorPresetNotFoundException
     */
    public function validatorKeyThrowsExceptionIfValidatorPresetIsNotFound()
    {
        $formDefinition = $this->getDummyFormDefinition();

        $page1 = $formDefinition->createPage('myPage1');
        $el = $page1->createElement('asdf', 'Neos.Form:MyElementWithBrokenValidator');
    }

    protected function getDummyFormDefinition()
    {
        $formDefinitionConstructorArguments = ['myForm', [
            'validatorPresets' => [
                'MyValidatorIdentifier' => [
                    'implementationClassName' => \Neos\Flow\Validation\Validator\StringLengthValidator::class,
                ],
                'MyOtherValidatorIdentifier' => [
                    'implementationClassName' => \Neos\Flow\Validation\Validator\NotEmptyValidator::class,
                ],
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                ],
                'Neos.Form:MyElementType' => [
                    'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                ],
                'Neos.Form:MyElementTypeWithAdditionalProperties' => [
                    'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                    'label'                   => 'my label',
                    'defaultValue'            => 'This is the default value',
                    'properties'              => [
                        'property1' => 'val1',
                        'property2' => 'val2',
                    ],
                    'renderingOptions' => [
                        'ro1' => 'rv1',
                        'ro2' => 'rv2',
                    ],
                    'rendererClassName' => 'MyRendererClassName',
                ],
                'Neos.Form:MyElementTypeWithoutImplementationClassName' => [],
                'Neos.Form:MyElementTypeWithUnknownProperties'          => [
                    'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                    'unknownProperty'         => 'foo',
                ],
                'Neos.Form:MyElementTypeWhichDoesNotImplementFormElementInterface' => [
                    'implementationClassName' => \Neos\Form\Factory\ArrayFormFactory::class,
                ],
                'Neos.Form:MyElementWithValidator' => [
                    'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                    'validators'              => [
                        [
                            'identifier' => 'MyValidatorIdentifier',
                            'options'    => ['minimum' => 10],
                        ],
                        [
                            'identifier' => 'MyOtherValidatorIdentifier',
                        ],
                    ],
                ],
                'Neos.Form:MyElementWithBrokenValidator' => [
                    'implementationClassName' => \Neos\Form\FormElements\GenericFormElement::class,
                    'validators'              => [
                        [
                            'identifier' => 'nonExisting',
                        ],
                    ],
                ],

            ],
        ]];

        $formDefinition = $this->getMockBuilder(\Neos\Form\Core\Model\FormDefinition::class)->setMethods(['getProcessingRule'])->setConstructorArgs($formDefinitionConstructorArguments)->getMock();

        return $formDefinition;
    }
}
