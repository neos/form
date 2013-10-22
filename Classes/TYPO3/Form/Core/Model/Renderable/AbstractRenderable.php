<?php
namespace TYPO3\Form\Core\Model\Renderable;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Convenience base class which implements common functionality for most
 * classes which implement RenderableInterface.
 *
 * **This class should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 */
abstract class AbstractRenderable implements RenderableInterface {

	/**
	 * Abstract "type" of this Renderable. Is used during the rendering process
	 * to determine the template file or the View PHP class being used to render
	 * the particular element.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The identifier of this renderable
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * The parent renderable
	 *
	 * @var CompositeRenderableInterface
	 */
	protected $parentRenderable;

	/**
	 * The label of this renderable
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * associative array of rendering options
	 *
	 * @var array
	 */
	protected $renderingOptions = array();

	/**
	 * Renderer class name to be used for this renderable.
	 *
	 * Is only set if a specific renderer should be used for this renderable,
	 * if it is NULL the caller needs to determine the renderer or take care
	 * of the rendering itself.
	 *
	 * @var string
	 */
	protected $rendererClassName = NULL;

	/**
	 * The position of this renderable inside the parent renderable.
	 *
	 * @var integer
	 */
	protected $index = 0;

	/**
	 * Get the type of the renderable
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the identifier of the element
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Set multiple properties of this object at once.
	 * Every property which has a corresponding set* method can be set using
	 * the passed $options array.
	 *
	 * @param array $options
	 * @return void
	 * @internal
	 */
	public function setOptions(array $options) {
		if (isset($options['label'])) {
			$this->setLabel($options['label']);
		}

		if (isset($options['defaultValue'])) {
			$this->setDefaultValue($options['defaultValue']);
		}

		if (isset($options['properties'])) {
			foreach ($options['properties'] as $key => $value) {
				$this->setProperty($key, $value);
			}
		}

		if (isset($options['rendererClassName'])) {
			$this->setRendererClassName($options['rendererClassName']);
		}

		if (isset($options['renderingOptions'])) {
			foreach ($options['renderingOptions'] as $key => $value) {
				$this->setRenderingOption($key, $value);
			}
		}

		if (isset($options['validators'])) {
			foreach ($options['validators'] as $validatorConfiguration) {
				$this->createValidator($validatorConfiguration['identifier'], isset($validatorConfiguration['options']) ? $validatorConfiguration['options'] : array());
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($options, array('label', 'defaultValue', 'properties', 'rendererClassName', 'renderingOptions', 'validators'));
	}

	/**
	 * Create a validator for the element
	 *
	 * @param string $validatorIdentifier
	 * @param array $options
	 * @return mixed
	 * @throws \TYPO3\Form\Exception\ValidatorPresetNotFoundException
	 */
	public function createValidator($validatorIdentifier, array $options = array()) {
		$validatorPresets = $this->getRootForm()->getValidatorPresets();
		if (isset($validatorPresets[$validatorIdentifier]) && is_array($validatorPresets[$validatorIdentifier]) && isset($validatorPresets[$validatorIdentifier]['implementationClassName'])) {
			$implementationClassName = $validatorPresets[$validatorIdentifier]['implementationClassName'];
			$defaultOptions = isset($validatorPresets[$validatorIdentifier]['options']) ? $validatorPresets[$validatorIdentifier]['options'] : array();

			$options = \TYPO3\Flow\Utility\Arrays::arrayMergeRecursiveOverrule($defaultOptions, $options);

			$validator = new $implementationClassName($options);
			$this->addValidator($validator);
			return $validator;
		} else {
			throw new \TYPO3\Form\Exception\ValidatorPresetNotFoundException('The validator preset identified by "' . $validatorIdentifier . '" could not be found, or the implementationClassName was not specified.', 1328710202);
		}

	}

	/**
	 * Add a validator to the element
	 *
	 * @param \TYPO3\Flow\Validation\Validator\ValidatorInterface $validator
	 * @return void
	 */
	public function addValidator(\TYPO3\Flow\Validation\Validator\ValidatorInterface $validator) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->addValidator($validator);
	}

	/**
	 * Get all validators on the element
	 *
	 * @return \SplObjectStorage
	 */
	public function getValidators() {
		$formDefinition = $this->getRootForm();
		return $formDefinition->getProcessingRule($this->getIdentifier())->getValidators();
	}

	/**
	 * Set the datatype
	 *
	 * @param string $dataType
	 * @return void
	 */
	public function setDataType($dataType) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->setDataType($dataType);
	}

	/**
	 * Set the renderer class name
	 *
	 * @param string $rendererClassName
	 * @api
	 * @return void
	 */
	public function setRendererClassName($rendererClassName) {
		$this->rendererClassName = $rendererClassName;
	}

	/**
	 * Get the classname of the renderer
	 *
	 * @return string
	 */
	public function getRendererClassName() {
		return $this->rendererClassName;
	}

	/**
	 * Get all rendering options
	 *
	 * @return array
	 */
	public function getRenderingOptions() {
		return $this->renderingOptions;
	}

	/**
	 * Set the rendering option $key to $value.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 * @return mixed
	 */
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	/**
	 * Get the parent renderable
	 *
	 * @return CompositeRenderableInterface
	 * @return void
	 */
	public function getParentRenderable() {
		return $this->parentRenderable;
	}

	/**
	 * Set the parent renderable
	 *
	 * @param CompositeRenderableInterface $parentRenderable
	 * @return void
	 */
	public function setParentRenderable(CompositeRenderableInterface $parentRenderable) {
		$this->parentRenderable = $parentRenderable;
		$this->registerInFormIfPossible();
	}

	/**
	 * Get the root form this element belongs to
	 *
	 * @internal
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException
	 * @return \TYPO3\Form\Core\Model\FormDefinition
	 */
	public function getRootForm() {
		$rootRenderable = $this->parentRenderable;
		while ($rootRenderable !== NULL && !($rootRenderable instanceof \TYPO3\Form\Core\Model\FormDefinition)) {
			$rootRenderable = $rootRenderable->getParentRenderable();
		}
		if ($rootRenderable === NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The form element "%s" is not attached to a parent form.', $this->identifier), 1326803398);
		}

		return $rootRenderable;
	}

	/**
	 * Register this element at the parent form, if there is a connection to the parent form.
	 *
	 * @internal
	 * @return void
	 */
	public function registerInFormIfPossible() {
		try {
			$rootForm = $this->getRootForm();
			$rootForm->registerRenderable($this);
		} catch (\TYPO3\Form\Exception\FormDefinitionConsistencyException $exception) {
		}
	}

	/**
	 * Triggered when the renderable is removed from it's parent
	 *
	 * @return void
	 */
	public function onRemoveFromParentRenderable() {
		try {
			$rootForm = $this->getRootForm();
			$rootForm->unregisterRenderable($this);
		} catch (\TYPO3\Form\Exception\FormDefinitionConsistencyException $exception) {
		}
		$this->parentRenderable = NULL;
	}

	/**
	 * Get the index of the renderable
	 *
	 * @return integer
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Set the index of the renderable
	 *
	 * @param integer $index
	 * @return void
	 */
	public function setIndex($index) {
		$this->index = $index;
	}

	/**
	 * Get the label of the renderable
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Set the label which shall be displayed next to the form element
	 *
	 * @param string $label
	 * @return void
	 * @api
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * Override this method in your custom Renderable if needed
	 *
	 * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
	 * @return void
	 */
	public function beforeRendering(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime) {
	}

	/**
	 * This is a callback that is invoked by the Form Factory after the whole form has been built.
	 * It can be used to add new form elements as children for complex form elements.
	 *
	 * Override this method in your custom Renderable if needed.
	 *
	 * @return void
	 * @api
	 */
	public function onBuildingFinished() {
	}

}
