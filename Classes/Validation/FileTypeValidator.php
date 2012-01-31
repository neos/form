<?php
namespace TYPO3\Form\Validation;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * @todo document
 */
class FileTypeValidator extends \TYPO3\FLOW3\Validation\Validator\AbstractValidator {

	/**
	 * The given $value is valid if it is an \TYPO3\FLOW3\Resource\Resource of the configured resolution
	 * Note: a value of NULL or empty string ('') is considered valid
	 *
	 * @param \TYPO3\FLOW3\Resource\Resource $resource The resource object that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($resource) {
		$this->validateOptions();

		if (!$resource instanceof \TYPO3\FLOW3\Resource\Resource) {
			$this->addError('The given value was not a Resource instance.', 1327865587);
			return;
		}
		$fileExtension = $resource->getFileExtension();
		if ($fileExtension === NULL || $fileExtension === '') {
			$this->addError('The file has no file extension.', 1327865808);
			return;
		}
		if (!in_array($fileExtension, $this->options['allowedExtensions'])) {
			$this->addError('The file extension "%s" is not allowed.', 1327865764, array($resource->getFileExtension()));
			return;
		}
	}

	/**
	 * @return void
	 * @throws \TYPO3\FLOW3\Validation\Exception\InvalidValidationOptionsException if the configured validation options are incorrect
	 */
	protected function validateOptions() {
		if (!isset($this->options['allowedExtensions'])) {
			throw new \TYPO3\FLOW3\Validation\Exception\InvalidValidationOptionsException('The option "allowedExtensions" was not specified.', 1327865682);
		} elseif (!is_array($this->options['allowedExtensions']) || $this->options['allowedExtensions'] === array()) {
			throw new \TYPO3\FLOW3\Validation\Exception\InvalidValidationOptionsException('The option "allowedExtensions" must be an array with at least one item.', 1328032876);
		}
	}
}

?>