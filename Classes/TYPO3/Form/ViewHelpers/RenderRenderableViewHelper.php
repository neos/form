<?php
namespace TYPO3\Form\ViewHelpers;

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
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Form\Core\Model\Renderable\RenderableInterface;
use TYPO3\Form\Core\Renderer\RendererInterface;

/**
 * Render a renderable
 */
class RenderRenderableViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @param RenderableInterface $renderable
	 * @return string
	 */
	public function render(RenderableInterface $renderable) {
		/** @var RendererInterface $view */
		$view = $this->viewHelperVariableContainer->getView();
		return $view->renderRenderable($renderable);
	}
}
