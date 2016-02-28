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
use TYPO3\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use TYPO3\Flow\I18n\Locale;
use TYPO3\Flow\I18n\Translator;
use TYPO3\Form\Core\Model\AbstractFinisher;
use TYPO3\Form\Exception\FinisherException;

/**
 * A simple finisher that outputs a given text
 *
 * Options:
 *
 * - translation.id: If specified, a localized message with the given label-id will be returned (@see Translator::translateById())
 * - translation.locale: Locale identifier. Defaults to the system default locale (only in conjunction with "translation.id")
 * - translation.source: Name of file with translations, defaults to "Main" (only in conjunction with "translation.id")
 * - translation.package: Key of the translation package, defaults to the "translationPackage" rendering option for the current form (only in conjunction with "translation.id")
 * - message: A hard-coded message to be rendered
 *
 * Usage:
 * //...
 * $confirmationFinisher = new \TYPO3\Form\Finishers\ConfirmationFinisher();
 * $confirmationFinisher->setOptions(
 *   array(
 *     'translation.id' => 'contactForm.confirmation',
 *     'translation.source' => 'Forms',
 *   )
 * );
 * $formDefinition->addFinisher($confirmationFinisher);
 * // ...
 */
class ConfirmationFinisher extends AbstractFinisher
{
    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'translation.id' => null,
        'translation.locale' => null,
        'translation.source' => 'Main',
        'translation.package' => null,
        'message' => '<p>The form has been submitted.</p>',
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
        $formRuntime = $this->finisherContext->getFormRuntime();

        $labelId = $this->parseOption('translation.id');
        if ($labelId !== null) {
            $locale = null;
            $localeIdentifier = $this->parseOption('translation.locale');
            if ($localeIdentifier !== null) {
                try {
                    $locale = new Locale($localeIdentifier);
                } catch (InvalidLocaleIdentifierException $exception) {
                    throw new FinisherException(sprintf('"%s" is not a valid locale identifier.', $locale), 1421325113, $exception);
                }
            }
            $messagePackageKey = $this->parseOption('translation.package');
            if ($messagePackageKey === null) {
                $renderingOptions = $formRuntime->getRenderingOptions();
                $messagePackageKey = $renderingOptions['translationPackage'];
            }
            $message = $this->translator->translateById($labelId, array(), null, $locale, $this->parseOption('translation.source'), $messagePackageKey);
        } else {
            $message = $this->parseOption('message');
        }

        $formRuntime->getResponse()->setContent($message);
    }
}
