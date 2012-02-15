<?php
namespace TYPO3\Form\FormElements;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A generic file upload form element
 */
class FileUpload extends \TYPO3\Form\Core\Model\AbstractFormElement {

	/**
	 * @return void
	 */
	public function initializeFormElement() {
		$this->setDataType('TYPO3\FLOW3\Resource\Resource');
		$fileTypeValidator = new \TYPO3\Form\Validation\FileTypeValidator(array('allowedExtensions' => $this->properties['allowedExtensions']));
		$this->addValidator($fileTypeValidator);
	}
}
?>