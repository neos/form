<?php
namespace TYPO3\Form\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Creates a button.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:form.submit value="Send Mail" />
 * </code>
 * <output>
 * <input type="submit" />
 * </output>
 *
 * <code title="Dummy content for template preview">
 * <f:submit name="mySubmit" value="Send Mail"><button>dummy button</button></f:submit>
 * </code>
 * <output>
 * <input type="submit" name="mySubmit" value="Send Mail" />
 * </output>
 *
 * @api
 */
class ButtonViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'button';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('autofocus', 'string', 'Specifies that a button should automatically get focus when the page loads');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerTagAttribute('form', 'string', 'Specifies one or more forms the button belongs to');
		$this->registerTagAttribute('formaction', 'string', 'Specifies where to send the form-data when a form is submitted. Only for type="submit"');
		$this->registerTagAttribute('formenctype', 'string', 'Specifies how form-data should be encoded before sending it to a server. Only for type="submit" (e.g. "application/x-www-form-urlencoded", "multipart/form-data" or "text/plain")');
		$this->registerTagAttribute('formmethod', 'string', 'Specifies how to send the form-data (which HTTP method to use). Only for type="submit" (e.g. "get" or "post")');
		$this->registerTagAttribute('formnovalidate', 'string', 'Specifies that the form-data should not be validated on submission. Only for type="submit"');
		$this->registerTagAttribute('formtarget', 'string', 'Specifies where to display the response after submitting the form. Only for type="submit" (e.g. "_blank", "_self", "_parent", "_top", "framename")');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the button.
	 *
	 * @param string $type Specifies the type of button (e.g. "button", "reset" or "submit")
	 * @return string
	 * @api
	 */
	public function render($type = 'submit') {
		$name = $this->getName();
		$this->registerFieldNameForFormTokenGeneration($name);

		$this->tag->addAttribute('type', $type);
		$this->tag->addAttribute('name', $name);
		$this->tag->addAttribute('value', $this->getValue());
		$this->tag->setContent($this->renderChildren());

		return $this->tag->render();
	}
}

?>