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

/**
 * This finisher sends an email to one recipient
 *
 * Options:
 *
 * - templatePathAndFilename (mandatory): Template path and filename for the mail body
 * - layoutRootPath: root path for the layouts
 * - partialRootPath: root path for the partials
 * - variables: associative array of variables which are available inside the Fluid template
 *
 * The following options control the mail sending. In all of them, placeholders in the form
 * of {...} are replaced with the corresponding form value; i.e. {email} as recipientAddress
 * makes the recipient address configurable.
 *
 * - subject (mandatory): Subject of the email
 * - recipientAddress (mandatory): Email address of the recipient
 * - recipientName: Human-readable name of the recipient
 * - senderAddress (mandatory): Email address of the sender
 * - senderName: Human-readable name of the sender
 * - replyToAddress: Email address of to be used as reply-to email
 * - format: format of the email (one of the FORMAT_* constants). By default mails are sent as HTML
 * - testMode: if TRUE the email is not actually sent but outputted for debugging purposes. Defaults to FALSE
 */
class EmailFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher {

	const FORMAT_PLAINTEXT = 'plaintext';
	const FORMAT_HTML = 'html';

	/**
	 * @var array
	 */
	protected $defaultOptions = array(
		'recipientName' => '',
		'senderName' => '',
		'format' => self::FORMAT_HTML,
		'testMode' => FALSE,
	);

	/**
	 * Executes this finisher
	 * @see AbstractFinisher::execute()
	 *
	 * @return void
	 * @throws \TYPO3\Form\Exception\FinisherException
	 */
	protected function executeInternal() {
		$formRuntime = $this->finisherContext->getFormRuntime();
		$standaloneView = $this->initializeStandaloneView();
		$standaloneView->assign('form', $formRuntime);
		$message = $standaloneView->render();

		$subject = $this->parseOption('subject');
		$recipientAddress = $this->parseOption('recipientAddress');
		$recipientName = $this->parseOption('recipientName');
		$senderAddress = $this->parseOption('senderAddress');
		$senderName = $this->parseOption('senderName');
		$replyToAddress = $this->parseOption('replyToAddress');
		$format = $this->parseOption('format');
		$testMode = $this->parseOption('testMode');

		if ($subject === NULL) {
			throw new \TYPO3\Form\Exception\FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
		}
		if ($recipientAddress === NULL) {
			throw new \TYPO3\Form\Exception\FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
		}
		if ($senderAddress === NULL) {
			throw new \TYPO3\Form\Exception\FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
		}

		$mail = new \TYPO3\SwiftMailer\Message();

		$mail
			->setFrom(array($senderAddress => $senderName))
			->setTo(array($recipientAddress => $recipientName))
			->setSubject($subject);

		if ($replyToAddress !== NULL) {
			$mail->setReplyTo($replyToAddress);
		}

		if ($format === self::FORMAT_PLAINTEXT) {
			$mail->setBody($message, 'text/plain');
		} else {
			$mail->setBody($message, 'text/html');
		}

		if ($testMode === TRUE) {
			\TYPO3\Flow\var_dump(
				array(
					'sender' => array($senderAddress => $senderName),
					'recipient' => array($recipientAddress => $recipientName),
					'replyToAddress' => $replyToAddress,
					'message' => $message,
					'format' => $format,
				),
				'E-Mail "' . $subject . '"'
			);
		} else {
			$mail->send();
		}
	}

	/**
	 * @return \TYPO3\Fluid\View\StandaloneView
	 * @throws \TYPO3\Form\Exception\FinisherException
	 */
	protected function initializeStandaloneView() {
		$standaloneView = new \TYPO3\Fluid\View\StandaloneView();
		if (!isset($this->options['templatePathAndFilename'])) {
			throw new \TYPO3\Form\Exception\FinisherException('The option "templatePathAndFilename" must be set for the EmailFinisher.', 1327058829);
		}
		$standaloneView->setTemplatePathAndFilename($this->options['templatePathAndFilename']);

		if (isset($this->options['partialRootPath'])) {
			$standaloneView->setPartialRootPath($this->options['partialRootPath']);
		}

		if (isset($this->options['layoutRootPath'])) {
			$standaloneView->setLayoutRootPath($this->options['layoutRootPath']);
		}

		if (isset($this->options['variables'])) {
			$standaloneView->assignMultiple($this->options['variables']);
		}
		return $standaloneView;
	}
}
