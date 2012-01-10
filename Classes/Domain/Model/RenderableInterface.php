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
interface RenderableInterface extends RootRenderableInterface {

	/**
	 * Return the parent renderable
	 *
	 * @return CompositeRenderableInterface the parent renderable
	 * @internal
	 */
	public function getParentRenderable();

	/**
	 * @param CompositeRenderableInterface $renderable
	 * @internal
	 */
	public function setParentRenderable(CompositeRenderableInterface $renderable);

	/**
	 * Set the index in the parent renderable
	 *
	 * @param integer $index
	 * @internal
	 */
	public function setIndex($index);

	/**
	 * Get the index inside the parent renderable
	 *
	 * @return integer
	 */
	public function getIndex();

	/**
	 * This function is called after a renderable has been removed from its parent
	 * renderable. The function should make sure to clean up the internal state,
	 * like reseting $this->parentRenderable or deregistering the renderable
	 * at the form.
	 *
	 * @internal
	 */
	public function onRemoveFromParentRenderable();
}
?>