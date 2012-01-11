<?php
namespace TYPO3\Form\Core\Runtime;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

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
	protected $formValues;

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
	 * @param string $key
	 * @param mixed $value
	 */
	public function setFormValue($key, $value) {
		$this->formValues[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getFormValue($key) {
		return isset($this->formValues[$key]) ? $this->formValues[$key] : NULL;
	}
}
?>