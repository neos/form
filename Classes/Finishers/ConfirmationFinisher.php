<?php
namespace TYPO3\Form\Finishers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * A simple finisher that outputs a given text
 */
class ConfirmationFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	protected $defaultOptions = array(
		'message' => '<p>The form has been submitted.</p>'
	);

	protected function executeInternal() {
		$formRuntime = $this->finisherContext->getFormRuntime();
		$response = $formRuntime->getResponse();
		$response->setContent($this->parseOption('message'));
	}
}
?>