<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Form\Core\Model\FinisherContext;

/**
 * Finisher that can be attached to a form in order to be invoked
 * as soon as the complete form is submitted
 */
interface FinisherInterface {

	/**
	 * Executes the finisher
	 *
	 * @param \TYPO3\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
	 * @return void
	 * @api
	 */
	public function execute(FinisherContext $finisherContext);

	/**
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options);

}
?>