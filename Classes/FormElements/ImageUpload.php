<?php
namespace TYPO3\Form\FormElements;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An image upload form element
 */
class ImageUpload extends \TYPO3\Form\Core\Model\AbstractFormElement {

	/**
	 * @return void
	 */
	public function initializeFormElement() {
		$this->setDataType('TYPO3\Media\Domain\Model\Image');
		$imageTypeValidator = new \TYPO3\Media\Validator\ImageTypeValidator(array('allowedTypes' => $this->properties['allowedTypes']));
		$this->addValidator($imageTypeValidator);
	}
}
?>