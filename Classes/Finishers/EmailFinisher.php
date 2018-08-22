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

use Neos\Flow\I18n\Service;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;
use Neos\FluidAdaptor\Exception;
use Neos\SwiftMailer\Message as SwiftMailerMessage;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Annotations as Flow;

/**
 * This finisher sends an email to one or more recipients
 *
 * Options:
 *
 * - templatePathAndFilename (mandatory if "templateSource" option is not set): Template path and filename for the mail body
 * - templateSource (mandatory if "templatePathAndFilename" option is not set): The raw Fluid template
 * - layoutRootPath: root path for the layouts
 * - partialRootPath: root path for the partials
 * - variables: associative array of variables which are available inside the Fluid template
 *
 * - referrer: The referrer of the form is available in the Fluid template
 *
 * The following options control the mail sending. In all of them, placeholders in the form
 * of {...} are replaced with the corresponding form value; i.e. {email} as recipientAddress
 * makes the recipient address configurable.
 *
 * - subject (mandatory): Subject of the email
 * - recipientAddress (mandatory): Email address of the recipient (use multiple addresses with an array)
 * - recipientName: Human-readable name of the recipient
 * - senderAddress (mandatory): Email address of the sender
 * - senderName: Human-readable name of the sender
 * - replyToAddress: Email address of to be used as reply-to email (use multiple addresses with an array)
 * - carbonCopyAddress: Email address of the copy recipient (use multiple addresses with an array)
 * - blindCarbonCopyAddress: Email address of the blind copy recipient (use multiple addresses with an array)
 * - format: format of the email (one of the FORMAT_* constants). By default mails are sent as HTML
 * - attachAllPersistentResources: if TRUE all FormElements that are converted to a PersistendResource (e.g. the FileUpload element) are added to the mail as attachments
 * - attachments: array of explicit files to be attached. Every item in the array has to be either "resource" being the path to a file, or "formElement" referring to the identifier of an Form Element that contains the PersistentResource to attach. This can be combined with the "attachAllPersistentResources" option
 * - testMode: if TRUE the email is not actually sent but outputted for debugging purposes. Defaults to FALSE
 */
class EmailFinisher extends AbstractFinisher
{
    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';
    
    /**
     * @var Service
     * @Flow\Inject
     */
    protected $i18nService;

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'recipientName' => '',
        'senderName' => '',
        'format' => self::FORMAT_HTML,
        'attachAllPersistentResources' => false,
        'attachments' => [],
        'testMode' => false,
    );

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws Exception
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        if (!class_exists(SwiftMailerMessage::class)) {
            throw new FinisherException('The "neos/swiftmailer" doesn\'t seem to be installed, but is required for the EmailFinisher to work!', 1503392532);
        }

        $subject = $this->getSubject();
        $recipientAddress = $this->getRecipientAddress();
        $recipientName = $this->getRecipientName();
        $senderAddress = $this->getSenderAddress();
        $senderName = $this->getSenderName();
        $replyToAddress = $this->getReplyToAddress();
        $carbonCopyAddress = $this->getCarbonCopyAddress();
        $blindCarbonCopyAddress = $this->getBlindCarbonCopyAddress();

        if ($subject === null) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
        }
        if (is_array($recipientAddress) && $recipientName !== null) {
            throw new FinisherException('The option "recipientName" cannot be used with multiple recipients in the EmailFinisher.', 1483365977);
        }
        if ($senderAddress === null) {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $this->sendMail($subject, $senderAddress, $senderName, $recipientAddress, $recipientName, $replyToAddress, $carbonCopyAddress, $blindCarbonCopyAddress);
    }

    /**
     * @return string
     * @throws Exception
     * @throws FinisherException
     */
    protected function getMailTemplate()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assign('form', $formRuntime);
        $referrer = $formRuntime->getRequest()->getHttpRequest()->getUri();
        $standaloneView->assign('referrer', $referrer);
        return $standaloneView->render();
    }

    /**
     * @return string
     */
    protected function getSubject()
    {
        return $this->parseOption('subject');
    }

    /**
     * @return string
     */
    protected function getRecipientAddress()
    {
        return $this->parseOption('recipientAddress');
    }

    /**
     * @return string
     */
    protected function getRecipientName()
    {
        return $this->parseOption('recipientName');
    }

    /**
     * @return string
     */
    protected function getSenderAddress()
    {
        return $this->parseOption('senderAddress');
    }

    /**
     * @return string
     */
    protected function getSenderName()
    {
        return $this->parseOption('senderName');
    }

    /**
     * @return string
     */
    protected function getReplyToAddress()
    {
        return $this->parseOption('replyToAddress');
    }

    /**
     * @return string
     */
    protected function getCarbonCopyAddress()
    {
        return $this->parseOption('carbonCopyAddress');
    }

    /**
     * @return string
     */
    protected function getBlindCarbonCopyAddress()
    {
        return $this->parseOption('blindCarbonCopyAddress');
    }

    /**
     * @param string $subject
     * @param string $senderAddress
     * @param string $senderName
     * @param array|string $recipientAddress
     * @param string $recipientName
     * @param string $replyToAddress
     * @param string $carbonCopyAddress
     * @param string $blindCarbonCopyAddress
     * @throws Exception
     * @throws FinisherException
     */
    protected function sendMail($subject, $senderAddress, $senderName, $recipientAddress, $recipientName, $replyToAddress, $carbonCopyAddress, $blindCarbonCopyAddress)
    {
        $format = $this->parseOption('format');
        $message = $this->getMailTemplate();

        $mail = new SwiftMailerMessage();

        $mail
            ->setFrom(array($senderAddress => $senderName))
            ->setSubject($subject);

        if (is_array($recipientAddress)) {
            $mail->setTo($recipientAddress);
        } else {
            $mail->setTo(array($recipientAddress => $recipientName));
        }

        if ($replyToAddress !== null) {
            $mail->setReplyTo($replyToAddress);
        }

        if ($carbonCopyAddress !== null) {
            $mail->setCc($carbonCopyAddress);
        }

        if ($blindCarbonCopyAddress !== null) {
            $mail->setBcc($blindCarbonCopyAddress);
        }

        if ($format === self::FORMAT_PLAINTEXT) {
            $mail->setBody($message, 'text/plain');
        } else {
            $mail->setBody($message, 'text/html');
        }
        $this->addAttachments($mail);

        if ($this->parseOption('testMode') === true) {
            exit(
                \Neos\Flow\var_dump([
                    'sender' => [$senderAddress => $senderName],
                    'recipients' => is_array($recipientAddress) ? $recipientAddress : [$recipientAddress => $recipientName],
                    'replyToAddress' => $replyToAddress,
                    'carbonCopyAddress' => $carbonCopyAddress,
                    'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
                    'format' => $format
                ], 'E-Mail "' . $subject . '"', true).
                $message
            );
        } else {
            $mail->send();
        }
    }

    /**
     * @return StandaloneView
     * @throws FinisherException
     * @throws Exception
     */
    protected function initializeStandaloneView()
    {
        $standaloneView = new StandaloneView();
        if (isset($this->options['templatePathAndFilename'])) {
            $templatePathAndFilename = $this->i18nService->getLocalizedFilename($this->options['templatePathAndFilename']);
            $standaloneView->setTemplatePathAndFilename($templatePathAndFilename[0]);
        } elseif (isset($this->options['templateSource'])) {
            $standaloneView->setTemplateSource($this->options['templateSource']);
        } else {
            throw new FinisherException('The option "templatePathAndFilename" or "templateSource" must be set for the EmailFinisher.', 1327058829);
        }


        if (isset($this->options['partialRootPath'])) {
            $standaloneView->setPartialRootPath($this->options['partialRootPath']);
        }

        if (isset($this->options['layoutRootPath'])) {
            $standaloneView->setLayoutRootPath($this->options['layoutRootPath']);
        }

        $standaloneView->assign('formValues', $this->finisherContext->getFormValues());

        if (isset($this->options['variables'])) {
            $standaloneView->assignMultiple($this->options['variables']);
        }
        return $standaloneView;
    }

    /**
     * @param SwiftMailerMessage $mail
     * @return void
     * @throws FinisherException
     */
    protected function addAttachments(SwiftMailerMessage $mail)
    {
        $formValues = $this->finisherContext->getFormValues();
        if ($this->parseOption('attachAllPersistentResources')) {
            foreach ($formValues as $formValue) {
                if ($formValue instanceof PersistentResource) {
                    $mail->attach(\Swift_Attachment::newInstance(stream_get_contents($formValue->getStream()), $formValue->getFilename(), $formValue->getMediaType()));
                }
            }
        }
        $attachmentConfigurations = $this->parseOption('attachments');
        if (is_array($attachmentConfigurations)) {
            foreach ($attachmentConfigurations as $attachmentConfiguration) {
                if (isset($attachmentConfiguration['resource'])) {
                    $mail->attach(\Swift_Attachment::fromPath($attachmentConfiguration['resource']));
                    continue;
                }
                if (!isset($attachmentConfiguration['formElement'])) {
                    throw new FinisherException('The "attachments" options need to specify a "resource" path or a "formElement" containing the resource to attach', 1503396636);
                }
                $resource = ObjectAccess::getPropertyPath($formValues, $attachmentConfiguration['formElement']);
                if (!$resource instanceof PersistentResource) {
                    continue;
                }
                $mail->attach(\Swift_Attachment::newInstance(stream_get_contents($resource->getStream()), $resource->getFilename(), $resource->getMediaType()));
            }
        }
    }
}
