<?php
namespace TYPO3\Form\Utility;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * @todo document
 * @internal
 */
class SupertypeResolver {

	/**
	 * @var array
	 */
	protected $configuration;

	public function __construct($configuration) {
		$this->configuration = $configuration;

	}

	/**
	 *
	 * @param type $type
	 * @return type
	 * @throws \Exception
	 * @internal
	 */
	public function getMergedTypeDefinition($type) {
		if (isset($this->configuration[$type])) {
			$mergedTypeDefinition = array();
			if (isset($this->configuration[$type]['superTypes'])) {
				foreach ($this->configuration[$type]['superTypes'] as $superType) {
					$mergedTypeDefinition = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->getMergedTypeDefinition($superType));
				}
			}
			$mergedTypeDefinition = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedTypeDefinition, $this->configuration[$type]);
			unset($mergedTypeDefinition['superTypes']);
			return $mergedTypeDefinition;
		} else {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('Type "%s" not found. Probably some configuration is missing.', $type), 1325686909);
		}
	}
}
?>