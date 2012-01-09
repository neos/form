<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * A Form element contract
 * @todo Fill interface
 * @todo document
 * @internal
 */
abstract class AbstractRenderable implements RenderableInterface {

	/**
	 * @var string
	 */
	protected $type;


	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var array
	 */
	protected $renderingOptions = array();


	public function getType() {
		return $this->type;
	}

	/**
	 * The identifier of this renderable
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Get all rendering options
	 *
	 * @return array associative array of rendering options
	 * @api
	 */
	public function getRenderingOptions() {
		return $this->renderingOptions;
	}

	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}
}
?>