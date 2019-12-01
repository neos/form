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

use Neos\Error\Messages\Error;
use Neos\Error\Messages\Message;
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Warning;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Exception\InvalidFlashMessageConfigurationException;
use Neos\Flow\Mvc\FlashMessage\FlashMessageService;
use Neos\Flow\Security\Exception\InvalidRequestPatternException;
use Neos\Flow\Security\Exception\NoRequestPatternFoundException;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;

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
 *     'severity' => \Neos\Error\Messages\Message::SEVERITY_ERROR
 *   )
 * );
 * $formDefinition->addFinisher($flashMessageFinisher);
 * // ...
 */
class FlashMessageFinisher extends AbstractFinisher
{
    /**
     * @Flow\Inject
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * @var array
     */
    protected $defaultOptions = [
        'messageBody' => null,
        'messageTitle' => '',
        'messageArguments' => [],
        'messageCode' => null,
        'severity' => Message::SEVERITY_OK,
    ];

    /**
     * Executes this finisher
     *
     * @return void
     * @throws FinisherException
     * @throws InvalidFlashMessageConfigurationException
     * @throws InvalidRequestPatternException
     * @throws NoRequestPatternFoundException
     * @see AbstractFinisher::execute()
     *
     */
    protected function executeInternal()
    {
        $messageBody = $this->parseOption('messageBody');
        if (!is_string($messageBody)) {
            throw new FinisherException(sprintf('The message body must be of type string, "%s" given.', gettype($messageBody)), 1335980069);
        }
        $messageTitle = $this->parseOption('messageTitle') ?? '';
        $messageArguments = $this->parseOption('messageArguments');
        $messageCode = $this->parseOption('messageCode');
        $severity = $this->parseOption('severity');
        switch ($severity) {
            case Message::SEVERITY_NOTICE:
                $message = new Notice($messageBody, $messageCode, $messageArguments, $messageTitle);
            break;
            case Message::SEVERITY_WARNING:
                $message = new Warning($messageBody, $messageCode, $messageArguments, $messageTitle);
            break;
            case Message::SEVERITY_ERROR:
                $message = new Error($messageBody, $messageCode, $messageArguments, $messageTitle);
            break;
            default:
                $message = new Message($messageBody, $messageCode, $messageArguments, $messageTitle);
            break;
        }

        $formRuntime = $this->finisherContext->getFormRuntime();
        $request = $formRuntime->getRequest()->getMainRequest();
        $this->flashMessageService->getFlashMessageContainerForRequest($request)->addMessage($message);
    }
}
