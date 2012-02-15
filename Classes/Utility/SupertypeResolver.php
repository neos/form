<?php
namespace TYPO3\Form\Utility;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Merges configuration based on the "superTypes" property of a so-called "type definition".
 *
 * @internal
 */
class SupertypeResolver {

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * @var array
	 */
	protected $settings;

	public function __construct($configuration) {
		$this->configuration = $configuration;

	}

	/**
	 * @param array $settings
	 * @internal
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 *
	 * @param string $type
	 * @param boolean $showHiddenProperties if TRUE, the hidden properties are shown as configured in settings "supertypeResolver.hiddenProperties" are shown as well. FALSE by default
	 * @return array
	 * @throws \TYPO3\Form\Exception\TypeDefinitionNotFoundException if a type definition was not found
	 * @internal
	 */
	public function getMergedTypeDefinition($type, $showHiddenProperties = FALSE) {
		if (isset($this->configuration[$type])) {
			$mergedTypeDefinition = array();
			if (isset($this->configuration[$type]['superTypes'])) {
				foreach ($this->configuration[$type]['superTypes'] as $superType) {
					$mergedTypeDefinition = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->getMergedTypeDefinition($superType, $showHiddenProperties));
				}
			}
			$mergedTypeDefinition = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->configuration[$type]);
			unset($mergedTypeDefinition['superTypes']);

			if ($showHiddenProperties === FALSE && isset($this->settings['supertypeResolver']['hiddenProperties']) && is_array($this->settings['supertypeResolver']['hiddenProperties'])) {
				foreach ($this->settings['supertypeResolver']['hiddenProperties'] as $propertyName) {
					unset($mergedTypeDefinition[$propertyName]);
				}
			}

			return $mergedTypeDefinition;
		} else {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('Type "%s" not found. Probably some configuration is missing.', $type), 1325686909);
		}
	}

	/**
	 * @param boolean $showHiddenProperties  if TRUE, the hidden properties are shown as configured in settings "supertypeResolver.hiddenProperties" are shown as well. FALSE by default
	 * @return array associative array of all completely merged type definitions.
	 * @internal
	 */
	public function getCompleteMergedTypeDefinition($showHiddenProperties = FALSE) {
		$configuration = array();
		foreach (array_keys($this->configuration) as $type) {
			$configuration[$type] = $this->getMergedTypeDefinition($type, $showHiddenProperties);
		}
		return $configuration;
	}
}
?>