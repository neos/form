<?php

namespace TYPO3\Form\Core\Renderer;


/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 *
 */
interface RendererInterface {
	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext);
	public function renderRenderable(\TYPO3\Form\Core\Model\Renderable\RootRenderableInterface $renderable);
}
?>