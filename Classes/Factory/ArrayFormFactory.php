<?php
namespace TYPO3\Form\Factory;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use TYPO3\Form\Core\Model\FormDefinition;
/**´
 *
 * @FLOW3\Scope("singleton")
 */
class ArrayFormFactory extends AbstractFormFactory {
	public function build(array $configuration, $presetName) {
		$formDefaults = $this->getPresetConfiguration($presetName);

		$form = new FormDefinition($configuration['identifier'], $formDefaults);
		foreach ($configuration['renderables'] as $pageConfiguration) {
			$this->addNestedRenderable($pageConfiguration, $form);
		}
		if (isset($configuration['finishers']) && is_array($configuration['finishers'])) {
			foreach ($configuration['finishers'] as $finisherConfiguration) {
				$finisherClassName = $finisherConfiguration['name'];
				$finisherOptions = (isset($finisherConfiguration['options']) ? $finisherConfiguration['options'] : array());
				$finisher = new $finisherClassName();
				$finisher->setOptions($finisherOptions);
				$form->addFinisher($finisher);
			}
		}

		unset($configuration['renderables']);
		unset($configuration['finishers']);
		unset($configuration['type']);
		unset($configuration['identifier']);
		unset($configuration['label']);
		$form->setOptions($configuration);

		return $form;
	}

	protected function addNestedRenderable($nestedRenderableConfiguration, \TYPO3\Form\Core\Model\Renderable\CompositeRenderableInterface $parentRenderable) {
		if (!isset($nestedRenderableConfiguration['identifier'])) {
			throw new \Exception('Identifier not set');
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

		if (isset($nestedRenderableConfiguration['validators']) && is_array($nestedRenderableConfiguration['validators'])) {
			foreach ($nestedRenderableConfiguration['validators'] as $validatorConfiguration) {
				$validatorClassName = $validatorConfiguration['name'];
				$validatorOptions = (isset($validatorConfiguration['options']) ? $validatorConfiguration['options'] : array());
				$renderable->addValidator(new $validatorClassName($validatorOptions));
			}
		}

		unset($nestedRenderableConfiguration['type']);
		unset($nestedRenderableConfiguration['identifier']);
		unset($nestedRenderableConfiguration['renderables']);
		unset($nestedRenderableConfiguration['validators']);

		$nestedRenderableConfiguration = $this->convertJsonArrayToAssociativeArray($nestedRenderableConfiguration);
		$renderable->setOptions($nestedRenderableConfiguration);

		foreach ($childRenderables as $elementConfiguration) {
			$this->addNestedRenderable($elementConfiguration, $renderable);
		}

		return $renderable;
	}


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
?>