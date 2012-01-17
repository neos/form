<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A processing Rule which contains information
 * for property mapping and validation.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * **This class is not yet fully specified; and is also only used internally
 * in the framework**.
 */
class ProcessingRule {

	/**
	 * The target data type the data should be converted to
	 *
	 * @var string
	 */
	protected $dataType;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Property\PropertyMappingConfiguration
	 */
	protected $propertyMappingConfiguration;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator
	 */
	protected $validator;

	/**
	 * @var \TYPO3\FLOW3\Error\Result
	 */
	protected $processingMessages;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Property\PropertyMapper
	 * @internal
	 */
	protected $propertyMapper;

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

	public function getValidator() {
		return $this->validator;
	}

	public function addValidator(\TYPO3\FLOW3\Validation\Validator\ValidatorInterface $validator) {
		$this->validator->addValidator($validator);
	}

	public function process($value) {
		if ($this->dataType !== NULL) {
			$value = $this->propertyMapper->convert($value, $this->dataType, $this->propertyMappingConfiguration);
			$messages = $this->propertyMapper->getMessages();
		} else {
			$messages = new \TYPO3\FLOW3\Error\Result();
		}

		$validationResult = $this->validator->validate($value);
		$messages->merge($validationResult);

		$this->processingMessages = $messages;
		return $value;
	}

	public function getProcessingMessages() {
		return $this->processingMessages;
	}
}
?>