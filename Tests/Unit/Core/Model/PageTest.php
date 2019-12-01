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
use Neos\Flow\Validation\Validator\ConjunctionValidator;
use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Flow\Validation\Validator\StringLengthValidator;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;
use Neos\Form\Core\Model\ProcessingRule;
use Neos\Form\Exception\FormDefinitionConsistencyException;
use Neos\Form\Exception\IdentifierNotValidException;
use Neos\Form\Exception\TypeDefinitionNotFoundException;
use Neos\Form\Exception\TypeDefinitionNotValidException;
use Neos\Form\Exception\ValidatorPresetNotFoundException;
use Neos\Form\Factory\ArrayFormFactory;
use Neos\Form\FormElements\GenericFormElement;
use Neos\Form\FormElements\Section;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Page Domain Model
 * @covers \Neos\Form\Core\Model\Page<extended>
 * @covers \Neos\Form\Core\Model\AbstractFormElement<extended>
 */
class PageTest extends UnitTestCase
{
    /**
     * @test
     */
    public function identifierSetInConstructorCanBeReadAgain()
    {
        $page = new Page('foo');
        Assert::assertSame('foo', $page->getIdentifier());

        $page = new Page('bar');
        Assert::assertSame('bar', $page->getIdentifier());
    }

    /**
     * @test
     */
    public function defaultTypeIsCorrect()
    {
        $page = new Page('foo');
        Assert::assertSame('Neos.Form:Page', $page->getType());
    }

    /**
     * @test
     */
    public function typeCanBeOverridden()
    {
        $page = new Page('foo', 'Neos.Foo:Bar');
        Assert::assertSame('Neos.Foo:Bar', $page->getType());
    }

    public function invalidIdentifiers()
    {
        return [
            'Null Identifier' => [null],
            'Integer Identifier' => [42],
            'Empty String Identifier' => ['']
        ];
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     * @param mixed $identifier
     * @throws IdentifierNotValidException
     */
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        $this->expectException(IdentifierNotValidException::class);
        new Page($identifier);
    }

    /**
     * @test
     */
    public function getElementsReturnsEmptyArrayByDefault()
    {
        $page = new Page('foo');
        Assert::assertSame([], $page->getElements());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsEmptyArrayByDefault()
    {
        $page = new Page('foo');
        Assert::assertSame([], $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsFirstLevelFormElements()
    {
        $page = new Page('foo');
        /** @var AbstractFormElement|MockObject $element1 */
        $element1 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        /** @var AbstractFormElement|MockObject $element2 */
        $element2 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $page->addElement($element1);
        $page->addElement($element2);
        Assert::assertSame([$element1, $element2], $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsRecursiveFormElementsInCorrectOrder()
    {
        $page = new Page('foo');

        /** @var AbstractFormElement|MockObject $element1 */
        $element1 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        /** @var Section|MockObject $element2 */
        $element2 = $this->getMockBuilder(Section::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        /** @var AbstractFormElement|MockObject $element21 */
        $element21 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        /** @var AbstractFormElement|MockObject $element22 */
        $element22 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $element2->addElement($element21);
        $element2->addElement($element22);
        /** @var AbstractFormElement|MockObject $element3 */
        $element3 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

        $page->addElement($element1);
        $page->addElement($element2);
        $page->addElement($element3);
        Assert::assertSame([$element1, $element2, $element21, $element22, $element3], $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function aFormElementCanOnlyBeAttachedToASinglePage()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

        /** @var AbstractFormElement|MockObject $element */
        $element = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

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
        /** @var AbstractFormElement|MockObject $element */
        $element = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $page->addElement($element);
        Assert::assertSame([$element], $page->getElements());
        Assert::assertSame($page, $element->getParentRenderable());
    }

    /**
     * @test
     */
    public function createElementCreatesElementAndAddsItToForm()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementType');

        Assert::assertSame('myElement', $element->getIdentifier());
        $this->assertInstanceOf(GenericFormElement::class, $element);
        Assert::assertSame('Neos.Form:MyElementType', $element->getType());
        Assert::assertSame([$element], $page->getElements());
    }

    /**
     * @test
     */
    public function createElementSetsAdditionalPropertiesInElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'Neos.Form:MyElementTypeWithAdditionalProperties');

        Assert::assertSame('my label', $element->getLabel());
        Assert::assertSame('This is the default value', $element->getDefaultValue());
        Assert::assertSame(['property1' => 'val1', 'property2' => 'val2'], $element->getProperties());
        Assert::assertSame(['ro1' => 'rv1', 'ro2' => 'rv2'], $element->getRenderingOptions());
        Assert::assertSame('MyRendererClassName', $element->getRendererClassName());
    }

    /**
     * @test
     */
    public function createElementThrowsExceptionIfPageIsNotAttachedToParentForm()
    {
        $this->expectException(FormDefinitionConsistencyException::class);
        $page = new Page('id');
        $page->createElement('myElement', 'Neos.Form:MyElementType');
    }

    /**
     * @test
     */
    public function createElementThrowsExceptionIfImplementationClassNameNotFound()
    {
        $this->expectException(TypeDefinitionNotFoundException::class);

        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $page->createElement('myElement', 'Neos.Form:MyElementTypeWithoutImplementationClassName');
    }

    /**
     * @test
     */
    public function createElementThrowsExceptionIfImplementationClassNameDoesNotImplementFormElementInterface()
    {
        $this->expectException(TypeDefinitionNotValidException::class);

        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $page->createElement('myElement', 'Neos.Form:MyElementTypeWhichDoesNotImplementFormElementInterface');
    }

    /**
     * @test
     */
    public function createElementThrowsExceptionIfUnknownPropertyFoundInTypeDefinition()
    {
        $this->expectException(TypeDefinitionNotValidException::class);

        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $page->createElement('myElement', 'Neos.Form:MyElementTypeWithUnknownProperties');
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

        Assert::assertSame([$element1, $element2], $page->getElements());
        $page->moveElementBefore($element2, $element1);
        Assert::assertSame([$element2, $element1], $page->getElements());
    }

    /**
     * @test
     */
    public function moveElementBeforeThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

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

        Assert::assertSame([$element1, $element2], $page->getElements());
        $page->moveElementAfter($element1, $element2);
        Assert::assertSame([$element2, $element1], $page->getElements());
    }

    /**
     * @test
     */
    public function moveElementAfterThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

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
        /** @var AbstractFormElement|MockObject $element1 */
        $element1 = $page1->createElement('myElement', 'Neos.Form:MyElementType');

        $page1->removeElement($element1);

        Assert::assertSame([], $page1->getElements());
        $this->assertNull($formDefinition->getElementByIdentifier('myElement'));

        $this->assertNull($element1->getParentRenderable());
    }

    /**
     * @test
     */
    public function removeElementThrowsExceptionIfElementIsNotOnCurrentPage()
    {
        $this->expectException(FormDefinitionConsistencyException::class);
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        /** @var AbstractFormElement|MockObject $element1 */
        $element1 = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();

        $page1->removeElement($element1);
    }

    /**
     * @test
     */
    public function validatorKeyCorrectlyAddsValidator()
    {
        $formDefinition = $this->getDummyFormDefinition();

        $mockProcessingRule = $this->getAccessibleMock(ProcessingRule::class, ['dummy']);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockProcessingRule->_set('validator', new ConjunctionValidator());
        $formDefinition->expects($this->any())->method('getProcessingRule')->with('asdf')->will($this->returnValue($mockProcessingRule));

        $page1 = $formDefinition->createPage('myPage1');
        /** @var AbstractFormElement|MockObject $element */
        $element = $page1->createElement('asdf', 'Neos.Form:MyElementWithValidator');
        $this->assertTrue($element->isRequired());
        $validators = $element->getValidators();
        $validators = iterator_to_array($validators);
        /** @var ValidatorInterface $firstValidator */
        $firstValidator = $validators[0];
        Assert::assertSame(2, count($validators));
        $this->assertInstanceOf(StringLengthValidator::class, $firstValidator);
        $validatorOptions = $firstValidator->getOptions();
        Assert::assertSame($validatorOptions['minimum'], 10);
        Assert::assertSame($validatorOptions['maximum'], PHP_INT_MAX);
    }

    /**
     * @test
     */
    public function validatorKeyThrowsExceptionIfValidatorPresetIsNotFound()
    {
        $this->expectException(ValidatorPresetNotFoundException::class);
        $formDefinition = $this->getDummyFormDefinition();

        $page1 = $formDefinition->createPage('myPage1');
        $page1->createElement('asdf', 'Neos.Form:MyElementWithBrokenValidator');
    }

    /**
     * @return FormDefinition|MockObject
     */
    protected function getDummyFormDefinition()
    {
        $formDefinitionConstructorArguments = ['myForm', [
            'validatorPresets' => [
                'MyValidatorIdentifier' => [
                    'implementationClassName' => StringLengthValidator::class
                ],
                'MyOtherValidatorIdentifier' => [
                    'implementationClassName' => NotEmptyValidator::class
                ],
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class
                ],
                'Neos.Form:MyElementType' => [
                    'implementationClassName' => GenericFormElement::class
                ],
                'Neos.Form:MyElementTypeWithAdditionalProperties' => [
                    'implementationClassName' => GenericFormElement::class,
                    'label' => 'my label',
                    'defaultValue' => 'This is the default value',
                    'properties' => [
                        'property1' => 'val1',
                        'property2' => 'val2'
                    ],
                    'renderingOptions' => [
                        'ro1' => 'rv1',
                        'ro2' => 'rv2'
                    ],
                    'rendererClassName' => 'MyRendererClassName'
                ],
                'Neos.Form:MyElementTypeWithoutImplementationClassName' => [],
                'Neos.Form:MyElementTypeWithUnknownProperties' => [
                    'implementationClassName' => GenericFormElement::class,
                    'unknownProperty' => 'foo'
                ],
                'Neos.Form:MyElementTypeWhichDoesNotImplementFormElementInterface' => [
                    'implementationClassName' => ArrayFormFactory::class,
                ],
                'Neos.Form:MyElementWithValidator' => [
                    'implementationClassName' => GenericFormElement::class,
                    'validators' => [
                        [
                            'identifier' => 'MyValidatorIdentifier',
                            'options' => ['minimum' => 10]
                        ],
                        [
                            'identifier' => 'MyOtherValidatorIdentifier'
                        ],
                    ]
                ],
                'Neos.Form:MyElementWithBrokenValidator' => [
                    'implementationClassName' => GenericFormElement::class,
                    'validators' => [
                        [
                            'identifier' => 'nonExisting',
                        ]
                    ]
                ]

            ]
        ]];

        $formDefinition = $this->getMockBuilder(FormDefinition::class)->setMethods(['getProcessingRule'])->setConstructorArgs($formDefinitionConstructorArguments)->getMock();
        return $formDefinition;
    }
}
