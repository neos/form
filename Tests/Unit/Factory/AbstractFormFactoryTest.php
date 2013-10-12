<?php
namespace TYPO3\Form\Tests\Unit\Factory;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Form\Utility\SupertypeResolver;

/**
 * Test for Supertype Resolver
 * @covers \TYPO3\Form\Factory\AbstractFormFactory<extended>
 */
class AbstractFormFactoryTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function dataProviderForConfigurationMerging() {
		$presets = array(
			'default' => array(
				'formElementTypes' => array(
					'TYPO3.Form:Base' => array()
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
						'TYPO3.Form:Base' => array()
					)
				)
			),

			'preset with one parent preset' => array(
				'presets' => $presets,
				'presetName' => 'special',
				'expected' => array(
					'formElementTypes' => array(
						'TYPO3.Form:Base' => array()
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
						'TYPO3.Form:Base' => array()
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
	public function getPresetConfigurationReturnsCorrectConfigurationForPresets($presets, $presetName, $expected) {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_set('formSettings', array(
			'presets' => $presets
		));

		$actual = $abstractFormFactory->_call('getPresetConfiguration', $presetName);
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @expectedException TYPO3\Form\Exception\PresetNotFoundException
	 */
	public function getPresetConfigurationThrowsExceptionIfPresetIsNotFound() {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_call('getPresetConfiguration', 'NonExistingPreset');
	}

	/**
	 * @test
	 */
	public function initializeObjectLoadsSettings() {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$mockConfigurationManager = $this->getMockBuilder('TYPO3\Flow\Configuration\ConfigurationManager')->disableOriginalConstructor()->getMock();
		$mockConfigurationManager
			->expects($this->once())
			->method('getConfiguration')
			->with(\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Form')
			->will($this->returnValue('MyConfig'));
		$abstractFormFactory->_set('configurationManager', $mockConfigurationManager);

		$abstractFormFactory->_call('initializeObject');
		$this->assertSame('MyConfig', $abstractFormFactory->_get('formSettings'));
	}

	/**
	 * @return \TYPO3\Form\Factory\AbstractFormFactory
	 */
	protected function getAbstractFormFactory() {
		return $this->getAccessibleMock('TYPO3\Form\Factory\AbstractFormFactory', array('build'));
	}

	/**
	 * @dataProvider dataProviderForConfigurationMerging
	 * @test
	 */
	public function getPresetsWorks($presets, $presetName, $expected) {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_set('formSettings', array(
			'presets' => $presets
		));

		$actual = $abstractFormFactory->getPresetNames();
		$this->assertSame(array('default', 'special', 'specialSub'), $actual);
	}
}
