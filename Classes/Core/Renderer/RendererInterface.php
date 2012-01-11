<?php

namespace TYPO3\Form\Core\Renderer;


/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Base interface for Renderers. A Renderer is used to render a renderable.
 *
 * **This interface is meant to be implemented by developers, although often you
 * will subclass AbstractElementRenderer** ({@link AbstractElementRenderer}).
 */
interface RendererInterface {

	/**
	 * Set the controller context which should be used
	 *
	 * @param \TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext
	 * @api
	 */
	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext);

	/**
	 * Render the passed $renderable and return the rendered Renderable.
	 *
	 * @param \TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable
	 * @return string the rendered $renderable
	 * @api
	 */
	public function renderRenderable(\TYPO3\Form\Core\Model\Renderable\RootRenderableInterface $renderable);
}
?>