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
 * Test for AbstractFinisher
 * @covers \Neos\Form\Core\Model\AbstractFinisher<extended>
 * @covers \Neos\Form\Core\Model\FinisherContext<extended>
 * @covers \Neos\Form\Core\Runtime\FormRuntime<extended>
 * @covers \Neos\Form\Core\Runtime\FormState<extended>
 */
class AbstractFinisherTest extends \Neos\Flow\Tests\UnitTestCase
{
    protected $formRuntime = null;

    /**
     * @test
     */
    public function executeSetsFinisherContextAndCallsExecuteInternal()
    {
        $finisher = $this->getAbstractFinisher();
        $finisher->expects($this->once())->method('executeInternal');

        $finisherContext = $this->getFinisherContext();
        $finisher->execute($finisherContext);
        $this->assertSame($finisherContext, $finisher->_get('finisherContext'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsPreviouslySetOption()
    {
        $finisher = $this->getAbstractFinisher();
        $finisherContext = $this->getFinisherContext();
        $finisher->execute($finisherContext);

        $finisher->setOptions(array('foo' => 'bar'));
        $this->assertSame('bar', $finisher->_call('parseOption', 'foo'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsNumbersAndSimpleTypesWithoutModification()
    {
        $finisher = $this->getAbstractFinisher();
        $finisherContext = $this->getFinisherContext();
        $finisher->execute($finisherContext);

        $obj = new \stdClass();
        $finisher->setOptions(array('foo' => 42, 'baz' => $obj));
        $this->assertSame(42, $finisher->_call('parseOption', 'foo'));
        $this->assertSame($obj, $finisher->_call('parseOption', 'baz'));
    }

    public function dataProviderForDefaultOptions()
    {
        $defaultOptions = array(
            'overridden1' => 'Overridden1Default',
            'nullOption' => 'NullDefault',
            'emptyStringOption' => 'EmptyStringDefault',
            'nonExisting' => 'NonExistingDefault'
        );

        $options = array(
            'overridden1' => 'MyString',
            'nullOption' => null,
            'emptyStringOption' => '',
            'someOptionWithoutDefault' => ''
        );

        return array(
            'Empty String is regarded as non-set value' => array(
                'defaultOptions' => $defaultOptions,
                'options' => $options,
                'optionKey' => 'emptyStringOption',
                'expected' => 'EmptyStringDefault'
            ),
            'null is regarded as non-set value' => array(
                'defaultOptions' => $defaultOptions,
                'options' => $options,
                'optionKey' => 'nullOption',
                'expected' => 'NullDefault'
            ),
            'non-existing key is regarded as non-set value' => array(
                'defaultOptions' => $defaultOptions,
                'options' => $options,
                'optionKey' => 'nonExisting',
                'expected' => 'NonExistingDefault'
            ),
            'empty string is unified to NULL if no default value exists' => array(
                'defaultOptions' => $defaultOptions,
                'options' => $options,
                'optionKey' => 'someOptionWithoutDefault',
                'expected' => null
            )
        );
    }

    /**
     * @dataProvider dataProviderForDefaultOptions
     * @test
     */
    public function parseOptionReturnsDefaultOptionIfNecessary($defaultOptions, $options, $optionKey, $expected)
    {
        $finisher = $this->getAbstractFinisher();
        $finisherContext = $this->getFinisherContext();
        $finisher->execute($finisherContext);

        $finisher->setOptions($options);
        $finisher->_set('defaultOptions', $defaultOptions);
        $this->assertSame($expected, $finisher->_call('parseOption', $optionKey));
    }

    public function dataProviderForPlaceholderReplacement()
    {
        $formValues = array(
            'foo' => 'My Value',
            'bar.baz' => 'Trst'
        );

        return array(
            'Simple placeholder' => array(
                'formValues' => $formValues,
                'optionValue' => 'test {foo} baz',
                'expected' => 'test My Value baz'
            ),
            'Property Path' => array(
                'formValues' => $formValues,
                'optionValue' => 'test {bar.baz} baz',
                'expected' => 'test Trst baz'
            ),
        );
    }

    /**
     * @dataProvider dataProviderForPlaceholderReplacement
     * @test
     */
    public function placeholdersAreReplacedWithFormRuntimeValues($formValues, $optionValue, $expected)
    {
        $finisher = $this->getAbstractFinisher();
        $finisherContext = $this->getFinisherContext();
        $formState = new \Neos\Form\Core\Runtime\FormState();
        foreach ($formValues as $key => $value) {
            $formState->setFormValue($key, $value);
        }

        $this->formRuntime->_set('formState', $formState);
        $finisher->execute($finisherContext);

        $finisher->setOptions(array('key1' => $optionValue));
        $this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
    }

    /**
     * @dataProvider dataProviderForPlaceholderReplacement
     * @test
     */
    public function placeholdersInsideDefaultsReplacedWithFormRuntimeValues($formValues, $optionValue, $expected)
    {
        $finisher = $this->getAbstractFinisher();
        $finisherContext = $this->getFinisherContext();
        $formState = new \Neos\Form\Core\Runtime\FormState();
        foreach ($formValues as $key => $value) {
            $formState->setFormValue($key, $value);
        }

        $this->formRuntime->_set('formState', $formState);
        $finisher->execute($finisherContext);

        $finisher->_set('defaultOptions', array('key1' => $optionValue));
        $this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
    }

    /**
     * @test
     */
    public function cancelCanBeSetOnFinisherContext()
    {
        $finisherContext = $this->getFinisherContext();
        $this->assertFalse($finisherContext->isCancelled());
        $finisherContext->cancel();
        $this->assertTrue($finisherContext->isCancelled());
    }

    /**
     * @return \Neos\Form\Core\Model\AbstractFinisher
     */
    protected function getAbstractFinisher()
    {
        return $this->getAccessibleMock(\Neos\Form\Core\Model\AbstractFinisher::class, array('executeInternal'));
    }

    /**
     * @return \Neos\Form\Core\Model\FinisherContext
     */
    protected function getFinisherContext()
    {
        $this->formRuntime = $this->getAccessibleMock(\Neos\Form\Core\Runtime\FormRuntime::class, array('dummy'), array(), '', false);
        return new \Neos\Form\Core\Model\FinisherContext($this->formRuntime);
    }
}
