<?php
namespace TYPO3\Form\Domain\Factory;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @todo document
 */
class AbstractFormFactory implements FormFactoryInterface {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @internal
	 */
	protected $configurationManager;

	public function initializeObject() {
		$this->settings = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Form');
	}

	/**
	 * @param array $configuration The factory-specific configuration
	 * @todo document
	 */
	public function build(array $configuration, $presetName) {
	}

	/**
	 *
	 * @param string $presetName
	 * @return array
	 * @api
	 */
	protected function getMergedConfiguration($presetName) {
		if (!isset($this->settings['Presets'][$presetName])) {
			throw new \TYPO3\Form\Exception\PresetNotFoundException(sprintf('The Preset "%s" was not found underneath TYPO3: Form: Presets.', $presetName), 1325685498);
		}
		$preset = $this->settings['Presets'][$presetName];
		if (isset($preset['parentPreset'])) {
			$parentPreset = $this->getMergedConfiguration($preset['parentPreset']);
			unset($preset['parentPreset']);
			$preset = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($parentPreset, $preset);
		}
		return $preset;
	}
}
?>