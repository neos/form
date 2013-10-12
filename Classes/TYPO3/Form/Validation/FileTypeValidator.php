<?php
namespace TYPO3\Form\Validation;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * The given $value is valid if it is an \TYPO3\Flow\Resource\Resource of the configured resolution
 * Note: a value of NULL or empty string ('') is considered valid
 */
class FileTypeValidator extends \TYPO3\Flow\Validation\Validator\AbstractValidator {

	/**
	 * @var array
	 */
	protected $supportedOptions = array(
		'allowedExtensions' => array(array(), 'Array of allowed file extensions', 'array', TRUE)
	);

	/**
	 * The given $value is valid if it is an \TYPO3\Flow\Resource\Resource of the configured resolution
	 * Note: a value of NULL or empty string ('') is considered valid
	 *
	 * @param \TYPO3\Flow\Resource\Resource $resource The resource object that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($resource) {
		if (!$resource instanceof \TYPO3\Flow\Resource\Resource) {
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
}
