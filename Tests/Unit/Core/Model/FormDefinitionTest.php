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

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\Core\Model\AbstractFormElement;
use Neos\Form\Core\Model\FinisherInterface;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\FormElementInterface;
use Neos\Form\Core\Model\Page;
use Neos\Form\Core\Model\ProcessingRule;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Exception;
use Neos\Form\Exception\DuplicateFormElementException;
use Neos\Form\Exception\FinisherPresetNotFoundException;
use Neos\Form\Exception\FormDefinitionConsistencyException;
use Neos\Form\Exception\IdentifierNotValidException;
use Neos\Form\Exception\TypeDefinitionNotFoundException;
use Neos\Form\Exception\TypeDefinitionNotValidException;
use Neos\Form\Utility\SupertypeResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;

require_once(__DIR__ . '/Fixture/EmptyFinisher.php');

/**
 * Test for FormDefinition Domain Model
 * @covers \Neos\Form\Core\Model\FormDefinition<extended>
 * @covers \Neos\Form\Core\Model\Page<extended>
 */
class FormDefinitionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function identifierSetInConstructorCanBeReadAgain()
    {
        $formDefinition = new FormDefinition('foo');
        Assert::assertSame('foo', $formDefinition->getIdentifier());

        $formDefinition = new FormDefinition('bar');
        Assert::assertSame('bar', $formDefinition->getIdentifier());
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
     * @param $identifier
     * @throws IdentifierNotValidException
     */
    public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier)
    {
        $this->expectException(IdentifierNotValidException::class);
        new FormDefinition($identifier);
    }

    /**
     * @test
     * @throws IdentifierNotValidException
     */
    public function constructorSetsRendererClassName()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'rendererClassName' => 'FooRenderer'
                ]
            ]
        ]);
        Assert::assertSame('FooRenderer', $formDefinition->getRendererClassName());
    }

    /**
     * @test
     * @throws IdentifierNotValidException
     */
    public function constructorSetsFinishers()
    {
        $formDefinition = new FormDefinition('myForm', [
            'finisherPresets' => [
                'myFinisher' => [
                    'implementationClassName' => $this->buildAccessibleProxy(Fixture\EmptyFinisher::class),
                    'options' => [
                        'foo' => 'bar',
                        'test' => 'asdf'
                    ]
                ]
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'finishers' => [
                        [
                            'identifier' => 'myFinisher',
                            'options' => [
                                'foo' => 'baz'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $finishers = $formDefinition->getFinishers();
        Assert::assertSame(1, count($finishers));
        $finisher = $finishers[0];
        $this->assertInstanceOf(Fixture\EmptyFinisher::class, $finisher);
        /** @noinspection PhpUndefinedMethodInspection */
        Assert::assertSame(['foo' => 'baz', 'test' => 'asdf'], $finisher->_get('options'));
    }

    /**
     * @test
     * @throws IdentifierNotValidException
     */
    public function constructorSetsRenderingOptions()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'renderingOptions' => [
                        'foo' => 'bar',
                        'baz' => 'test'
                    ]
                ]
            ]
        ]);
        Assert::assertSame(['foo' => 'bar', 'baz' => 'test'], $formDefinition->getRenderingOptions());
    }

    /**
     * @test
     * @throws IdentifierNotValidException
     */
    public function constructorMakesValidatorPresetsAvailable()
    {
        $formDefinition = new FormDefinition('myForm', [
            'validatorPresets' => [
                'foo' => 'bar'
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => []
            ]
        ]);
        Assert::assertSame(['foo' => 'bar'], $formDefinition->getValidatorPresets());
    }

    /**
     * @test
     * @throws IdentifierNotValidException
     */
    public function constructorThrowsExceptionIfUnknownPropertySet()
    {
        $this->expectException(TypeDefinitionNotValidException::class);
        new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'unknownFormProperty' => 'val'
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function getPagesReturnsEmptyArrayByDefault()
    {
        $formDefinition = new FormDefinition('foo');
        Assert::assertSame([], $formDefinition->getPages());
    }

    /**
     * @test
     */
    public function getPageByIndexThrowsExceptionIfSpecifiedIndexDoesNotExist()
    {
        $this->expectException(Exception::class);
        $formDefinition = new FormDefinition('foo');
        $formDefinition->getPageByIndex(0);
    }

    /**
     * @test
     */
    public function hasPageWithIndexReturnsTrueIfTheSpecifiedIndexExists()
    {
        $formDefinition = new FormDefinition('foo');
        $page = new Page('bar');
        $formDefinition->addPage($page);
        Assert::assertTrue($formDefinition->hasPageWithIndex(0));
    }

    /**
     * @test
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function hasPageWithIndexReturnsFalseIfTheSpecifiedIndexDoesNotExist()
    {
        $formDefinition = new FormDefinition('foo');
        Assert::assertFalse($formDefinition->hasPageWithIndex(0));
        $page = new Page('bar');
        $formDefinition->addPage($page);
        Assert::assertFalse($formDefinition->hasPageWithIndex(1));
    }

    /**
     * @test
     */
    public function addPageAddsPageToPagesArrayAndSetsBackReferenceToForm()
    {
        $formDefinition = new FormDefinition('foo');
        $page = new Page('bar');
        $formDefinition->addPage($page);
        Assert::assertSame([$page], $formDefinition->getPages());
        Assert::assertSame($formDefinition, $page->getParentRenderable());

        Assert::assertSame($page, $formDefinition->getPageByIndex(0));
    }

    /**
     * @test
     */
    public function addPageAddsIndexToPage()
    {
        $formDefinition = new FormDefinition('foo');
        $page1 = new Page('bar1');
        $formDefinition->addPage($page1);

        $page2 = new Page('bar2');
        $formDefinition->addPage($page2);

        Assert::assertSame(0, $page1->getIndex());
        Assert::assertSame(1, $page2->getIndex());
    }

    /**
     * @test
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function getElementByIdentifierReturnsElementsWhichAreAlreadyAttachedToThePage()
    {
        $page = new Page('bar');
        $mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
        $page->addElement($mockFormElement);

        $formDefinition = new FormDefinition('foo');
        $formDefinition->addPage($page);

        Assert::assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
    }

    /**
     * @test
     */
    public function getElementByIdentifierReturnsElementsWhichAreLazilyAttachedToThePage()
    {
        $formDefinition = new FormDefinition('foo');

        $page = new Page('bar');
        $formDefinition->addPage($page);

        $mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
        $page->addElement($mockFormElement);
        Assert::assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
    }

    /**
     * @test
     */
    public function bindReturnsBoundFormRuntime()
    {
        $formDefinition = new FormDefinition('foo');

        /** @var ActionRequest|MockObject $mockRequest */
        $mockRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $mockResponse = new ActionResponse();

        $form = $formDefinition->bind($mockRequest, $mockResponse);
        Assert::assertInstanceOf(FormRuntime::class, $form);
    }

    /**
     * @test
     */
    public function attachingTwoElementsWithSameIdentifierToFormThrowsException1()
    {
        $this->expectException(DuplicateFormElementException::class);

        $mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
        $mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

        $page = new Page('bar');
        $page->addElement($mockFormElement1);
        $page->addElement($mockFormElement2);

        $formDefinition = new FormDefinition('foo');
        $formDefinition->addPage($page);
    }

    /**
     * @test
     */
    public function attachingTwoElementsWithSameIdentifierToFormThrowsException2()
    {
        $this->expectException(DuplicateFormElementException::class);

        $mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
        $mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

        $page = new Page('bar');
        $page->addElement($mockFormElement1);

        $formDefinition = new FormDefinition('foo');
        $formDefinition->addPage($page);

        $page->addElement($mockFormElement2);
    }

    /**
     * @test
     */
    public function aPageCanOnlyBeAttachedToASingleFormDefinition()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

        $page = new Page('bar');

        $formDefinition1 = new FormDefinition('foo1');
        $formDefinition2 = new FormDefinition('foo2');

        $formDefinition1->addPage($page);
        $formDefinition2->addPage($page);
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageCreatesPageAndAddsItToForm()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class
                ]
            ]
        ]);
        $page = $formDefinition->createPage('myPage');
        Assert::assertSame('myPage', $page->getIdentifier());
        Assert::assertSame($page, $formDefinition->getPageByIndex(0));
        Assert::assertSame(0, $page->getIndex());
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageSetsLabelFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class,
                    'label' => 'My Label'
                ]
            ]
        ]);
        $page = $formDefinition->createPage('myPage');
        Assert::assertSame('My Label', $page->getLabel());
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageSetsRendererClassNameFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class,
                    'rendererClassName' => 'MyRenderer'
                ]
            ]
        ]);
        $page = $formDefinition->createPage('myPage');
        Assert::assertSame('MyRenderer', $page->getRendererClassName());
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageSetsRenderingOptionsFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class,
                    'renderingOptions' => ['foo' => 'bar', 'baz' => 'asdf']
                ]
            ]
        ]);
        $page = $formDefinition->createPage('myPage');
        Assert::assertSame(['foo' => 'bar', 'baz' => 'asdf'], $page->getRenderingOptions());
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageThrowsExceptionIfUnknownPropertyFoundInTypeDefinition()
    {
        $this->expectException(TypeDefinitionNotValidException::class);

        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => Page::class,
                    'label' => 'My Label',
                    'unknownProperty' => 'this is an unknown property'
                ]
            ]
        ]);
        $formDefinition->createPage('myPage');
    }

    /**
     * @test
     * @throws Exception
     * @throws IdentifierNotValidException
     * @throws TypeDefinitionNotFoundException
     */
    public function createPageThrowsExceptionIfImplementationClassNameNotFound()
    {
        $this->expectException(TypeDefinitionNotFoundException::class);

        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [

                ],
                'Neos.Form:Page2' => []
            ]
        ]);
        $formDefinition->createPage('myPage', 'Neos.Form:Page2');
    }

    /**
     * @test
     */
    public function formFieldTypeManagerIsReturned()
    {
        $formDefinition = new FormDefinition('myForm');
        Assert::assertInstanceOf(SupertypeResolver::class, $formDefinition->getFormFieldTypeManager());
    }

    /**
     * @test
     */
    public function movePageBeforeMovesPageBeforeReferenceElement()
    {
        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $page3 = new Page('bar3');
        $formDefinition->addPage($page1);
        $formDefinition->addPage($page2);
        $formDefinition->addPage($page3);

        Assert::assertSame(0, $page1->getIndex());
        Assert::assertSame(1, $page2->getIndex());
        Assert::assertSame(2, $page3->getIndex());
        Assert::assertSame([$page1, $page2, $page3], $formDefinition->getPages());

        $formDefinition->movePageBefore($page2, $page1);

        Assert::assertSame(1, $page1->getIndex());
        Assert::assertSame(0, $page2->getIndex());
        Assert::assertSame(2, $page3->getIndex());
        Assert::assertSame([$page2, $page1, $page3], $formDefinition->getPages());
    }

    /**
     * @test
     */
    public function movePageBeforeThrowsExceptionIfPagesDoNotBelongToSameForm()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $formDefinition->addPage($page1);

        $formDefinition->movePageBefore($page2, $page1);
    }

    /**
     * @test
     */
    public function movePageAfterMovesPageAfterReferenceElement()
    {
        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $page3 = new Page('bar3');
        $formDefinition->addPage($page1);
        $formDefinition->addPage($page2);
        $formDefinition->addPage($page3);

        Assert::assertSame(0, $page1->getIndex());
        Assert::assertSame(1, $page2->getIndex());
        Assert::assertSame(2, $page3->getIndex());
        Assert::assertSame([$page1, $page2, $page3], $formDefinition->getPages());

        $formDefinition->movePageAfter($page1, $page2);

        Assert::assertSame(1, $page1->getIndex());
        Assert::assertSame(0, $page2->getIndex());
        Assert::assertSame(2, $page3->getIndex());
        Assert::assertSame([$page2, $page1, $page3], $formDefinition->getPages());
    }

    /**
     * @test
     */
    public function movePageAfterThrowsExceptionIfPagesDoNotBelongToSameForm()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $formDefinition->addPage($page1);

        $formDefinition->movePageAfter($page2, $page1);
    }

    /**
     * @test
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function removePageRemovesPageFromForm()
    {
        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $formDefinition->addPage($page1);
        $formDefinition->addPage($page2);

        $formDefinition->removePage($page1);
        $this->assertNull($page1->getParentRenderable());
        Assert::assertSame([$page2], $formDefinition->getPages());
    }

    /**
     * @test
     * @throws FormDefinitionConsistencyException
     * @throws IdentifierNotValidException
     */
    public function removePageRemovesFormElementsOnPageFromForm()
    {
        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $element1 = $this->getMockFormElement('el1');
        $page1->addElement($element1);
        $formDefinition->addPage($page1);
        $element2 = $this->getMockFormElement('el2');
        $page1->addElement($element2);

        Assert::assertSame($element1, $formDefinition->getElementByIdentifier('el1'));
        Assert::assertSame($element2, $formDefinition->getElementByIdentifier('el2'));

        $formDefinition->removePage($page1);

        $this->assertNull($formDefinition->getElementByIdentifier('el1'));
        $this->assertNull($formDefinition->getElementByIdentifier('el2'));
    }

    /**
     * @test
     */
    public function removePageThrowsExceptionIfPageIsNotOnForm()
    {
        $this->expectException(FormDefinitionConsistencyException::class);

        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $formDefinition->removePage($page1);
    }

    /**
     * @test
     */
    public function getProcessingRuleCreatesProcessingRuleIfItDoesNotExistYet()
    {
        $formDefinition = new FormDefinition('foo1');
        $processingRule1 = $formDefinition->getProcessingRule('foo');
        $processingRule2 = $formDefinition->getProcessingRule('foo');

        $this->assertInstanceOf(ProcessingRule::class, $processingRule1);
        Assert::assertSame($processingRule1, $processingRule2);

        Assert::assertSame(['foo' => $processingRule1], $formDefinition->getProcessingRules());
    }

    /**
     * @test
     */
    public function addFinisherAddsFinishersToList()
    {
        $formDefinition = new FormDefinition('foo1');
        Assert::assertSame([], $formDefinition->getFinishers());
        $finisher = $this->getMockFinisher();
        $formDefinition->addFinisher($finisher);
        Assert::assertSame([$finisher], $formDefinition->getFinishers());
    }

    /**
     * @test
     */
    public function createFinisherThrowsExceptionIfFinisherPresetNotFound()
    {
        $this->expectException(FinisherPresetNotFoundException::class);

        $formDefinition = new FormDefinition('foo1');
        $formDefinition->createFinisher('asdf');
    }

    /**
     * @test
     */
    public function createFinisherThrowsExceptionIfImplementationClassNameIsEmpty()
    {
        $this->expectException(FinisherPresetNotFoundException::class);

        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $formDefinition->createFinisher('asdf');
    }

    /**
     * @test
     */
    public function createFinisherCreatesFinisherCorrectly()
    {
        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $finisher = $formDefinition->createFinisher('email');
        $this->assertInstanceOf(Fixture\EmptyFinisher::class, $finisher);
        Assert::assertSame([$finisher], $formDefinition->getFinishers());
    }

    /**
     * @test
     */
    public function createFinisherSetsOptionsCorrectly()
    {
        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $finisher = $formDefinition->createFinisher('emailWithOptions');
        /** @noinspection PhpUndefinedMethodInspection */
        Assert::assertSame(['foo' => 'bar', 'name' => 'asdf'], $finisher->_get('options'));
    }

    /**
     * @test
     */
    public function createFinisherSetsOptionsCorrectlyAndMergesThemWithPassedOptions()
    {
        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $finisher = $formDefinition->createFinisher('emailWithOptions', ['foo' => 'baz']);
        Assert::assertSame(['foo' => 'baz', 'name' => 'asdf'], $finisher->_get('options'));
    }


    /**
     * @return FormDefinition
     */
    protected function getFormDefinitionWithFinisherConfiguration()
    {
        $formDefinition = new FormDefinition('foo1', [
            'finisherPresets' => [
                'asdf' => [
                    'assd' => 'as'
                ],
                'email' => [
                    'implementationClassName' => $this->buildAccessibleProxy(Fixture\EmptyFinisher::class)
                ],
                'emailWithOptions' => [
                    'implementationClassName' => $this->buildAccessibleProxy(Fixture\EmptyFinisher::class),
                    'options' => [
                        'foo' => 'bar',
                        'name' => 'asdf'
                    ]
                ]
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => []
            ]
        ]);
        return $formDefinition;
    }

    /**
     * @return FinisherInterface|MockObject
     */
    protected function getMockFinisher()
    {
        return $this->createMock(FinisherInterface::class);
    }

    /**
     * @param string $identifier
     * @return FormElementInterface|MockObject
     */
    protected function getMockFormElement($identifier)
    {
        $mockFormElement = $this->getMockBuilder(AbstractFormElement::class)->setMethods(['getIdentifier'])->disableOriginalConstructor()->getMock();
        $mockFormElement->expects($this->any())->method('getIdentifier')->will($this->returnValue($identifier));

        return $mockFormElement;
    }
}
