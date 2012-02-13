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
		'message' => 'The form has been submitted.'
	);

	protected function executeInternal() {
		$response = $this->finisherContext->getResponse();
		$response->setContent($this->parseOption('message'));
	}
}
?>