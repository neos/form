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
use Neos\SwiftMailer\Message as SwiftMailerMessage;
use Neos\Utility\Arrays;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Annotations as Flow;

/**
 * This finisher sends an email to one or more recipients
 *
 * Options:
 *
 * - templatePathAndFilename (mandatory if "templateSource" option is not set): Template path and filename for the mail body
 * - templateSource (mandatory if "templatePathAndFilename" option is not set): The raw Fluid template
 * - htmlTemplatePathAndFilename (mandatory if "htmlTemplateSource" option is not set): Template path and filename for the html mail body
 * - htmlTemplateSource (mandatory if "htmlTemplatePathAndFilename" option is not set): The raw Fluid template for html mail body
 * - plaintextTemplatePathAndFilename (mandatory if "plaintextTemplateSource" option is not set): Template path and filename for the plaintext mail body
 * - plaintextTemplateSource (mandatory if "plaintextTemplatePathAndFilename" option is not set): The raw Fluid template for plaintext mail body
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
 * - attachAllPersistentResources: if TRUE all FormElements that are converted to a PersistentResource (e.g. the FileUpload element) are added to the mail as attachments
 * - attachments: array of explicit files to be attached. Every item in the array has to be either "resource" being the path to a file, or "formElement" referring to the identifier of an Form Element that contains the PersistentResource to attach. This can be combined with the "attachAllPersistentResources" option
 * - testMode: if TRUE the email is not actually sent but outputted for debugging purposes. Defaults to FALSE
 */
class EmailFinisher extends AbstractFinisher
{
    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';
    const FORMAT_MULTIPART = 'multipart';
    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @var Service
     * @Flow\Inject
     */
    protected $i18nService;

    protected $formatContentTypes = [
        self::FORMAT_HTML => self::CONTENT_TYPE_HTML,
        self::FORMAT_PLAINTEXT => self::CONTENT_TYPE_PLAINTEXT,
    ];

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
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        if (!class_exists(SwiftMailerMessage::class)) {
            throw new FinisherException('The "neos/swiftmailer" doesn\'t seem to be installed, but is required for the EmailFinisher to work!', 1503392532);
        }
        $subject = $this->parseOption('subject');
        $recipientAddress = $this->parseOption('recipientAddress');
        $recipientName = $this->parseOption('recipientName');
        $senderAddress = $this->parseOption('senderAddress');
        $senderName = $this->parseOption('senderName');
        $replyToAddress = $this->parseOption('replyToAddress');
        $carbonCopyAddress = $this->parseOption('carbonCopyAddress');
        $blindCarbonCopyAddress = $this->parseOption('blindCarbonCopyAddress');
        $format = $this->parseOption('format');
        $testMode = $this->parseOption('testMode');
        $messages = $this->getMessages($format);

        if ($subject === null) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
        }
        if (is_array($recipientAddress) && !empty($recipientName)) {
            throw new FinisherException('The option "recipientName" cannot be used with multiple recipients in the EmailFinisher.', 1483365977);
        }
        if ($senderAddress === null) {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

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

        $this->addMessages($mail, $messages);
        $this->addAttachments($mail);

        if ($testMode === true) {
            \Neos\Flow\var_dump(
                array(
                    'sender' => array($senderAddress => $senderName),
                    'recipients' => is_array($recipientAddress) ? $recipientAddress : array($recipientAddress => $recipientName),
                    'replyToAddress' => $replyToAddress,
                    'carbonCopyAddress' => $carbonCopyAddress,
                    'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
                    'message' => $messages,
                    'format' => $format,
                ),
                'E-Mail "' . $subject . '"'
            );
        } else {
            $mail->send();
        }
    }

    protected function addMessages(SwiftMailerMessage $mail, array $messages): void
    {
        foreach ($messages as $messageFormat => $message) {
            if (count($messages) === 1) {
                $mail->setBody($message, $this->formatContentTypes[$messageFormat]);
            } else {
                $mail->addPart($message, $this->formatContentTypes[$messageFormat]);
            }
        }
    }

    protected function getMessages(string $format): array
    {
        $messages = [];
        if ($format === self::FORMAT_MULTIPART) {
            $messages[self::FORMAT_HTML] = $this->createMessage(self::FORMAT_HTML);
            $messages[self::FORMAT_PLAINTEXT] = $this->createMessage(self::FORMAT_PLAINTEXT);
        } elseif ($format === self::FORMAT_PLAINTEXT) {
            $messages[self::FORMAT_PLAINTEXT] = $this->createMessage(self::FORMAT_PLAINTEXT);
        } else {
            $messages[self::FORMAT_HTML] = $this->createMessage(self::FORMAT_HTML);
        }

        return $messages;
    }

    protected function createMessage(string $format): string
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $standaloneView = $this->initializeStandaloneView($format);
        $standaloneView->assign('form', $formRuntime);
        $referrer = $formRuntime->getRequest()->getHttpRequest()->getUri();
        $standaloneView->assign('referrer', $referrer);

        return $standaloneView->render();
    }

    /**
     * @param string $format
     * @return StandaloneView
     * @throws FinisherException
     * @throws \Neos\FluidAdaptor\Exception
     */
    protected function initializeStandaloneView(string $format = ''): StandaloneView
    {
        $templatePathAndFilenameOption = 'templatePathAndFilename';
        $templateSourceOption = 'templateSource';
        $isSingleTemplate = isset($this->options[$templatePathAndFilenameOption]) || isset($this->options[$templateSourceOption]);

        if (!$isSingleTemplate && in_array($format, [self::FORMAT_PLAINTEXT, self::FORMAT_HTML])) {
            $templatePathAndFilenameOption = $format . ucfirst($templatePathAndFilenameOption);
            $templateSourceOption = $format . ucfirst($templateSourceOption);
        }

        $standaloneView = new StandaloneView();
        if (isset($this->options[$templatePathAndFilenameOption])) {
            $templatePathAndFilename = $this->i18nService->getLocalizedFilename($this->options[$templatePathAndFilenameOption]);
            $standaloneView->setTemplatePathAndFilename($templatePathAndFilename[0]);
        } elseif (isset($this->options[$templateSourceOption])) {
            $standaloneView->setTemplateSource($this->options[$templateSourceOption]);
        } else {
            $options = [
                'templatePathAndFilename',
                'templateSource',
                self::FORMAT_PLAINTEXT . 'TemplatePathAndFilename',
                self::FORMAT_PLAINTEXT . 'TemplateSource',
                self::FORMAT_HTML . 'TemplatePathAndFilename',
                self::FORMAT_HTML . 'TemplateSource'
            ];
            throw new FinisherException(sprintf('One of the option "%s" must be set for the EmailFinisher.', implode('", "', $options)), 1551371435);
        }


        if (isset($this->options['partialRootPath'])) {
            $standaloneView->setPartialRootPath($this->options['partialRootPath']);
        }

        if (isset($this->options['layoutRootPath'])) {
            $standaloneView->setLayoutRootPath($this->options['layoutRootPath']);
        }

        $variables = $this->finisherContext->getFormValues();
        // Backwards compatibility, see https://github.com/neos/form/issues/121
        $variables['formValues'] = $this->finisherContext->getFormValues();
        if (isset($this->options['variables'])) {
            $variables = Arrays::arrayMergeRecursiveOverrule($variables, $this->options['variables']);
        }
        $standaloneView->assignMultiple($variables);
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
                    $mail->attach(new \Swift_Attachment(stream_get_contents($formValue->getStream()), $formValue->getFilename(), $formValue->getMediaType()));
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
                $mail->attach(new \Swift_Attachment(stream_get_contents($resource->getStream()), $resource->getFilename(), $resource->getMediaType()));
            }
        }
    }
}
