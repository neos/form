<?php

namespace TYPO3\Form\Domain\Renderer;


/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 *
 */
interface FormRendererInterface {
	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext);
	public function renderRenderable(\TYPO3\Form\Domain\Model\RenderableInterface $renderable);
}
?>