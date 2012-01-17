<?php
namespace TYPO3\Form\FormElements;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Section, being part of a bigger Page
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
class Section extends \TYPO3\Form\Core\Model\AbstractSection implements \TYPO3\Form\Core\Model\FormElementInterface {

	public function getDefaultValue() {
		return NULL;
	}
	public function getProperties() {
		return array();
	}
	public function setDefaultValue($defaultValue) {

	}
	public function setProperty($key, $value) {

	}
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	public function getValidator() {
		$formDefinition = $this->getRootForm();
		if ($formDefinition !== NULL) {
			return $formDefinition->getProcessingRule($this->getIdentifier())->getValidator();
		} else {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The form element "%s" is not attached to a parent form, thus getValidator() cannot be called.', $this->identifier), 1326824120);
		}
	}

	public function isRequired() {
		$conjunctionValidator = $this->getValidator();
		foreach ($conjunctionValidator->getValidators() as $validator) {
			if ($validator instanceof \TYPO3\FLOW3\Validation\Validator\NotEmptyValidator) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>