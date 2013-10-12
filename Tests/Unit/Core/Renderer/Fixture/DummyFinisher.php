<?php
namespace TYPO3\Form\Tests\Unit\Core\Runtime\Renderer\Fixture;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Form\Core\Model\FinisherContext;

/**
* Dummy finisher for testing
*/
class DummyFinisher implements \TYPO3\Form\Core\Model\FinisherInterface {

	public $cb = NULL;

	/**
	 * Executes the finisher
	 *
	 * @param \TYPO3\Form\Core\Model\FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
	 * @return void
	 * @api
	 */
	public function execute(FinisherContext $finisherContext) {
		$cb = $this->cb;
		$cb($finisherContext);
	}

	/**
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options) {}

	/**
	 * Sets a single finisher option (@see setOptions())
	 *
	 * @param string $optionName name of the option to be set
	 * @param mixed $optionValue value of the option
	 * @return void
	 * @api
	 */
	public function setOption($optionName, $optionValue) {}
}
