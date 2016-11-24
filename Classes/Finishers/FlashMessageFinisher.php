<?php
namespace Neos\Form\Finishers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Error\Message;

/**
 * A simple finisher that adds a message to the FlashMessageContainer
 *
 * Usage:
 * //...
 * $flashMessageFinisher = new \Neos\Form\Finishers\FlashMessageFinisher();
 * $flashMessageFinisher->setOptions(
 *   array(
 *     'messageBody' => 'Some message body',
 *     'messageTitle' => 'Some message title',
 *     'messageArguments' => array('foo' => 'bar'),
 *     'severity' => \Neos\Flow\Error\Message::SEVERITY_ERROR
 *   )
 * );
 * $formDefinition->addFinisher($flashMessageFinisher);
 * // ...
 */
class FlashMessageFinisher extends \Neos\Form\Core\Model\AbstractFinisher
{
    /**
     * @Flow\Inject
     * @var \Neos\Flow\Mvc\FlashMessageContainer
     */
    protected $flashMessageContainer;

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'messageBody' => null,
        'messageTitle' => '',
        'messageArguments' => array(),
        'messageCode' => null,
        'severity' => Message::SEVERITY_OK,
    );

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws \Neos\Form\Exception\FinisherException
     */
    protected function executeInternal()
    {
        $messageBody = $this->parseOption('messageBody');
        if (!is_string($messageBody)) {
            throw new \Neos\Form\Exception\FinisherException(sprintf('The message body must be of type string, "%s" given.', gettype($messageBody)), 1335980069);
        }
        $messageTitle = $this->parseOption('messageTitle');
        $messageArguments = $this->parseOption('messageArguments');
        $messageCode = $this->parseOption('messageCode');
        $severity = $this->parseOption('severity');
        switch ($severity) {
            case Message::SEVERITY_NOTICE:
                $message = new \Neos\Flow\Error\Notice($messageBody, $messageCode, $messageArguments, $messageTitle);
                break;
            case Message::SEVERITY_WARNING:
                $message = new \Neos\Flow\Error\Warning($messageBody, $messageCode, $messageArguments, $messageTitle);
                break;
            case Message::SEVERITY_ERROR:
                $message = new \Neos\Flow\Error\Error($messageBody, $messageCode, $messageArguments, $messageTitle);
                break;
            default:
                $message = new Message($messageBody, $messageCode, $messageArguments, $messageTitle);
                break;
        }
        $this->flashMessageContainer->addMessage($message);
    }
}
