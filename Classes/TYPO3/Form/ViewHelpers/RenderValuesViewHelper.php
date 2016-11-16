<?php
namespace TYPO3\Form\ViewHelpers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Form\Core\Model\FormElementInterface;
use TYPO3\Form\Core\Model\Renderable\CompositeRenderableInterface;
use TYPO3\Form\Core\Model\Renderable\RootRenderableInterface;
use TYPO3\Form\Core\Renderer\RendererInterface;
use TYPO3\Media\Domain\Model\Image;

/**
 * Renders the values of a form
 */
class RenderValuesViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @param RootRenderableInterface $renderable
     * @param string $as
     * @return string the rendered form values
     */
    public function render(RootRenderableInterface $renderable, $as = 'formValue')
    {
        if ($renderable instanceof CompositeRenderableInterface) {
            $elements = $renderable->getRenderablesRecursively();
        } else {
            $elements = array($renderable);
        }

        /** @var RendererInterface $fluidFormRenderer */
        $fluidFormRenderer = $this->viewHelperVariableContainer->getView();
        $formRuntime = $fluidFormRenderer->getFormRuntime();
        $formState = $formRuntime->getFormState();
        $output = '';
        foreach ($elements as $element) {
            if (!$element instanceof FormElementInterface) {
                continue;
            }
            $value = $formState->getFormValue($element->getIdentifier());

            $formValue = array(
                'element' => $element,
                'value' => $value,
                'processedValue' => $this->processElementValue($element, $value),
                'isMultiValue' => is_array($value) || $value instanceof \Iterator
            );
            $this->templateVariableContainer->add($as, $formValue);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove($as);
        }
        return $output;
    }

    /**
     * Converts the given value to a simple type (string or array) considering the underlying FormElement definition
     *
     * @param FormElementInterface $element
     * @param mixed $value
     * @return string
     */
    protected function processElementValue(FormElementInterface $element, $value)
    {
        $properties = $element->getProperties();
        if (isset($properties['options']) && is_array($properties['options'])) {
            if (is_array($value)) {
                return $this->mapValuesToOptions($value, $properties['options']);
            } else {
                return $this->mapValueToOption($value, $properties['options']);
            }
        }
        if (is_object($value)) {
            return $this->processObject($element, $value);
        }
        return $value;
    }

    /**
     * Replaces the given values (=keys) with the corresponding elements in $options
     * @see mapValueToOption()
     *
     * @param array $value
     * @param array $options
     * @return string
     */
    protected function mapValuesToOptions(array $value, array $options)
    {
        $result = array();
        foreach ($value as $key) {
            $result[] = $this->mapValueToOption($key, $options);
        }
        return $result;
    }

    /**
     * Replaces the given value (=key) with the corresponding element in $options
     * If the key does not exist in $options, it is returned without modification
     *
     * @param mixed $value
     * @param array $options
     * @return string
     */
    protected function mapValueToOption($value, array $options)
    {
        return isset($options[$value]) ? $options[$value] : $value;
    }

    /**
     * Converts the given $object to a string representation considering the $element FormElement definition
     *
     * @param FormElementInterface $element
     * @param object $object
     * @return string
     */
    protected function processObject(FormElementInterface $element, $object)
    {
        $properties = $element->getProperties();
        if ($object instanceof \DateTime) {
            if (isset($properties['dateFormat'])) {
                $dateFormat = $properties['dateFormat'];
                if (isset($properties['displayTimeSelector']) && $properties['displayTimeSelector'] === true) {
                    $dateFormat .= ' H:i';
                }
            } else {
                $dateFormat = \DateTime::W3C;
            }
            return $object->format($dateFormat);
        }
        if ($object instanceof Image) {
            return sprintf('%s Image (%d x %d)', $object->getFileExtension(), $object->getWidth(), $object->getHeight());
        }
        if (method_exists($object, '__toString')) {
            return (string)$object;
        }
        return 'Object [' . get_class($object) . ']';
    }
}
