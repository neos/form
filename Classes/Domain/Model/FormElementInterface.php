<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * A base form element interface, which can be the starting point for creating
 * custom (PHP-based) Form Elements.
 * 
 * A *FormElement* is a part of a *Page*, which in turn is part of a FormDefinition.
 * See {@link FormDefinition} for an in-depth explanation.
 *
 * Often, you should rather subclass {@link AbstractFormElement} instead of
 * implementing this interface.
 */
interface FormElementInterface extends RenderableInterface {

	/**
	 * Sets this Form element's parent page
	 *
	 * @param \TYPO3\Form\Domain\Model\Page $parentPage The Form element's parent page
	 * @return void
	 * @internal
	 */
	public function setParentPage(Page $parentPage);

	/**
	 * Get the Form element's parent page
	 *
	 * @return \TYPO3\Form\Domain\Model\Page The Form element's parent page
	 * @internal
	 */
	public function getParentPage();

	/**
	 * Get the label which shall be displayed next to the form element
	 *
	 * @return string
	 * @api
	 */
	public function getLabel();

	/**
	 * Set the label which shall be displayed next to the form element
	 *
	 * @param string $label
	 * @api
	 */
	public function setLabel($label);

	/**
	 * Get the default value with which the Form Element should be initialized
	 * during display.
	 *
	 * @return mixed the default value for this Form Element
	 * @api
	 */
	public function getDefaultValue();

	/**
	 * Set the default value with which the Form Element should be initialized
	 * during display.
	 *
	 * @param mixed $defaultValue the default value for this Form Element
	 * @api
	 */
	public function setDefaultValue($defaultValue);

}
?>