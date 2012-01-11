<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use \TYPO3\Form\Core\Runtime\FormRuntime;

/**
 * Finisher that can be attached to a form in order to be invoked
 * as soon as the complete form is submitted
 *
 * **This API will still improve in milestone P1.3. (Rendering, Finisher)**
 */
interface FinisherInterface {

	/**
	 * Executes the finisher
	 *
	 * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime The Form runtime that invokes this finisher
	 * @return boolean TRUE by default, FALSE if invocation chain should be canceled
	 * @api
	 */
	public function execute(FormRuntime $formRuntime);

	/**
	 * @param array $options configuration options for this finisher
	 * @return void
	 * @api
	 */
	public function setOptions(array $options);

}
?>