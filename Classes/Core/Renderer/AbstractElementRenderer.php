<?php

namespace TYPO3\Form\Core\Renderer;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Abstract renderer which can be used as base class for custom renderers.
 *
 * **This class is meant to be subclassed by developers**.
 */
abstract class AbstractElementRenderer implements RendererInterface {

	/**
	 * The assigned controller context which might be needed by the renderer.
	 *
	 * @var \TYPO3\FLOW3\MVC\Controller\ControllerContext
	 * @api
	 */
	protected $controllerContext;

	/**
	 * @var \TYPO3\Form\Core\Runtime\FormRuntime
	 * @api
	 */
	protected $formRuntime;

	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	public function setFormRuntime(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime) {
		$this->formRuntime = $formRuntime;
	}

	public function getFormRuntime() {
		return $this->formRuntime;
	}
}
?>