<?php

namespace Neos\Form\Tests\Unit\Factory;

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
 * Test for Supertype Resolver.
 *
 * @covers \Neos\Form\Factory\AbstractFormFactory<extended>
 */
class AbstractFormFactoryTest extends \Neos\Flow\Tests\UnitTestCase
{
    public function dataProviderForConfigurationMerging()
    {
        $presets = [
            'default' => [
                'formElementTypes' => [
                    'Neos.Form:Base' => [],
                ],
            ],
            'special' => [
                'parentPreset' => 'default',
                'foo'          => 'bar',
                'baz'          => [
                    'test' => 'yeah',
                ],
            ],
            'specialSub' => [
                'parentPreset' => 'special',
                'baz'          => [
                    'test' => 42,
                ],
            ],
        ];

        return [
            'preset without parent present' => [
                'presets'    => $presets,
                'presetName' => 'default',
                'expected'   => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => [],
                    ],
                ],
            ],

            'preset with one parent preset' => [
                'presets'    => $presets,
                'presetName' => 'special',
                'expected'   => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => [],
                    ],
                    'foo' => 'bar',
                    'baz' => [
                        'test' => 'yeah',
                    ],
                ],
            ],

            'preset with two parent presets' => [
                'presets'    => $presets,
                'presetName' => 'specialSub',
                'expected'   => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => [],
                    ],
                    'foo' => 'bar',
                    'baz' => [
                        'test' => 42,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForConfigurationMerging
     * @test
     */
    public function getPresetConfigurationReturnsCorrectConfigurationForPresets($presets, $presetName, $expected)
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_set('formSettings', [
            'presets' => $presets,
        ]);

        $actual = $abstractFormFactory->_call('getPresetConfiguration', $presetName);
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @expectedException Neos\Form\Exception\PresetNotFoundException
     */
    public function getPresetConfigurationThrowsExceptionIfPresetIsNotFound()
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_call('getPresetConfiguration', 'NonExistingPreset');
    }

    /**
     * @test
     */
    public function initializeObjectLoadsSettings()
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $mockConfigurationManager = $this->getMockBuilder(\Neos\Flow\Configuration\ConfigurationManager::class)->disableOriginalConstructor()->getMock();
        $mockConfigurationManager
            ->expects($this->once())
            ->method('getConfiguration')
            ->with(\Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Form')
            ->will($this->returnValue('MyConfig'));
        $abstractFormFactory->_set('configurationManager', $mockConfigurationManager);

        $abstractFormFactory->_call('initializeObject');
        $this->assertSame('MyConfig', $abstractFormFactory->_get('formSettings'));
    }

    /**
     * @return \Neos\Form\Factory\AbstractFormFactory
     */
    protected function getAbstractFormFactory()
    {
        return $this->getAccessibleMock(\Neos\Form\Factory\AbstractFormFactory::class, ['build']);
    }

    /**
     * @dataProvider dataProviderForConfigurationMerging
     * @test
     */
    public function getPresetsWorks($presets, $presetName, $expected)
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_set('formSettings', [
            'presets' => $presets,
        ]);

        $actual = $abstractFormFactory->getPresetNames();
        $this->assertSame(['default', 'special', 'specialSub'], $actual);
    }
}
