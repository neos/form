<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * A Form element contract
 * @todo Fill interface
 */
interface RenderableInterface {
	/**
	 * @return string
	 */
	public function getType();

	/**
	 * The (globally unique) identifier of this renderable
	 *
	 * @return string
	 */
	public function getIdentifier();

	// TODO: maybe also a rendering context

	public function getTemplateVariableName();
}
?>