<?php
namespace TYPO3\Form\Core\Model\Renderable;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Base interface which all Form Parts except the FormDefinition must adhere
 * to (i.e. all elements which are NOT the root of a Form).
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
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
	 * Set the new parent renderable. You should not call this directly;
	 * it is automatically called by addRenderable.
	 *
	 * This method should also register itself at the parent form, if possible.
	 *
	 * @param CompositeRenderableInterface $renderable
	 * @internal
	 */
	public function setParentRenderable(CompositeRenderableInterface $renderable);

	/**
	 * Set the index of this renderable inside the parent renderable
	 *
	 * @param integer $index
	 * @internal
	 */
	public function setIndex($index);

	/**
	 * Get the index inside the parent renderable
	 *
	 * @return integer
	 * @api
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