<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Base interface which all Form Elements, and also the FormDefinition and Page
 * must adhere to.
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 *
 */
interface RenderableInterface {

	/**
	 * Abstract "type" of this Renderable. Is used during the rendering process
	 * to determine the template file or the View PHP class being used to render
	 * the particular element.
	 *
	 * @return string
	 * @api
	 */
	public function getType();

	/**
	 * The identifier of this renderable
	 *
	 * @return string
	 * @api
	 */
	public function getIdentifier();

	/**
	 * Get all rendering options
	 *
	 * @return array associative array of rendering options
	 * @api
	 */
	public function getRenderingOptions();

}
?>