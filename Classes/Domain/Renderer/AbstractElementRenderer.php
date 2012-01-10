<?php

namespace TYPO3\Form\Domain\Renderer;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 *
 */
abstract class AbstractElementRenderer implements RendererInterface {
	protected $controllerContext;

	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}
}
?>