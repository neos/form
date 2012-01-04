<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The form state
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * @todo greatly expand documentation
 */
class FormState {

	const NOPAGE = -1;

	protected $lastDisplayedPageIndex = self::NOPAGE;

	/**
	 * @var array
	 */
	protected $formValues;

	public function isFormSubmitted() {
		return ($this->lastDisplayedPageIndex !== self::NOPAGE);
	}

	public function getLastDisplayedPageIndex() {
		return $this->lastDisplayedPageIndex;
	}

	public function setLastDisplayedPageIndex($lastDisplayedPageIndex) {
		$this->lastDisplayedPageIndex = $lastDisplayedPageIndex;
	}

	public function getFormValues() {
		return $this->formValues;
	}

	public function setFormValue($key, $value) {
		$this->formValues[$key] = $value;
	}

	public function getFormValue($key) {
		return isset($this->formValues[$key]) ? $this->formValues[$key] : NULL;
	}
}
?>