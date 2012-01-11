<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A mapping Rule which contains information
 * for property mapping and validation.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * **This class is not yet fully specified; and is also only used internally
 * in the framework**.
 */
class MappingRule {

	/**
	 * @var string
	 */
	protected $dataType;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Property\PropertyMappingConfiguration
	 */
	protected $propertyMappingConfiguration;

	/**
	 * @return \TYPO3\FLOW3\Property\PropertyMappingConfiguration
	 */
	public function getPropertyMappingConfiguration() {
		return $this->propertyMappingConfiguration;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @param string $dataType
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
	}
}
?>