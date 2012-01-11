<?php
namespace TYPO3\Form\Core\Model\Renderable;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
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

	public function getType() {
		return $this->type;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Set the renderer class name
	 *
	 * @param string $rendererClassName
	 * @api
	 */
	public function setRendererClassName($rendererClassName) {
		$this->rendererClassName = $rendererClassName;
	}


	public function getRendererClassName() {
		return $this->rendererClassName;
	}

	public function getRenderingOptions() {
		return $this->renderingOptions;
	}

	/**
	 * Set the rendering option $key to $value.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 */
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	public function getParentRenderable() {
		return $this->parentRenderable;
	}

	public function setParentRenderable(CompositeRenderableInterface $parentRenderable) {
		$this->parentRenderable = $parentRenderable;
		$this->registerInFormIfPossible();
	}

	/**
	 * Register this element at the parent form, if there is a connection to the parent form.
	 *
	 * @internal
	 */
	public function registerInFormIfPossible() {
		$rootRenderable = $this->parentRenderable;
		while ($rootRenderable !== NULL && !($rootRenderable instanceof \TYPO3\Form\Core\Model\FormDefinition)) {
			$rootRenderable = $rootRenderable->getParentRenderable();
		}
		if ($rootRenderable !== NULL) {
			$rootRenderable->registerRenderable($this);
		}
	}


	public function onRemoveFromParentRenderable() {
		$rootForm = $this->parentRenderable;
		while ($rootForm !== NULL && !($rootForm instanceof \TYPO3\Form\Core\Model\FormDefinition)) {
			$rootForm = $rootForm->getParentRenderable();
		}
		if ($rootForm !== NULL) {
			$rootForm->unregisterRenderable($this);
		}
		$this->parentRenderable = NULL;
	}

	public function getIndex() {
		return $this->index;
	}

	public function setIndex($index) {
		$this->index = $index;
	}

	public function getLabel() {
		return $this->label;
	}

	/**
	 * Set the label which shall be displayed next to the form element
	 *
	 * @param string $label
	 * @api
	 */
	public function setLabel($label) {
		$this->label = $label;
	}



}
?>