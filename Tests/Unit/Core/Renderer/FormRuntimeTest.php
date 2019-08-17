<?php
namespace Neos\Form\Tests\Unit\Core\Runtime;

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
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Form\Core\Runtime\FormState;
use Neos\Form\FormElements\GenericFormElement;
use PHPUnit\Framework\Assert;

require_once(__DIR__ . '/Fixture/DummyFinisher.php');

/**
 * Test for Form Runtime
 *
 * @covers \Neos\Form\Core\Runtime\FormRuntime<extended>
 */
class FormRuntimeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function valuesSetInConstructorCanBeReadAgain()
    {
        $formDefinition = new FormDefinition('foo');

        $mockActionRequest = $this->getMockBuilder(ActionRequest::class)->setMethods(['createSubRequest'])->disableOriginalConstructor()->getMock();

        $mockFormSubRequest = $this->getMockBuilder(ActionRequest::class)->setMethods(['getParentRequest'])->disableOriginalConstructor()->getMock();
        $mockFormSubRequest->expects(self::any())->method('getParentRequest')->willReturn($mockActionRequest);

        $mockActionRequest->expects(self::once())->method('createSubRequest')->willReturn($mockFormSubRequest);
        $actionResponse = new ActionResponse();

        $formRuntime = $this->getAccessibleMock(FormRuntime::class, ['dummy'], [$formDefinition, $mockActionRequest, $actionResponse]);

        Assert::assertSame($mockActionRequest, $formRuntime->getRequest()->getParentRequest());
        Assert::assertInstanceOf(ActionResponse::class, $formRuntime->getResponse());
        Assert::assertSame($formDefinition, $formRuntime->_get('formDefinition'));
    }

    /**
     * @test
     */
    public function getTypeReturnsTypeOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame('Neos.Form:Form', $formRuntime->getType());
    }

    /**
     * @test
     */
    public function getIdentifierReturnsIdentifierOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame('foo', $formRuntime->getIdentifier());
    }

    /**
     * @test
     */
    public function getRenderingOptionsReturnsRenderingOptionsOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setRenderingOption('asdf', 'test');
        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame(['asdf' => 'test'], $formRuntime->getRenderingOptions());
    }

    /**
     * @test
     */
    public function getRendererClassNameReturnsRendererClassNameOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setRendererClassName('MyRendererClassName');
        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame('MyRendererClassName', $formRuntime->getRendererClassName());
    }

    /**
     * @test
     */
    public function getLabelReturnsLabelOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setLabel('my cool label');
        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame('my cool label', $formRuntime->getLabel());
    }

    /**
     * @test
     */
    public function invokeFinishersInvokesFinishersInCorrectOrder()
    {
        $formDefinition = new FormDefinition('foo');

        $finisherCalls = [];

        $finisher1 = $this->getMockFinisher(function () use (&$finisherCalls) {
            $finisherCalls[] = func_get_args();
        });
        $finisher2 = $this->getMockFinisher(function ($finisherContext) use (&$finisherCalls) {
            $finisherCalls[] = func_get_args();
            $finisherContext->cancel();
        });
        $finisher3 = $this->getMockFinisher(function ($finisherContext) use (&$finisherCalls) {
            $finisherCalls[] = func_get_args();
        });
        $formDefinition->addFinisher($finisher1);
        $formDefinition->addFinisher($finisher2);
        $formDefinition->addFinisher($finisher3);

        $formRuntime = $this->createFormRuntime($formDefinition);
        $formRuntime->_call('invokeFinishers');

        Assert::assertSame(2, count($finisherCalls));
        Assert::assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
        Assert::assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
    }

    /**
     * @return \Neos\Form\Core\Model\FinisherInterface
     */
    protected function getMockFinisher(\Closure $closureToExecute)
    {
        $finisher = new Renderer\Fixture\DummyFinisher();
        $finisher->cb = $closureToExecute;

        return $finisher;
    }

    /**
     * @test
     */
    public function pageNavigationWorks()
    {
        $formDefinition = new FormDefinition('foo');
        $page1 = new Page('p1');
        $page2 = new Page('p2');
        $page3 = new Page('p3');
        $formDefinition->addPage($page1);
        $formDefinition->addPage($page2);
        $formDefinition->addPage($page3);

        $formRuntime = $this->createFormRuntime($formDefinition);
        Assert::assertSame([$page1, $page2, $page3], $formRuntime->getPages());

        $formRuntime->overrideCurrentPage(0);
        Assert::assertSame(null, $formRuntime->getPreviousPage());
        Assert::assertSame($page1, $formRuntime->getCurrentPage());
        Assert::assertSame($page2, $formRuntime->getNextPage());

        $formRuntime->overrideCurrentPage(1);
        Assert::assertSame($page1, $formRuntime->getPreviousPage());
        Assert::assertSame($page2, $formRuntime->getCurrentPage());
        Assert::assertSame($page3, $formRuntime->getNextPage());

        $formRuntime->overrideCurrentPage(2);
        Assert::assertSame($page2, $formRuntime->getPreviousPage());
        Assert::assertSame($page3, $formRuntime->getCurrentPage());
        Assert::assertSame(null, $formRuntime->getNextPage());
    }

    /**
     * @test
     */
    public function arrayAccessReturnsDefaultValuesIfSet()
    {
        $formDefinition = new FormDefinition('foo');
        $page1 = new Page('p1');
        $formDefinition->addPage($page1);
        $element1 = new GenericFormElement('foo', 'Bar');
        $page1->addElement($element1);

        $element1->setDefaultValue('My Default');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $formState = new FormState();
        $formRuntime->_set('formState', $formState);
        Assert::assertSame($formState, $formRuntime->getFormState());

        Assert::assertSame('My Default', $formRuntime['foo']);
        $formRuntime['foo'] = 'Overridden';
        Assert::assertSame('Overridden', $formRuntime['foo']);
        $formRuntime['foo'] = null;
        Assert::assertSame('My Default', $formRuntime['foo']);

        $formRuntime['foo'] = 'Overridden2';
        Assert::assertSame('Overridden2', $formRuntime['foo']);

        unset($formRuntime['foo']);
        Assert::assertSame('My Default', $formRuntime['foo']);

        Assert::assertSame(null, $formRuntime['nonExisting']);
    }

    /**
     * @param FormDefinition $formDefinition
     * @return FormRuntime|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createFormRuntime(FormDefinition $formDefinition)
    {
        $mockActionRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $actionResponse = new ActionResponse();

        return $this->getAccessibleMock(FormRuntime::class, ['dummy'], [$formDefinition, $mockActionRequest, $actionResponse]);
    }
}
