<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Convenience base class which implements common functionality for most
 * classes which implement RenderableInterface.
 *
 * **This interface should not be implemented by developers**, it is only
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
	 * of the renderer itself.
	 *
	 * @var string
	 */
	protected $rendererClassName = NULL;


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


}
?>