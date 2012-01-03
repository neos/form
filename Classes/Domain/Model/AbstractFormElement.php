<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Form element
 */
abstract class AbstractFormElement implements FormElementInterface {

	/**
	 * The identifier
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string the form element type
	 */
	protected $type;

	/**
	 * The parent page
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
	 * Constructor. Needs this FormElement's identifier
	 *
	 * @param string $identifier The FormElement's identifier
	 * @param string $type The Form Element Type
	 * @return void
	 * @api
	 */
	public function __construct($identifier, $type) {
		$this->identifier = $identifier;
		$this->type = $type;
	}
	/**
	 * Get the Form element's identifier
	 *
	 * @return string The Form element's identifier
	 * @api
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get the Form element's parent page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page The Form element's parent page
	 * @internal
	 */
	public function getParentPage() {
		return $this->parentPage;
	}

	/**
	 * Sets this Form element's parent page
	 *
	 * @param \TYPO3\Form\Domain\Model\Page $parentPage The Form element's parent page
	 * @return void
	 * @internal
	 */
	public function setParentPage(Page $parentPage) {
		$this->parentPage = $parentPage;
	}

	/**
	 * @return string
	 * @api
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 * @api
	 */
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
}
?>