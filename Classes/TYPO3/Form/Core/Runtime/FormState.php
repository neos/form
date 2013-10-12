<?php
namespace TYPO3\Form\Core\Runtime;

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
 * The current state of the form which is attached to the {@link FormRuntime}
 * and saved in a session or the client.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * @internal
 */
class FormState {

	/**
	 * Constant which means that we are currently not on any page; i.e. the form
	 * has never rendered before.
	 */
	const NOPAGE = -1;

	/**
	 * The last displayed page index
	 *
	 * @var integer
	 */
	protected $lastDisplayedPageIndex = self::NOPAGE;

	/**
	 * @var array
	 */
	protected $formValues = array();

	/**
	 * @return boolean FALSE if the form has never been submitted before, TRUE otherwise
	 */
	public function isFormSubmitted() {
		return ($this->lastDisplayedPageIndex !== self::NOPAGE);
	}

	/**
	 * @return integer
	 */
	public function getLastDisplayedPageIndex() {
		return $this->lastDisplayedPageIndex;
	}

	/**
	 * @param integer $lastDisplayedPageIndex
	 * @return void
	 */
	public function setLastDisplayedPageIndex($lastDisplayedPageIndex) {
		$this->lastDisplayedPageIndex = $lastDisplayedPageIndex;
	}

	/**
	 * @return array
	 */
	public function getFormValues() {
		return $this->formValues;
	}

	/**
	 * @param string $propertyPath
	 * @param mixed $value
	 * @return void
	 */
	public function setFormValue($propertyPath, $value) {
		$this->formValues = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->formValues, $propertyPath, $value);
	}

	/**
	 * @param string $propertyPath
	 * @return mixed
	 */
	public function getFormValue($propertyPath) {
		return \TYPO3\Flow\Utility\Arrays::getValueByPath($this->formValues, $propertyPath);
	}
}
