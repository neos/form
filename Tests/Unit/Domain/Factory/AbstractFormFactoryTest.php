<?php
namespace TYPO3\Form\Tests\Unit\Domain\Factory;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Utility\SupertypeResolver;

/**
 * Test for Supertype Resolver
 * @covers \TYPO3\Form\Domain\Factory\AbstractFormFactory
 */
class AbstractFormFactoryTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	public function dataProviderForConfigurationMerging() {
		$presets = array(
			'Default' => array(
				'formElementTypes' => array(
					'TYPO3.Form:Base' => array()
				)
			),
			'Special' => array(
				'parentPreset' => 'Default',
				'foo' => 'bar',
				'baz' => array(
					'test' => 'yeah'
				)
			),
			'SpecialSub' => array(
				'parentPreset' => 'Special',
				'baz' => array(
					'test' => 42
				)
			)
		);
		return array(
			'preset without parent present' => array(
				'presets' => $presets,
				'presetName' => 'Default',
				'expected' => array(
					'formElementTypes' => array(
						'TYPO3.Form:Base' => array()
					)
				)
			),

			'preset with one parent preset' => array(
				'presets' => $presets,
				'presetName' => 'Special',
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
				'presetName' => 'SpecialSub',
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
		$abstractFormFactory->_set('settings', array(
			'Presets' => $presets
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
	 * @return \TYPO3\Form\Domain\Factory\AbstractFormFactory
	 */
	protected function getAbstractFormFactory() {
		return $this->getAccessibleMock('TYPO3\Form\Domain\Factory\AbstractFormFactory', array('dummy'));
	}
}
?>