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

use Neos\Form\Utility\SupertypeResolver;

/**
 * Test for Supertype Resolver
 * @covers \Neos\Form\Factory\AbstractFormFactory<extended>
 */
class AbstractFormFactoryTest extends \Neos\Flow\Tests\UnitTestCase
{
    public function dataProviderForConfigurationMerging()
    {
        $presets = array(
            'default' => array(
                'formElementTypes' => array(
                    'Neos.Form:Base' => array()
                )
            ),
            'special' => array(
                'parentPreset' => 'default',
                'foo' => 'bar',
                'baz' => array(
                    'test' => 'yeah'
                )
            ),
            'specialSub' => array(
                'parentPreset' => 'special',
                'baz' => array(
                    'test' => 42
                )
            )
        );
        return array(
            'preset without parent present' => array(
                'presets' => $presets,
                'presetName' => 'default',
                'expected' => array(
                    'formElementTypes' => array(
                        'Neos.Form:Base' => array()
                    )
                )
            ),

            'preset with one parent preset' => array(
                'presets' => $presets,
                'presetName' => 'special',
                'expected' => array(
                    'formElementTypes' => array(
                        'Neos.Form:Base' => array()
                    ),
                    'foo' => 'bar',
                    'baz' => array(
                        'test' => 'yeah'
                    )
                )
            ),

            'preset with two parent presets' => array(
                'presets' => $presets,
                'presetName' => 'specialSub',
                'expected' => array(
                    'formElementTypes' => array(
                        'Neos.Form:Base' => array()
                    ),
                    'foo' => 'bar',
                    'baz' => array(
                        'test' => 42
                    )
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderForConfigurationMerging
     * @test
     */
    public function getPresetConfigurationReturnsCorrectConfigurationForPresets($presets, $presetName, $expected)
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_set('formSettings', array(
            'presets' => $presets
        ));

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
        return $this->getAccessibleMock(\Neos\Form\Factory\AbstractFormFactory::class, array('build'));
    }

    /**
     * @dataProvider dataProviderForConfigurationMerging
     * @test
     */
    public function getPresetsWorks($presets, $presetName, $expected)
    {
        $abstractFormFactory = $this->getAbstractFormFactory();
        $abstractFormFactory->_set('formSettings', array(
            'presets' => $presets
        ));

        $actual = $abstractFormFactory->getPresetNames();
        $this->assertSame(array('default', 'special', 'specialSub'), $actual);
    }
}
