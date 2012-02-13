<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Form\Core\Runtime\FormRuntime;

/**
 * The context that is passed to each finisher when executed.
 * It acts like an EventObject that is able to stop propagation.
 */
class FinisherContext {

	/**
	 * If TRUE further finishers won't be invoked
	 *
	 * @var boolean
	 */
	protected $cancelled = FALSE;

	/**
	 * A reference to the Form Runtime that the finisher belongs to
	 *
	 * @var \TYPO3\Form\Core\Runtime\FormRuntime
	 */
	protected $formRuntime;

	/**
	 * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
	 */
	public function __construct(FormRuntime $formRuntime) {
		$this->formRuntime = $formRuntime;
	}

	/**
	 * Cancels the finisher invocation after the current finisher
	 *
	 * @return void
	 */
	public function cancel() {
		$this->cancelled = TRUE;
	}

	/**
	 * TRUE if no futher finishers should be invoked. Defaults to FALSE
	 *
	 * @return boolean
	 */
	public function isCancelled() {
		return $this->cancelled;
	}

	/**
	 * The Form Runtime that is associated with the current finisher
	 *
	 * @return \TYPO3\Form\Core\Runtime\FormRuntime
	 */
	public function getFormRuntime() {
		return $this->formRuntime;
	}
}
?>