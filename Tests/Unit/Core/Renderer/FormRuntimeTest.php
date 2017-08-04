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

use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Page;

require_once(__DIR__ . '/Fixture/DummyFinisher.php');

/**
 * Test for Form Runtime
 *
 * @covers \Neos\Form\Core\Runtime\FormRuntime<extended>
 */
class FormRuntimeTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function valuesSetInConstructorCanBeReadAgain()
    {
        $formDefinition = new FormDefinition('foo');
        $mockActionRequest = $this->getMockBuilder(\Neos\Flow\Mvc\ActionRequest::class)->disableOriginalConstructor()->getMock();
        $mockHttpResponse = $this->getMockBuilder(\Neos\Flow\Http\Response::class)->disableOriginalConstructor()->getMock();

        $formRuntime = $this->getAccessibleMock(\Neos\Form\Core\Runtime\FormRuntime::class, ['dummy'], [$formDefinition, $mockActionRequest, $mockHttpResponse]);

        $this->assertSame($mockActionRequest, $formRuntime->getRequest()->getParentRequest());
        $this->assertSame($mockHttpResponse, $formRuntime->getResponse());
        $this->assertSame($formDefinition, $formRuntime->_get('formDefinition'));
    }

    /**
     * @test
     */
    public function getTypeReturnsTypeOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $this->assertSame('Neos.Form:Form', $formRuntime->getType());
    }

    /**
     * @test
     */
    public function getIdentifierReturnsIdentifierOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $this->assertSame('foo', $formRuntime->getIdentifier());
    }

    /**
     * @test
     */
    public function getRenderingOptionsReturnsRenderingOptionsOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setRenderingOption('asdf', 'test');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $this->assertSame(['asdf' => 'test'], $formRuntime->getRenderingOptions());
    }

    /**
     * @test
     */
    public function getRendererClassNameReturnsRendererClassNameOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setRendererClassName('MyRendererClassName');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $this->assertSame('MyRendererClassName', $formRuntime->getRendererClassName());
    }

    /**
     * @test
     */
    public function getLabelReturnsLabelOfFormDefinition()
    {
        $formDefinition = new FormDefinition('foo');
        $formDefinition->setLabel('my cool label');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $this->assertSame('my cool label', $formRuntime->getLabel());
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

        $this->assertSame(2, count($finisherCalls));
        $this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
        $this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
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
        $this->assertSame([$page1, $page2, $page3], $formRuntime->getPages());

        $formRuntime->overrideCurrentPage(0);
        $this->assertSame(null, $formRuntime->getPreviousPage());
        $this->assertSame($page1, $formRuntime->getCurrentPage());
        $this->assertSame($page2, $formRuntime->getNextPage());

        $formRuntime->overrideCurrentPage(1);
        $this->assertSame($page1, $formRuntime->getPreviousPage());
        $this->assertSame($page2, $formRuntime->getCurrentPage());
        $this->assertSame($page3, $formRuntime->getNextPage());

        $formRuntime->overrideCurrentPage(2);
        $this->assertSame($page2, $formRuntime->getPreviousPage());
        $this->assertSame($page3, $formRuntime->getCurrentPage());
        $this->assertSame(null, $formRuntime->getNextPage());
    }

    /**
     * @test
     */
    public function arrayAccessReturnsDefaultValuesIfSet()
    {
        $formDefinition = new FormDefinition('foo');
        $page1 = new Page('p1');
        $formDefinition->addPage($page1);
        $element1 = new \Neos\Form\FormElements\GenericFormElement('foo', 'Bar');
        $page1->addElement($element1);

        $element1->setDefaultValue('My Default');
        $formRuntime = $this->createFormRuntime($formDefinition);
        $formState = new \Neos\Form\Core\Runtime\FormState();
        $formRuntime->_set('formState', $formState);
        $this->assertSame($formState, $formRuntime->getFormState());

        $this->assertSame('My Default', $formRuntime['foo']);
        $formRuntime['foo'] = 'Overridden';
        $this->assertSame('Overridden', $formRuntime['foo']);
        $formRuntime['foo'] = null;
        $this->assertSame('My Default', $formRuntime['foo']);

        $formRuntime['foo'] = 'Overridden2';
        $this->assertSame('Overridden2', $formRuntime['foo']);

        unset($formRuntime['foo']);
        $this->assertSame('My Default', $formRuntime['foo']);

        $this->assertSame(null, $formRuntime['nonExisting']);
    }

    /**
     * @param FormDefinition $formDefinition
     * @return \Neos\Form\Core\Runtime\FormRuntime
     */
    protected function createFormRuntime(FormDefinition $formDefinition)
    {
        $mockActionRequest = $this->getMockBuilder(\Neos\Flow\Mvc\ActionRequest::class)->disableOriginalConstructor()->getMock();
        $mockHttpResponse = $this->getMockBuilder(\Neos\Flow\Http\Response::class)->disableOriginalConstructor()->getMock();

        return $this->getAccessibleMock(\Neos\Form\Core\Runtime\FormRuntime::class, ['dummy'], [$formDefinition, $mockActionRequest, $mockHttpResponse]);
    }
}
