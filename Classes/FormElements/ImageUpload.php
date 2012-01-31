<?php
namespace TYPO3\Form\FormElements;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
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
		$formDefinition = $this->getRootForm();
		$processingRule = $formDefinition->getProcessingRule($this->identifier);
		$imageConverter = new \TYPO3\Media\TypeConverter\ImageConverter();
		$processingRule->getPropertyMappingConfiguration()->setTypeConverter($imageConverter);
		$processingRule->setDataType('TYPO3\Media\Domain\Model\Image');
		$imageTypeValidator = new \TYPO3\Media\Validator\ImageTypeValidator(array('allowedTypes' => $this->properties['allowedTypes']));
		$processingRule->addValidator($imageTypeValidator);
	}
}
?>