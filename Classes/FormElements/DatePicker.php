<?php
namespace TYPO3\Form\FormElements;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A date picker form element
 */
class DatePicker extends \TYPO3\Form\Core\Model\AbstractFormElement {

	/**
	 * @return void
	 */
	public function initializeFormElement() {
		$formDefinition = $this->getRootForm();
		$processingRule = $formDefinition->getProcessingRule($this->identifier);
		$dateTimeConverter = new \TYPO3\FLOW3\Property\TypeConverter\DateTimeConverter();
		$processingRule->getPropertyMappingConfiguration()->setTypeConverter($dateTimeConverter);
		$processingRule->setDataType('DateTime');
	}
}
?>