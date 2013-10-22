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
	 * @return void
	 * @internal
	 */
	public function setParentRenderable(CompositeRenderableInterface $renderable);

	/**
	 * Set the index of this renderable inside the parent renderable
	 *
	 * @param integer $index
	 * @return void
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
	 * @return void
	 * @internal
	 */
	public function onRemoveFromParentRenderable();

	/**
	 * This is a callback that is invoked by the Form Factory after the whole form has been built.
	 * It can be used to add new form elements as children for complex form elements.
	 *
	 * @return void
	 * @api
	 */
	public function onBuildingFinished();
}
