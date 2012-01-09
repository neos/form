<?php

namespace TYPO3\Form\Domain\Renderer;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author sebastian
 */
interface ElementRendererInterface {
	public function setControllerContext(\TYPO3\FLOW3\MVC\Controller\ControllerContext $controllerContext);
	public function renderRenderable(\TYPO3\Form\Domain\Model\RenderableInterface $renderable);
}
?>