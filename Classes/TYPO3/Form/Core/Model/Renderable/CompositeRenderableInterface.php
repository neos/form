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
 * Interface which all Form Parts must adhere to **when they have sub elements**.
 * This includes especially "FormDefinition" and "Page".
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 *
 */
interface CompositeRenderableInterface extends RenderableInterface {

	/**
	 * Returns all RenderableInterface instances of this composite renderable recursively
	 *
	 * @return array<TYPO3\Form\Core\Model\RenderableInterface>
	 * @internal
	 */
	public function getRenderablesRecursively();
}
