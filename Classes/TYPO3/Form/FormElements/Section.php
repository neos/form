<?php
namespace TYPO3\Form\FormElements;

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
 * A Section, being part of a bigger Page
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 *
 * Once we support traits, the duplicated code between AbstractFormElement and Section could be extracted to a Trait.
 */
class Section extends \TYPO3\Form\Core\Model\AbstractSection implements \TYPO3\Form\Core\Model\FormElementInterface {

	/**
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Will be called as soon as the element is (tried to be) added to a form
	 * @see registerInFormIfPossible()
	 *
	 * @return void
	 * @internal
	 */
	public function initializeFormElement() {
	}

	/**
	 * Returns a unique identifier of this element.
	 * While element identifiers are only unique within one form,
	 * this includes the identifier of the form itself, making it "globally" unique
	 *
	 * @return string the "globally" unique identifier of this element
	 * @api
	 */
	public function getUniqueIdentifier() {
		$formDefinition = $this->getRootForm();
		return sprintf('%s-%s', $formDefinition->getIdentifier(), $this->identifier);
	}

	/**
	 * Get the default value with which the Form Element should be initialized
	 * during display.
	 * Note: This is currently not used for section elements
	 *
	 * @return mixed the default value for this Form Element
	 * @api
	 */
	public function getDefaultValue() {
		return NULL;
	}

	/**
	 * Set the default value with which the Form Element should be initialized
	 * during display.
	 * Note: This is currently ignored for section elements
	 *
	 * @param mixed $defaultValue the default value for this Form Element
	 * @api
	 */
	public function setDefaultValue($defaultValue) {

	}


	/**
	 * Get all element-specific configuration properties
	 *
	 * @return array
	 * @api
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Set an element-specific configuration property.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 * @api
	 */
	public function setProperty($key, $value) {
		$this->properties[$key] = $value;
	}

	/**
	 * Set the rendering option $key to $value.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 * @return mixed
	 */
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	/**
	 * Get all validators on the element
	 *
	 * @return \SplObjectStorage
	 */
	public function getValidators() {
		$formDefinition = $this->getRootForm();
		return $formDefinition->getProcessingRule($this->getIdentifier())->getValidators();
	}

	/**
	 * Add a validator to the element
	 *
	 * @param \TYPO3\Flow\Validation\Validator\ValidatorInterface $validator
	 * @return void
	 */
	public function addValidator(\TYPO3\Flow\Validation\Validator\ValidatorInterface $validator) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->addValidator($validator);
	}

	/**
	 * Whether or not this element is required
	 *
	 * @return boolean
	 * @api
	 */
	public function isRequired() {
		foreach ($this->getValidators() as $validator) {
			if ($validator instanceof \TYPO3\Flow\Validation\Validator\NotEmptyValidator) {
				return TRUE;
			}
		}
		return FALSE;
	}
}
