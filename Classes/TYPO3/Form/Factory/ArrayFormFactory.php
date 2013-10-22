<?php
namespace TYPO3\Form\Factory;

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
use TYPO3\Form\Core\Model\FormDefinition;
/**´
 *
 * @Flow\Scope("singleton")
 */
class ArrayFormFactory extends AbstractFormFactory {

	/**
	 * Build a form definition, depending on some configuration and a "Preset Name".
	 *
	 * @param array $configuration
	 * @param string $presetName
	 * @return \TYPO3\Form\Core\Model\FormDefinition
	 */
	public function build(array $configuration, $presetName) {
		$formDefaults = $this->getPresetConfiguration($presetName);

		$form = new FormDefinition($configuration['identifier'], $formDefaults);
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
	 * @param \TYPO3\Form\Core\Model\Renderable\CompositeRenderableInterface CompositeRenderableInterface $parentRenderable
	 * @return mixed
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException
	 */
	protected function addNestedRenderable($nestedRenderableConfiguration, \TYPO3\Form\Core\Model\Renderable\CompositeRenderableInterface $parentRenderable) {
		if (!isset($nestedRenderableConfiguration['identifier'])) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('Identifier not set.', 1329289436);
		}
		if ($parentRenderable instanceof FormDefinition) {
			$renderable = $parentRenderable->createPage($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
		} else {
			$renderable = $parentRenderable->createElement($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
		}

		if (isset($nestedRenderableConfiguration['renderables']) && is_array($nestedRenderableConfiguration['renderables'])) {
			$childRenderables = $nestedRenderableConfiguration['renderables'];
		} else {
			$childRenderables = array();
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
	protected function convertJsonArrayToAssociativeArray($input) {
		$output = array();
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
