<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A base form element, which is the starting point for creating custom (PHP-based)
 * Form Elements.
 *
 * **This class is meant to be subclassed by developers.**
 *
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * Often, you should rather subclass this class instead of directly
 * implementing {@link FormElementInterface}.
 */
abstract class AbstractFormElement implements FormElementInterface {

	/**
	 * The identifier
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * the form element type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The parent page
	 *
	 * @var \TYPO3\Form\Domain\Model\Page
	 */
	protected $parentPage;

	/**
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var mixed
	 */
	protected $defaultValue = NULL;

	/**
	 * @var \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator
	 */
	protected $conjunctionValidator;

	/**
	 * Constructor. Needs this FormElement's identifier and the FormElement type
	 *
	 * @param string $identifier The FormElement's identifier
	 * @param string $type The Form Element Type
	 * @return void
	 * @api
	 */
	public function __construct($identifier, $type) {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
		$this->type = $type;
		$this->conjunctionValidator = new \TYPO3\FLOW3\Validation\Validator\ConjunctionValidator();
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function getParentPage() {
		return $this->parentPage;
	}

	public function setParentPage(Page $parentPage) {
		$this->parentPage = $parentPage;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getType() {
		return $this->type;
	}

	public function getTemplateVariableName() {
		return 'element';
	}

	public function getDefaultValue() {
		return $this->defaultValue;
	}

	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}

	public function addValidator(\TYPO3\FLOW3\Validation\Validator\ValidatorInterface $validator) {
		$this->conjunctionValidator->addValidator($validator);
	}

	public function getValidator() {
		return $this->conjunctionValidator;
	}
}
?>