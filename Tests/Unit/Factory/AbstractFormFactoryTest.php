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

use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Tests\UnitTestCase;
use Neos\Form\Exception\PresetNotFoundException;
use Neos\Form\Factory\AbstractFormFactory;
use PHPUnit\Framework\Assert;

/**
 * Test for Supertype Resolver
 * @covers \Neos\Form\Factory\AbstractFormFactory<extended>
 */
class AbstractFormFactoryTest extends UnitTestCase
{
    public function dataProviderForConfigurationMerging()
    {
        $presets = [
            'default' => [
                'formElementTypes' => [
                    'Neos.Form:Base' => []
                ]
            ],
            'special' => [
                'parentPreset' => 'default',
                'foo' => 'bar',
                'baz' => [
                    'test' => 'yeah'
                ]
            ],
            'specialSub' => [
                'parentPreset' => 'special',
                'baz' => [
                    'test' => 42
                ]
            ]
        ];
        return [
            'preset without parent present' => [
                'presets' => $presets,
                'presetName' => 'default',
                'expected' => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => []
                    ]
                ]
            ],

            'preset with one parent preset' => [
                'presets' => $presets,
                'presetName' => 'special',
                'expected' => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => []
                    ],
                    'foo' => 'bar',
                    'baz' => [
                        'test' => 'yeah'
                    ]
                ]
            ],

            'preset with two parent presets' => [
                'presets' => $presets,
                'presetName' => 'specialSub',
                'expected' => [
                    'formElementTypes' => [
                        'Neos.Form:Base' => []
                    ],
                    'foo' => 'bar',
                    'baz' => [
                        'test' => 42
                    ]
                ]
            ]
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
            'presets' => $presets
        ]);

        $actual = $abstractFormFactory->_call('getPresetConfiguration', $presetName);
        Assert::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getPresetConfigurationThrowsExceptionIfPresetIsNotFound()
    {
        $this->expectException(PresetNotFoundException::class);
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_call('getPresetConfiguration', 'NonExistingPreset');
    }

    /**
     * @test
     */
    public function initializeObjectLoadsSettings()
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $mockConfigurationManager = $this->getMockBuilder(ConfigurationManager::class)->disableOriginalConstructor()->getMock();
        $mockConfigurationManager
            ->expects($this->once())
            ->method('getConfiguration')
            ->with(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Form')
            ->will($this->returnValue('MyConfig'));
        $abstractFormFactory->_set('configurationManager', $mockConfigurationManager);

        $abstractFormFactory->_call('initializeObject');
        Assert::assertSame('MyConfig', $abstractFormFactory->_get('formSettings'));
    }

    /**
     * @return AbstractFormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAbstractFormFactory()
    {
        return $this->getAccessibleMock(AbstractFormFactory::class, ['build']);
    }

    /**
     * @dataProvider dataProviderForConfigurationMerging
     * @test
     */
    public function getPresetsWorks($presets)
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_set('formSettings', [
            'presets' => $presets
        ]);

        $actual = $abstractFormFactory->getPresetNames();
        Assert::assertSame(['default', 'special', 'specialSub'], $actual);
    }
}
