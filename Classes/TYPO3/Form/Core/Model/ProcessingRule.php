<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A processing Rule contains information for property mapping and validation.
 *
 * **This class is not meant to be subclassed by developers.**
 */
class ProcessingRule {

	/**
	 * The target data type the data should be converted to
	 *
	 * @var string
	 */
	protected $dataType;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMappingConfiguration
	 */
	protected $propertyMappingConfiguration;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Validation\Validator\ConjunctionValidator
	 */
	protected $validator;

	/**
	 * @var \TYPO3\Flow\Error\Result
	 */
	protected $processingMessages;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 * @internal
	 */
	protected $propertyMapper;

	/**
	 * Constructs this processing rule
	 */
	public function __construct() {
		$this->processingMessages = new \TYPO3\Flow\Error\Result();
	}

	/**
	 * @return \TYPO3\Flow\Property\PropertyMappingConfiguration
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

	/**
	 * Returns the child validators of the ConjunctionValidator that is bound to this processing rule
	 *
	 * @return \SplObjectStorage<\TYPO3\Flow\Validation\Validator\ValidatorInterface>
	 * @internal
	 */
	public function getValidators() {
		return $this->validator->getValidators();
	}

	/**
	 * @param \TYPO3\Flow\Validation\Validator\ValidatorInterface $validator
	 * @return void
	 */
	public function addValidator(\TYPO3\Flow\Validation\Validator\ValidatorInterface $validator) {
		$this->validator->addValidator($validator);
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function process($value) {
		if ($this->dataType !== NULL) {
			$value = $this->propertyMapper->convert($value, $this->dataType, $this->propertyMappingConfiguration);
			$messages = $this->propertyMapper->getMessages();
		} else {
			$messages = new \TYPO3\Flow\Error\Result();
		}

		$validationResult = $this->validator->validate($value);
		$messages->merge($validationResult);

		$this->processingMessages->merge($messages);
		return $value;
	}

	/**
	 * @return \TYPO3\Flow\Error\Result
	 */
	public function getProcessingMessages() {
		return $this->processingMessages;
	}
}
