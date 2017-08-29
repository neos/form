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
use Neos\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Translator;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;

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
 * $confirmationFinisher = new \Neos\Form\Finishers\ConfirmationFinisher();
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
            $message = $this->translator->translateById($labelId, [], null, $locale, $this->parseOption('translation.source'), $messagePackageKey);
        } else {
            $message = $this->parseOption('message');
        }

        $formRuntime->getResponse()->setContent($message);
    }
}
