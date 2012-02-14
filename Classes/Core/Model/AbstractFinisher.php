<?php
namespace TYPO3\Form\Core\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Form\Core\Model\FinisherContext;

/**
 * Finisher base class.
 *
 * **This class is meant to be subclassed by developers**
 */
abstract class AbstractFinisher implements \TYPO3\Form\Core\Model\FinisherInterface {

	/**
	 * The options which have been set from the outside. Instead of directly
	 * accessing them, you should rather use parseOption().
	 *
	 * @var array
	 * @internal
	 */
	protected $options = array();

	/**
	 * These are the default options of the finisher.
	 * Override them in your concrete implementation.
	 * Default options should not be changed from "outside"
	 *
	 * @var array
	 * @api
	 */
	protected $defaultOptions = array();

	/**
	 * @var \TYPO3\Form\Core\Model\FinisherContext
	 * @api
	 */
	protected $finisherContext;

	public function setOptions(array $options) {
		$this->options = $options;
	}

	final public function execute(FinisherContext $finisherContext) {
		$this->finisherContext = $finisherContext;
		$this->executeInternal();
	}

	/**
	 * This method is called in the concrete finisher whenever self::execute() is called.
	 *
	 * Override and fill with your own implementation!
	 *
	 * @return void
	 * @api
	 */
	abstract protected function executeInternal();

	/**
	 * Read the option called $optionName from $this->options, and parse {...}
	 * as object accessors.
	 *
	 * if $optionName was not found, the corresponding default option is returned (from $this->defaultOptions)
	 *
	 * @param string $optionName
	 * @return mixed
	 * @api
	 */
	protected function parseOption($optionName) {
		if (!isset($this->options[$optionName]) || $this->options[$optionName] === '') {
			if (isset($this->defaultOptions[$optionName])) {
				$option = $this->defaultOptions[$optionName];
			} else {
				return NULL;
			}
		} else {
			$option = $this->options[$optionName];
		}
		if (!is_string($option)) {
			return $option;
		}
		$formRuntime = $this->finisherContext->getFormRuntime();
		return preg_replace_callback('/{([^}]+)}/', function($match) use ($formRuntime) {
			return \TYPO3\FLOW3\Reflection\ObjectAccess::getPropertyPath($formRuntime, $match[1]);
		}, $option);
	}


}
?>