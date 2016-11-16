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
 * Test for Page Domain Model
 * @covers \TYPO3\Form\Core\Model\Page<extended>
 * @covers \TYPO3\Form\Core\Model\AbstractFormElement<extended>
 */
class PageTest extends \TYPO3\Flow\Tests\UnitTestCase
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
        $this->assertSame('TYPO3.Form:Page', $page->getType());
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
        return array(
            'Null Identifier' => array(null),
            'Integer Identifier' => array(42),
            'Empty String Identifier' => array('')
        );
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\IdentifierNotValidException
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
        $this->assertSame(array(), $page->getElements());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsEmptyArrayByDefault()
    {
        $page = new Page('foo');
        $this->assertSame(array(), $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsFirstLevelFormElements()
    {
        $page = new Page('foo');
        $element1 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $element2 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $page->addElement($element1);
        $page->addElement($element2);
        $this->assertSame(array($element1, $element2), $page->getElementsRecursively());
    }

    /**
     * @test
     */
    public function getElementsRecursivelyReturnsRecursiveFormElementsInCorrectOrder()
    {
        $page = new Page('foo');
        $element1 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $element2 = $this->getMockBuilder(\TYPO3\Form\FormElements\Section::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $element21 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $element22 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $element2->addElement($element21);
        $element2->addElement($element22);
        $element3 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

        $page->addElement($element1);
        $page->addElement($element2);
        $page->addElement($element3);
        $this->assertSame(array($element1, $element2, $element21, $element22, $element3), $page->getElementsRecursively());
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
     */
    public function aFormElementCanOnlyBeAttachedToASinglePage()
    {
        $element = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

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
        $element = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $page->addElement($element);
        $this->assertSame(array($element), $page->getElements());
        $this->assertSame($page, $element->getParentRenderable());
    }

    /**
     * @test
     */
    public function createElementCreatesElementAndAddsItToForm()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'TYPO3.Form:MyElementType');

        $this->assertSame('myElement', $element->getIdentifier());
        $this->assertInstanceOf(\TYPO3\Form\FormElements\GenericFormElement::class, $element);
        $this->assertSame('TYPO3.Form:MyElementType', $element->getType());
        $this->assertSame(array($element), $page->getElements());
    }

    /**
     * @test
     */
    public function createElementSetsAdditionalPropertiesInElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithAdditionalProperties');

        $this->assertSame('my label', $element->getLabel());
        $this->assertSame('This is the default value', $element->getDefaultValue());
        $this->assertSame(array('property1' => 'val1', 'property2' => 'val2'), $element->getProperties());
        $this->assertSame(array('ro1' => 'rv1', 'ro2' => 'rv2'), $element->getRenderingOptions());
        $this->assertSame('MyRendererClassName', $element->getRendererClassName());
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
     */
    public function createElementThrowsExceptionIfPageIsNotAttachedToParentForm()
    {
        $page = new Page('id');
        $page->createElement('myElement', 'TYPO3.Form:MyElementType');
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\TypeDefinitionNotFoundException
     */
    public function createElementThrowsExceptionIfImplementationClassNameNotFound()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithoutImplementationClassName');
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\TypeDefinitionNotValidException
     */
    public function createElementThrowsExceptionIfImplementationClassNameDoesNotImplementFormElementInterface()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWhichDoesNotImplementFormElementInterface');
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\TypeDefinitionNotValidException
     */
    public function createElementThrowsExceptionIfUnknownPropertyFoundInTypeDefinition()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element = $page->createElement('myElement', 'TYPO3.Form:MyElementTypeWithUnknownProperties');
    }

    /**
     * @test
     */
    public function moveElementBeforeMovesElementBeforeReferenceElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element1 = $page->createElement('myElement', 'TYPO3.Form:MyElementType');
        $element2 = $page->createElement('myElement2', 'TYPO3.Form:MyElementType');

        $this->assertSame(array($element1, $element2), $page->getElements());
        $page->moveElementBefore($element2, $element1);
        $this->assertSame(array($element2, $element1), $page->getElements());
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
     */
    public function moveElementBeforeThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $page2 = $formDefinition->createPage('myPage2');

        $element1 = $page1->createElement('myElement', 'TYPO3.Form:MyElementType');
        $element2 = $page2->createElement('myElement2', 'TYPO3.Form:MyElementType');

        $page1->moveElementBefore($element1, $element2);
    }

    /**
     * @test
     */
    public function moveElementAfterMovesElementAfterReferenceElement()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page = $formDefinition->createPage('myPage');
        $element1 = $page->createElement('myElement', 'TYPO3.Form:MyElementType');
        $element2 = $page->createElement('myElement2', 'TYPO3.Form:MyElementType');

        $this->assertSame(array($element1, $element2), $page->getElements());
        $page->moveElementAfter($element1, $element2);
        $this->assertSame(array($element2, $element1), $page->getElements());
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
     */
    public function moveElementAfterThrowsExceptionIfElementsAreNotOnSamePage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $page2 = $formDefinition->createPage('myPage2');

        $element1 = $page1->createElement('myElement', 'TYPO3.Form:MyElementType');
        $element2 = $page2->createElement('myElement2', 'TYPO3.Form:MyElementType');

        $page1->moveElementAfter($element1, $element2);
    }

    /**
     * @test
     */
    public function removeElementRemovesElementFromCurrentPageAndUnregistersItFromForm()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $element1 = $page1->createElement('myElement', 'TYPO3.Form:MyElementType');

        $page1->removeElement($element1);

        $this->assertSame(array(), $page1->getElements());
        $this->assertNull($formDefinition->getElementByIdentifier('myElement'));

        $this->assertNull($element1->getParentRenderable());
    }

    /**
     * @test
     * @expectedException TYPO3\Form\Exception\FormDefinitionConsistencyException
     */
    public function removeElementThrowsExceptionIfElementIsNotOnCurrentPage()
    {
        $formDefinition = $this->getDummyFormDefinition();
        $page1 = $formDefinition->createPage('myPage1');
        $element1 = $this->getMockBuilder(\TYPO3\Form\Core\Model\AbstractFormElement::class)->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

        $page1->removeElement($element1);
    }

    /**
     * @test
     */
    public function validatorKeyCorrectlyAddsValidator()
    {
        $formDefinition = $this->getDummyFormDefinition();

        $mockProcessingRule = $this->getAccessibleMock(\TYPO3\Form\Core\Model\ProcessingRule::class, array('dummy'));
        $mockProcessingRule->_set('validator', new \TYPO3\Flow\Validation\Validator\ConjunctionValidator());
        $formDefinition->expects($this->any())->method('getProcessingRule')->with('asdf')->will($this->returnValue($mockProcessingRule));

        $page1 = $formDefinition->createPage('myPage1');
        $el = $page1->createElement('asdf', 'TYPO3.Form:MyElementWithValidator');
        $this->assertTrue($el->isRequired());
        $validators = $el->getValidators();
        $validators = iterator_to_array($validators);
        $this->assertSame(2, count($validators));
        $this->assertInstanceOf(\TYPO3\Flow\Validation\Validator\StringLengthValidator::class, $validators[0]);
        $this->assertSame(array('minimum' => 10, 'maximum' => PHP_INT_MAX), $validators[0]->getOptions());
    }

    /**
     * @test
     * @expectedException \TYPO3\Form\Exception\ValidatorPresetNotFoundException
     */
    public function validatorKeyThrowsExceptionIfValidatorPresetIsNotFound()
    {
        $formDefinition = $this->getDummyFormDefinition();

        $page1 = $formDefinition->createPage('myPage1');
        $el = $page1->createElement('asdf', 'TYPO3.Form:MyElementWithBrokenValidator');
    }

    protected function getDummyFormDefinition()
    {
        $formDefinitionConstructorArguments = array('myForm', array(
            'validatorPresets' => array(
                'MyValidatorIdentifier' => array(
                    'implementationClassName' => \TYPO3\Flow\Validation\Validator\StringLengthValidator::class
                ),
                'MyOtherValidatorIdentifier' => array(
                    'implementationClassName' => \TYPO3\Flow\Validation\Validator\NotEmptyValidator::class
                ),
            ),
            'formElementTypes' => array(
                'TYPO3.Form:Form' => array(),
                'TYPO3.Form:Page' => array(
                    'implementationClassName' => \TYPO3\Form\Core\Model\Page::class
                ),
                'TYPO3.Form:MyElementType' => array(
                    'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class
                ),
                'TYPO3.Form:MyElementTypeWithAdditionalProperties' => array(
                    'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class,
                    'label' => 'my label',
                    'defaultValue' => 'This is the default value',
                    'properties' => array(
                        'property1' => 'val1',
                        'property2' => 'val2'
                    ),
                    'renderingOptions' => array(
                        'ro1' => 'rv1',
                        'ro2' => 'rv2'
                    ),
                    'rendererClassName' => 'MyRendererClassName'
                ),
                'TYPO3.Form:MyElementTypeWithoutImplementationClassName' => array(),
                'TYPO3.Form:MyElementTypeWithUnknownProperties' => array(
                    'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class,
                    'unknownProperty' => 'foo'
                ),
                'TYPO3.Form:MyElementTypeWhichDoesNotImplementFormElementInterface' => array(
                    'implementationClassName' => \TYPO3\Form\Factory\ArrayFormFactory::class,
                ),
                'TYPO3.Form:MyElementWithValidator' => array(
                    'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class,
                    'validators' => array(
                        array(
                            'identifier' => 'MyValidatorIdentifier',
                            'options' => array('minimum' => 10)
                        ),
                        array(
                            'identifier' => 'MyOtherValidatorIdentifier'
                        ),
                    )
                ),
                'TYPO3.Form:MyElementWithBrokenValidator' => array(
                    'implementationClassName' => \TYPO3\Form\FormElements\GenericFormElement::class,
                    'validators' => array(
                        array(
                            'identifier' => 'nonExisting',
                        )
                    )
                )

            )
        ));

        $formDefinition = $this->getMockBuilder(\TYPO3\Form\Core\Model\FormDefinition::class)->setMethods(array('getProcessingRule'))->setConstructorArgs($formDefinitionConstructorArguments)->getMock();
        return $formDefinition;
    }
}
