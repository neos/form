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

/**
 * Test for AbstractFinisher.
 *
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

        $finisher->setOptions(['foo' => 'bar']);
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
        $finisher->setOptions(['foo' => 42, 'baz' => $obj]);
        $this->assertSame(42, $finisher->_call('parseOption', 'foo'));
        $this->assertSame($obj, $finisher->_call('parseOption', 'baz'));
    }

    public function dataProviderForDefaultOptions()
    {
        $defaultOptions = [
            'overridden1'       => 'Overridden1Default',
            'nullOption'        => 'NullDefault',
            'emptyStringOption' => 'EmptyStringDefault',
            'nonExisting'       => 'NonExistingDefault',
        ];

        $options = [
            'overridden1'              => 'MyString',
            'nullOption'               => null,
            'emptyStringOption'        => '',
            'someOptionWithoutDefault' => '',
        ];

        return [
            'Empty String is regarded as non-set value' => [
                'defaultOptions' => $defaultOptions,
                'options'        => $options,
                'optionKey'      => 'emptyStringOption',
                'expected'       => 'EmptyStringDefault',
            ],
            'null is regarded as non-set value' => [
                'defaultOptions' => $defaultOptions,
                'options'        => $options,
                'optionKey'      => 'nullOption',
                'expected'       => 'NullDefault',
            ],
            'non-existing key is regarded as non-set value' => [
                'defaultOptions' => $defaultOptions,
                'options'        => $options,
                'optionKey'      => 'nonExisting',
                'expected'       => 'NonExistingDefault',
            ],
            'empty string is unified to NULL if no default value exists' => [
                'defaultOptions' => $defaultOptions,
                'options'        => $options,
                'optionKey'      => 'someOptionWithoutDefault',
                'expected'       => null,
            ],
        ];
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
        $formValues = [
            'foo'     => 'My Value',
            'bar.baz' => 'Trst',
        ];

        return [
            'Simple placeholder' => [
                'formValues'  => $formValues,
                'optionValue' => 'test {foo} baz',
                'expected'    => 'test My Value baz',
            ],
            'Property Path' => [
                'formValues'  => $formValues,
                'optionValue' => 'test {bar.baz} baz',
                'expected'    => 'test Trst baz',
            ],
        ];
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

        $finisher->setOptions(['key1' => $optionValue]);
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

        $finisher->_set('defaultOptions', ['key1' => $optionValue]);
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
        return $this->getAccessibleMock(\Neos\Form\Core\Model\AbstractFinisher::class, ['executeInternal']);
    }

    /**
     * @return \Neos\Form\Core\Model\FinisherContext
     */
    protected function getFinisherContext()
    {
        $this->formRuntime = $this->getAccessibleMock(\Neos\Form\Core\Runtime\FormRuntime::class, ['dummy'], [], '', false);

        return new \Neos\Form\Core\Model\FinisherContext($this->formRuntime);
    }
}
