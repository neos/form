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
 */
interface RenderableInterface {
	/**
	 * Abstract "type" of this Renderable. Is used during the rendering process
	 * to determine the template file or the View PHP class being used to render
	 * the particular element
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * The identifier of this renderable
	 *
	 * @return string
	 */
	public function getIdentifier();

	// TODO: maybe also a rendering context

	public function getTemplateVariableName();
}
?>