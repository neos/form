<?php

namespace Neos\Form\ViewHelpers;

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
use Neos\Flow\I18n\Translator;
use Neos\Flow\ResourceManagement\Exception as ResourceException;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Form\Core\Model\FormElementInterface;

/**
 * ViewHelper to translate the property of a given form element based on its rendering options.
 *
 * = Examples =
 *
 * <code>
 * {element -> form:translateElementProperty(property: 'placeholder')}
 * </code>
 * <output>
 *  the translated placeholder, or the actual "placeholder" property if no translation was found
 * </output>
 */
class TranslateElementPropertyViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     *
     * @var Translator
     */
    protected $translator;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @param string               $property
     * @param FormElementInterface $element
     *
     * @return string the rendered form head
     */
    public function render($property, FormElementInterface $element = null)
    {
        if ($element === null) {
            $element = $this->renderChildren();
        }
        if ($property === 'label') {
            $defaultValue = $element->getLabel();
        } else {
            $defaultValue = isset($element->getProperties()[$property]) ? (string) $element->getProperties()[$property] : '';
        }
        $renderingOptions = $element->getRenderingOptions();
        if (!isset($renderingOptions['translationPackage'])) {
            return $defaultValue;
        }
        $translationId = sprintf('forms.elements.%s.%s', $element->getIdentifier(), $property);
        try {
            $translation = $this->translator->translateById($translationId, [], null, null, 'Main', $renderingOptions['translationPackage']);
        } catch (ResourceException $exception) {
            return $defaultValue;
        }

        return $translation === null ? $defaultValue : $translation;
    }
}
