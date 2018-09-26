<?php
namespace Neos\Form\Factory;

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
use Neos\Form\Core\Model\AbstractSection;
use Neos\Form\Core\Model\FormDefinition;
use Neos\Form\Core\Model\Renderable\CompositeRenderableInterface;
use Neos\Form\Core\Model\Renderable\RenderableInterface;
use Neos\Form\Exception;
use Neos\Form\Exception\IdentifierNotValidException;

/**´
 *
 * @Flow\Scope("singleton")
 */
class ArrayFormFactory extends AbstractFormFactory
{
    /**
     * Build a form definition, depending on some configuration and a "Preset Name".
     *
     * @param array $configuration
     * @param string $presetName
     * @return \Neos\Form\Core\Model\FormDefinition
     */
    public function build(array $configuration, $presetName)
    {
        $formDefaults = $this->getPresetConfiguration($presetName);

        $form = isset($configuration['type']) ?
            new FormDefinition($configuration['identifier'], $formDefaults, $configuration['type']) :
            new FormDefinition($configuration['identifier'], $formDefaults);

        if (isset($configuration['renderables'])) {
            foreach ($configuration['renderables'] as $pageConfiguration) {
                $this->addNestedRenderable($pageConfiguration, $form);
            }
        }

        unset($configuration['renderables']);
        unset($configuration['type']);
        unset($configuration['identifier']);
        unset($configuration['label']);
        $form->setOptions($configuration);

        $this->triggerFormBuildingFinished($form);

        return $form;
    }

    /**
     * @param array $nestedRenderableConfiguration
     * @param CompositeRenderableInterface $parentRenderable
     * @return RenderableInterface
     * @throws Exception|IdentifierNotValidException
     */
    protected function addNestedRenderable(array $nestedRenderableConfiguration, CompositeRenderableInterface $parentRenderable)
    {
        if (!isset($nestedRenderableConfiguration['identifier'])) {
            throw new IdentifierNotValidException('Identifier not set.', 1329289436);
        }
        if ($parentRenderable instanceof FormDefinition) {
            $renderable = $parentRenderable->createPage($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
        } elseif ($parentRenderable instanceof AbstractSection) {
            $renderable = $parentRenderable->createElement($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
        } else {
            throw new Exception(sprintf('parentRenderable has to be an instance of FormDefinition or AbstractSection, got "%s"', is_object($parentRenderable) ? get_class($parentRenderable) : gettype($parentRenderable)), 1504024050);
        }

        if (isset($nestedRenderableConfiguration['renderables']) && is_array($nestedRenderableConfiguration['renderables'])) {
            $childRenderables = $nestedRenderableConfiguration['renderables'];
        } else {
            $childRenderables = [];
        }

        unset($nestedRenderableConfiguration['type']);
        unset($nestedRenderableConfiguration['identifier']);
        unset($nestedRenderableConfiguration['renderables']);

        $nestedRenderableConfiguration = $this->convertJsonArrayToAssociativeArray($nestedRenderableConfiguration);
        $renderable->setOptions($nestedRenderableConfiguration);

        foreach ($childRenderables as $elementConfiguration) {
            $this->addNestedRenderable($elementConfiguration, $renderable);
        }

        return $renderable;
    }

    /**
     * @param array $input
     * @return array
     */
    protected function convertJsonArrayToAssociativeArray(array $input)
    {
        $output = [];
        foreach ($input as $key => $value) {
            if (is_integer($key) && is_array($value) && isset($value['_key']) && isset($value['_value'])) {
                $key = $value['_key'];
                $value = $value['_value'];
            }
            if (is_array($value)) {
                $output[$key] = $this->convertJsonArrayToAssociativeArray($value);
            } else {
                $output[$key] = $value;
            }
        }
        return $output;
    }
}
