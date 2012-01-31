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
		$formDefinition = $this->getRootForm();
		$processingRule = $formDefinition->getProcessingRule($this->identifier);
		$resourceTypeConverter = new \TYPO3\FLOW3\Resource\ResourceTypeConverter();
		$processingRule->getPropertyMappingConfiguration()->setTypeConverter($resourceTypeConverter);
		$processingRule->setDataType('TYPO3\FLOW3\Resource\Resource');
		$fileTypeValidator = new \TYPO3\Form\Validation\FileTypeValidator(array('allowedExtensions' => $this->properties['allowedExtensions']));
		$processingRule->addValidator($fileTypeValidator);
	}
}
?>