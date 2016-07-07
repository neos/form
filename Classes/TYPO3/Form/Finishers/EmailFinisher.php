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
use TYPO3\Form\Core\Model\AbstractFormElement;
use TYPO3\Form\Core\Model\Page;
use TYPO3\Form\Core\Runtime\FormRuntime;

/**
 * This finisher sends an email to one recipient
 *
 * Options:
 *
 * - templatePathAndFilename (mandatory): Template path and filename for the mail body
 * - layoutRootPath: root path for the layouts
 * - partialRootPath: root path for the partials
 * - variables: associative array of variables which are available inside the Fluid template
 * - translation.enabled:
 * - translation.locale: Locale identifier. Defaults to the system default locale (only in conjunction with "translation.enabled" being TRUE)
 * - translation.source: Name of file with translations, defaults to "Main" (only in conjunction with "translation.enabled" being TRUE)
 * - translation.package: Key of the translation package, defaults to the "translationPackage" rendering option for the current form (only in conjunction with "translation.enabled" being TRUE)
 *
 * The following options control the mail sending. In all of them, placeholders in the form
 * of {...} are replaced with the corresponding form value; i.e. {email} as recipientAddress
 * makes the recipient address configurable.
 *
 * - subject (mandatory): Subject of the email (translatable)
 * - recipientAddress (mandatory): Email address of the recipient
 * - recipientName: Human-readable name of the recipient
 * - senderAddress (mandatory): Email address of the sender
 * - senderName: Human-readable name of the sender
 * - replyToAddress: Email address of to be used as reply-to email (use multiple addresses with an array)
 * - carbonCopyAddress: Email address of the copy recipient (use multiple addresses with an array)
 * - blindCarbonCopyAddress: Email address of the blind copy recipient (use multiple addresses with an array)
 * - format: format of the email (one of the FORMAT_* constants). By default mails are sent as HTML
 * - testMode: if TRUE the email is not actually sent but outputted for debugging purposes. Defaults to FALSE
 */
class EmailFinisher extends \TYPO3\Form\Core\Model\AbstractFinisher
{
    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'recipientName' => '',
        'senderName' => '',
        'format' => self::FORMAT_HTML,
        'testMode' => false,
        'translation.enabled' => false,
        'translation.locale' => null,
        'translation.source' => 'Main',
        'translation.package' => null,
    );

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\I18n\Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();

        $this->translateElementOptions($formRuntime);

        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assignMultiple(array(
            'form' => $formRuntime,
            'translation' => array(
                'package' => $this->getTranslationPackage(),
                'locale' => $this->getTranslationLocale(),
                'source' => $this->parseOption('translation.source')
            )
        ));
        $message = $standaloneView->render();

        $subject = $this->translateOption('subject');
        $recipientAddress = $this->parseOption('recipientAddress');
        $recipientName = $this->parseOption('recipientName');
        $senderAddress = $this->parseOption('senderAddress');
        $senderName = $this->parseOption('senderName');
        $replyToAddress = $this->parseOption('replyToAddress');
        $carbonCopyAddress = $this->parseOption('carbonCopyAddress');
        $blindCarbonCopyAddress = $this->parseOption('blindCarbonCopyAddress');
        $format = $this->parseOption('format');
        $testMode = $this->parseOption('testMode');

        if ($subject === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
        }
        if ($senderAddress === null) {
            throw new \TYPO3\Form\Exception\FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $mail = new \TYPO3\SwiftMailer\Message();

        $mail
            ->setFrom(array($senderAddress => $senderName))
            ->setTo(array($recipientAddress => $recipientName))
            ->setSubject($subject);

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

        if ($testMode === true) {
            \TYPO3\Flow\var_dump(
                array(
                    'sender' => array($senderAddress => $senderName),
                    'recipient' => array($recipientAddress => $recipientName),
                    'replyToAddress' => $replyToAddress,
                    'carbonCopyAddress' => $carbonCopyAddress,
                    'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
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
    protected function initializeStandaloneView()
    {
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

    /**
     * Translate option values of form elements with options such as checkboxes, selectdropdowns, radiobuttons
     *
     * @param FormRuntime $formRuntime
     */
    protected function translateElementOptions(FormRuntime $formRuntime) {
        if ($this->parseOption('translation.enabled')) {
            /** @var Page $page */
            foreach ($formRuntime->getPages() as $page) {
                foreach ($page->getElementsRecursively() as $element) {
                    if (!$element instanceof AbstractFormElement) {
                        continue;
                    }

                    $properties = $element->getProperties();
                    if (!array_key_exists('options', $properties)) {
                        continue;
                    }

                    try {
                        $labelId = sprintf('forms.elements.%s.options.%s', $element->getIdentifier(), $formRuntime->getFormState()->getFormValue($element->getIdentifier()));
                        $optionValue = $this->translator->translateById($labelId, array(), null, $this->getTranslationLocale(), $this->parseOption('translation.source'), $this->getTranslationPackage());
                        $formRuntime->getFormState()->setFormValue($element->getIdentifier(), $optionValue);
                    } catch (\TYPO3\Flow\Resource\Exception $exception) {
                    }
                }
            }
        }
    }

    /**
     * Translate given finisher option such as subject
     *
     * @param string $optionName
     * @return mixed|string
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function translateOption($optionName) {
        if ($this->parseOption('translation.enabled')) {
            $labelId = sprintf('forms.emailFinisher.%s.%s', $this->getIdentifier(), $optionName);
            return $this->translator->translateById($labelId, array(), null, $this->getTranslationLocale(), $this->parseOption('translation.source'), $this->getTranslationPackage());
        } else {
            return $this->parseOption($optionName);
        }
    }

    /**
     * Get the identifier of finisher in finisher set
     *
     * @return integer
     */
    protected function getIdentifier() {
        if ($this->identifier === null) {
            foreach ($this->finisherContext->getFormRuntime()->getFormDefinition()->getFinishers() as $key => $finisher) {
                if ($finisher === $this) {
                    $this->identifier = $key;
                }
            }
        }

        return $this->identifier;
    }

    /**
     * @return array|mixed
     */
    protected function getTranslationPackage() {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $packageKey = $this->parseOption('translation.package');
        if ($packageKey === null) {
            $renderingOptions = $formRuntime->getRenderingOptions();
            $packageKey = $renderingOptions['translationPackage'];
        }
        return $packageKey;
    }

    /**
     * @return null|\TYPO3\Flow\I18n\Locale
     * @throws \TYPO3\Form\Exception\FinisherException
     */
    protected function getTranslationLocale() {
        $locale = null;
        $localeIdentifier = $this->parseOption('translation.locale');
        if ($localeIdentifier !== null) {
            try {
                $locale = new \TYPO3\Flow\I18n\Locale($localeIdentifier);
            } catch (\TYPO3\Flow\I18n\Exception\InvalidLocaleIdentifierException $exception) {
                throw new \TYPO3\Form\Exception\FinisherException(sprintf('"%s" is not a valid locale identifier.', $locale), 1467888923, $exception);
            }
        }
        return $locale;
    }
}
