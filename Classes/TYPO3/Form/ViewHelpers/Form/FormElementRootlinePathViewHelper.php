<?php
namespace TYPO3\Form\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Form".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Form Element Rootline Path
 */
class FormElementRootlinePathViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {


	/**
	 * @param \TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable
	 * @return string
	 */
	public function render(\TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable) {
		$path = $renderable->getIdentifier();
		while ($renderable = $renderable->getParentRenderable()) {
			$path = $renderable->getIdentifier() . '/' . $path;
		}
		return $path;
	}
}
