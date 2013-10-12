<?php
namespace TYPO3\Form\Finishers;

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
use TYPO3\Flow\Error\Message;

/**
 * A simple finisher that adds a message to the FlashMessageContainer
 *
 * Usage:
 * //...
 * $flashMessageFinisher = new \TYPO3\Form\Finishers\FlashMessageFinisher();
 * $flashMessageFinisher->setOptions(
 *   array(
 *     'messageBody' => 'Some message body',
 *     'messageTitle' => 'Some message title',
 *     'messageArguments' => array('foo' => 'bar'),
 *     'severity' => \TYPO3\Flow\Error\Message::SEVERITY_ERROR
 *   )
 * );
 * $formDefinition->addFinisher($flashMessageFinisher);
 * // ...
 */
class FlashMessageFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	/**
	 * @var array
	 */
	protected $defaultOptions = array(
		'messageBody' => NULL,
		'messageTitle' => '',
		'messageArguments' => array(),
		'messageCode' => NULL,
		'severity' => Message::SEVERITY_OK,
	);

	/**
	 * Executes this finisher
	 * @see AbstractFinisher::execute()
	 *
	 * @return void
	 * @throws \TYPO3\Form\Exception\FinisherException
	 */
	protected function executeInternal() {
		$messageBody = $this->parseOption('messageBody');
		if (!is_string($messageBody)) {
			throw new \TYPO3\Form\Exception\FinisherException(sprintf('The message body must be of type string, "%s" given.', gettype($messageBody)), 1335980069);
		}
		$messageTitle = $this->parseOption('messageTitle');
		$messageArguments = $this->parseOption('messageArguments');
		$messageCode = $this->parseOption('messageCode');
		$severity = $this->parseOption('severity');
		switch ($severity) {
			case Message::SEVERITY_NOTICE:
				$message = new \TYPO3\Flow\Error\Notice($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			case Message::SEVERITY_WARNING:
				$message = new \TYPO3\Flow\Error\Warning($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			case Message::SEVERITY_ERROR:
				$message = new \TYPO3\Flow\Error\Error($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
			default:
				$message = new Message($messageBody, $messageCode, $messageArguments, $messageTitle);
				break;
		}
		$this->flashMessageContainer->addMessage($message);
	}
}
