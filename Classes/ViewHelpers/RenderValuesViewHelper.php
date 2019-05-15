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

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Form\Core\Model\FormElementInterface;
use Neos\Form\Core\Model\Renderable\CompositeRenderableInterface;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Core\Renderer\RendererInterface;
use Neos\Form\Core\Runtime\FormRuntime;
use Neos\Media\Domain\Model\Image;

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
     * Initialize the arguments.
     *
     * @return void
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('renderable', RootRenderableInterface::class, 'Relative Fusion path to be rendered');
        $this->registerArgument('formRuntime', FormRuntime::class, 'Relative Fusion path to be rendered');
        $this->registerArgument('as', 'string', 'Relative Fusion path to be rendered', false, 'formValue');
    }

    /**
     * @return string the rendered form values
     */
    public function render(): string
    {
        if ($this->hasArgument('formRuntime')) {
            $formRuntime = $this->arguments['formRuntime'];
        } else {
            /** @var RendererInterface $fluidFormRenderer */
            $fluidFormRenderer = $this->viewHelperVariableContainer->getView();
            $formRuntime = $fluidFormRenderer->getFormRuntime();
        }
        if ($this->hasArgument('renderable')) {
            $renderable = $this->arguments['renderable'];
        } else {
            $renderable = $formRuntime->getFormDefinition();
        }
        if ($renderable instanceof CompositeRenderableInterface) {
            $elements = $renderable->getRenderablesRecursively();
        } else {
            $elements = [$renderable];
        }

        $formState = $formRuntime->getFormState();
        $output = '';
        foreach ($elements as $element) {
            if (!$element instanceof FormElementInterface) {
                continue;
            }
            $value = $formState->getFormValue($element->getIdentifier());

            $formValue = [
                'element' => $element,
                'value' => $value,
                'processedValue' => $this->processElementValue($element, $value),
                'isMultiValue' => is_array($value) || $value instanceof \Iterator
            ];
            $this->templateVariableContainer->add($this->arguments['as'], $formValue);
            $output .= $this->renderChildren();
            $this->templateVariableContainer->remove($this->arguments['as']);
        }
        return $output;
    }

    /**
     * Converts the given value to a simple type (string or array) considering the underlying FormElement definition
     *
     * @param FormElementInterface $element
     * @param mixed $value
     * @return string|array
     */
    protected function processElementValue(FormElementInterface $element, $value)
    {
        $properties = $element->getProperties();
        if (isset($properties['options']) && is_array($properties['options'])) {
            if (is_array($value)) {
                return $this->mapValuesToOptions($value, $properties['options']);
            }

            return $this->mapValueToOption($value, $properties['options']);
        }
        if (is_object($value)) {
            return $this->processObject($element, $value);
        }
        return $value;
    }

    /**
     * Replaces the given values (=keys) with the corresponding elements in $options
     *
     * @see mapValueToOption()
     *
     * @param array $value
     * @param array $options
     * @return array
     */
    protected function mapValuesToOptions(array $value, array $options): array
    {
        $result = [];
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
    protected function mapValueToOption($value, array $options): string
    {
        return $options[$value] ?? $value;
    }

    /**
     * Converts the given $object to a string representation considering the $element FormElement definition
     *
     * @param FormElementInterface $element
     * @param object $object
     * @return string
     */
    protected function processObject(FormElementInterface $element, $object): string
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
