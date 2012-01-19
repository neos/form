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
	public function registerInFormIfPossible() {
		// TODO this should be done through the form element definition
		$formDefinition = $this->getRootForm();
		$processingRule = $formDefinition->getProcessingRule($this->identifier);
		$processingRule->setDataType('\TYPO3\Media\Domain\Model\Image');
	}
}
?>