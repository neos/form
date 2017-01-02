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

require_once __DIR__.'/Fixture/EmptyFinisher.php';

/**
 * Test for FormDefinition Domain Model.
 *
 * @covers \Neos\Form\Core\Model\FormDefinition<extended>
 * @covers \Neos\Form\Core\Model\Page<extended>
 */
class FormDefinitionTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function identifierSetInConstructorCanBeReadAgain()
    {
        $formDefinition = new FormDefinition('foo');
        $this->assertSame('foo', $formDefinition->getIdentifier());

        $formDefinition = new FormDefinition('bar');
        $this->assertSame('bar', $formDefinition->getIdentifier());
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
        new FormDefinition($identifier);
    }

    /**
     * @test
     */
    public function constructorSetsRendererClassName()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'rendererClassName' => 'FooRenderer',
                ],
            ],
        ]);
        $this->assertSame('FooRenderer', $formDefinition->getRendererClassName());
    }

    /**
     * @test
     */
    public function constructorSetsFinishers()
    {
        $formDefinition = new FormDefinition('myForm', [
            'finisherPresets' => [
                'myFinisher' => [
                    'implementationClassName' => $this->buildAccessibleProxy(\Neos\Form\Tests\Unit\Core\Model\Fixture\EmptyFinisher::class),
                    'options'                 => [
                        'foo'  => 'bar',
                        'test' => 'asdf',
                    ],
                ],
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'finishers' => [
                        [
                            'identifier' => 'myFinisher',
                            'options'    => [
                                'foo' => 'baz',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $finishers = $formDefinition->getFinishers();
        $this->assertSame(1, count($finishers));
        $finisher = $finishers[0];
        $this->assertInstanceOf(\Neos\Form\Tests\Unit\Core\Model\Fixture\EmptyFinisher::class, $finisher);
        $this->assertSame(['foo' => 'baz', 'test' => 'asdf'], $finisher->_get('options'));
    }

    /**
     * @test
     */
    public function constructorSetsRenderingOptions()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'renderingOptions' => [
                        'foo' => 'bar',
                        'baz' => 'test',
                    ],
                ],
            ],
        ]);
        $this->assertSame(['foo' => 'bar', 'baz' => 'test'], $formDefinition->getRenderingOptions());
    }

    /**
     * @test
     */
    public function constructorMakesValidatorPresetsAvailable()
    {
        $formDefinition = new FormDefinition('myForm', [
            'validatorPresets' => [
                'foo' => 'bar',
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [],
            ],
        ]);
        $this->assertSame(['foo' => 'bar'], $formDefinition->getValidatorPresets());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotValidException
     */
    public function constructorThrowsExceptionIfUnknownPropertySet()
    {
        new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [
                    'unknownFormProperty' => 'val',
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function getPagesReturnsEmptyArrayByDefault()
    {
        $formDefinition = new FormDefinition('foo');
        $this->assertSame([], $formDefinition->getPages());
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception
     */
    public function getPageByIndexThrowsExceptionIfSpecifiedIndexDoesNotExist()
    {
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
        $this->assertTrue($formDefinition->hasPageWithIndex(0));
    }

    /**
     * @test
     */
    public function hasPageWithIndexReturnsFalseIfTheSpecifiedIndexDoesNotExist()
    {
        $formDefinition = new FormDefinition('foo');
        $this->assertFalse($formDefinition->hasPageWithIndex(0));
        $page = new Page('bar');
        $formDefinition->addPage($page);
        $this->assertFalse($formDefinition->hasPageWithIndex(1));
    }

    /**
     * @test
     */
    public function addPageAddsPageToPagesArrayAndSetsBackReferenceToForm()
    {
        $formDefinition = new FormDefinition('foo');
        $page = new Page('bar');
        $formDefinition->addPage($page);
        $this->assertSame([$page], $formDefinition->getPages());
        $this->assertSame($formDefinition, $page->getParentRenderable());

        $this->assertSame($page, $formDefinition->getPageByIndex(0));
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

        $this->assertSame(0, $page1->getIndex());
        $this->assertSame(1, $page2->getIndex());
    }

    /**
     * @test
     */
    public function getElementByIdentifierReturnsElementsWhichAreAlreadyAttachedToThePage()
    {
        $page = new Page('bar');
        $mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
        $page->addElement($mockFormElement);

        $formDefinition = new FormDefinition('foo');
        $formDefinition->addPage($page);

        $this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
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
        $this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
    }

    /**
     * @test
     */
    public function bindReturnsBoundFormRuntime()
    {
        $formDefinition = new FormDefinition('foo');

        $mockRequest = $this->getMockBuilder(\Neos\Flow\Mvc\ActionRequest::class)->disableOriginalConstructor()->getMock();
        $mockResponse = $this->getMockBuilder(\Neos\Flow\Http\Response::class)->getMock();

        $form = $formDefinition->bind($mockRequest, $mockResponse);
        $this->assertInstanceOf(\Neos\Form\Core\Runtime\FormRuntime::class, $form);
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\DuplicateFormElementException
     */
    public function attachingTwoElementsWithSameIdentifierToFormThrowsException1()
    {
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
     * @expectedException Neos\Form\Exception\DuplicateFormElementException
     */
    public function attachingTwoElementsWithSameIdentifierToFormThrowsException2()
    {
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
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function aPageCanOnlyBeAttachedToASingleFormDefinition()
    {
        $page = new Page('bar');

        $formDefinition1 = new FormDefinition('foo1');
        $formDefinition2 = new FormDefinition('foo2');

        $formDefinition1->addPage($page);
        $formDefinition2->addPage($page);
    }

    /**
     * @test
     */
    public function createPageCreatesPageAndAddsItToForm()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                ],
            ],
        ]);
        $page = $formDefinition->createPage('myPage');
        $this->assertSame('myPage', $page->getIdentifier());
        $this->assertSame($page, $formDefinition->getPageByIndex(0));
        $this->assertSame(0, $page->getIndex());
    }

    /**
     * @test
     */
    public function createPageSetsLabelFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                    'label'                   => 'My Label',
                ],
            ],
        ]);
        $page = $formDefinition->createPage('myPage');
        $this->assertSame('My Label', $page->getLabel());
    }

    /**
     * @test
     */
    public function createPageSetsRendererClassNameFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                    'rendererClassName'       => 'MyRenderer',
                ],
            ],
        ]);
        $page = $formDefinition->createPage('myPage');
        $this->assertSame('MyRenderer', $page->getRendererClassName());
    }

    /**
     * @test
     */
    public function createPageSetsRenderingOptionsFromTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                    'renderingOptions'        => ['foo' => 'bar', 'baz' => 'asdf'],
                ],
            ],
        ]);
        $page = $formDefinition->createPage('myPage');
        $this->assertSame(['foo' => 'bar', 'baz' => 'asdf'], $page->getRenderingOptions());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotValidException
     */
    public function createPageThrowsExceptionIfUnknownPropertyFoundInTypeDefinition()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [],
                'Neos.Form:Page' => [
                    'implementationClassName' => \Neos\Form\Core\Model\Page::class,
                    'label'                   => 'My Label',
                    'unknownProperty'         => 'this is an unknown property',
                ],
            ],
        ]);
        $page = $formDefinition->createPage('myPage');
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\TypeDefinitionNotFoundException
     */
    public function createPageThrowsExceptionIfImplementationClassNameNotFound()
    {
        $formDefinition = new FormDefinition('myForm', [
            'formElementTypes' => [
                'Neos.Form:Form' => [

                ],
                'Neos.Form:Page2' => [],
            ],
        ]);
        $page = $formDefinition->createPage('myPage', 'Neos.Form:Page2');
    }

    /**
     * @test
     */
    public function formFieldTypeManagerIsReturned()
    {
        $formDefinition = new FormDefinition('myForm');
        $this->assertInstanceOf(\Neos\Form\Utility\SupertypeResolver::class, $formDefinition->getFormFieldTypeManager());
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

        $this->assertSame(0, $page1->getIndex());
        $this->assertSame(1, $page2->getIndex());
        $this->assertSame(2, $page3->getIndex());
        $this->assertSame([$page1, $page2, $page3], $formDefinition->getPages());

        $formDefinition->movePageBefore($page2, $page1);

        $this->assertSame(1, $page1->getIndex());
        $this->assertSame(0, $page2->getIndex());
        $this->assertSame(2, $page3->getIndex());
        $this->assertSame([$page2, $page1, $page3], $formDefinition->getPages());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function movePageBeforeThrowsExceptionIfPagesDoNotBelongToSameForm()
    {
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

        $this->assertSame(0, $page1->getIndex());
        $this->assertSame(1, $page2->getIndex());
        $this->assertSame(2, $page3->getIndex());
        $this->assertSame([$page1, $page2, $page3], $formDefinition->getPages());

        $formDefinition->movePageAfter($page1, $page2);

        $this->assertSame(1, $page1->getIndex());
        $this->assertSame(0, $page2->getIndex());
        $this->assertSame(2, $page3->getIndex());
        $this->assertSame([$page2, $page1, $page3], $formDefinition->getPages());
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function movePageAfterThrowsExceptionIfPagesDoNotBelongToSameForm()
    {
        $formDefinition = new FormDefinition('foo1');
        $page1 = new Page('bar1');
        $page2 = new Page('bar2');
        $formDefinition->addPage($page1);

        $formDefinition->movePageAfter($page2, $page1);
    }

    /**
     * @test
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
        $this->assertSame([$page2], $formDefinition->getPages());
    }

    /**
     * @test
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

        $this->assertSame($element1, $formDefinition->getElementByIdentifier('el1'));
        $this->assertSame($element2, $formDefinition->getElementByIdentifier('el2'));

        $formDefinition->removePage($page1);

        $this->assertNull($formDefinition->getElementByIdentifier('el1'));
        $this->assertNull($formDefinition->getElementByIdentifier('el2'));
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\FormDefinitionConsistencyException
     */
    public function removePageThrowsExceptionIfPageIsNotOnForm()
    {
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

        $this->assertInstanceOf(\Neos\Form\Core\Model\ProcessingRule::class, $processingRule1);
        $this->assertSame($processingRule1, $processingRule2);

        $this->assertSame(['foo' => $processingRule1], $formDefinition->getProcessingRules());
    }

    /**
     * @test
     */
    public function addFinisherAddsFinishersToList()
    {
        $formDefinition = new FormDefinition('foo1');
        $this->assertSame([], $formDefinition->getFinishers());
        $finisher = $this->getMockFinisher();
        $formDefinition->addFinisher($finisher);
        $this->assertSame([$finisher], $formDefinition->getFinishers());
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\FinisherPresetNotFoundException
     */
    public function createFinisherThrowsExceptionIfFinisherPresetNotFound()
    {
        $formDefinition = new FormDefinition('foo1');
        $formDefinition->createFinisher('asdf');
    }

    /**
     * @test
     * @expectedException \Neos\Form\Exception\FinisherPresetNotFoundException
     */
    public function createFinisherThrowsExceptionIfImplementationClassNameIsEmpty()
    {
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
        $this->assertInstanceOf(\Neos\Form\Tests\Unit\Core\Model\Fixture\EmptyFinisher::class, $finisher);
        $this->assertSame([$finisher], $formDefinition->getFinishers());
    }

    /**
     * @test
     */
    public function createFinisherSetsOptionsCorrectly()
    {
        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $finisher = $formDefinition->createFinisher('emailWithOptions');
        $this->assertSame(['foo' => 'bar', 'name' => 'asdf'], $finisher->_get('options'));
    }

    /**
     * @test
     */
    public function createFinisherSetsOptionsCorrectlyAndMergesThemWithPassedOptions()
    {
        $formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
        $finisher = $formDefinition->createFinisher('emailWithOptions', ['foo' => 'baz']);
        $this->assertSame(['foo' => 'baz', 'name' => 'asdf'], $finisher->_get('options'));
    }

    /**
     * @return \Neos\Form\Core\Model\FormDefinition
     */
    protected function getFormDefinitionWithFinisherConfiguration()
    {
        $formDefinition = new FormDefinition('foo1', [
            'finisherPresets' => [
                'asdf' => [
                    'assd' => 'as',
                ],
                'email' => [
                    'implementationClassName' => $this->buildAccessibleProxy(\Neos\Form\Tests\Unit\Core\Model\Fixture\EmptyFinisher::class),
                ],
                'emailWithOptions' => [
                    'implementationClassName' => $this->buildAccessibleProxy(\Neos\Form\Tests\Unit\Core\Model\Fixture\EmptyFinisher::class),
                    'options'                 => [
                        'foo'  => 'bar',
                        'name' => 'asdf',
                    ],
                ],
            ],
            'formElementTypes' => [
                'Neos.Form:Form' => [],
            ],
        ]);

        return $formDefinition;
    }

    /**
     * @return \Neos\Form\Core\Model\FinisherInterface
     */
    protected function getMockFinisher()
    {
        return $this->createMock(\Neos\Form\Core\Model\FinisherInterface::class);
    }

    /**
     * @param string $identifier
     *
     * @return \Neos\Form\Core\Model\FormElementInterface
     */
    protected function getMockFormElement($identifier)
    {
        $mockFormElement = $this->getMockBuilder(\Neos\Form\Core\Model\AbstractFormElement::class)->setMethods(['getIdentifier'])->disableOriginalConstructor()->getMock();
        $mockFormElement->expects($this->any())->method('getIdentifier')->will($this->returnValue($identifier));

        return $mockFormElement;
    }
}
