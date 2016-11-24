<?php
namespace TYPO3\Form\Core\Renderer;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Base interface for Renderers. A Renderer is used to render a renderable.
 *
 * **This interface is meant to be implemented by developers, although often you
 * will subclass AbstractElementRenderer** ({@link AbstractElementRenderer}).
 */
interface RendererInterface
{
    /**
     * Set the controller context which should be used
     *
     * @param \Neos\Flow\Mvc\Controller\ControllerContext $controllerContext
     * @api
     */
    public function setControllerContext(\Neos\Flow\Mvc\Controller\ControllerContext $controllerContext);

    /**
     * Render the passed $renderable and return the rendered Renderable.
     * Note: This method is expected to invoke the beforeRendering() callback on the $renderable
     *
     * @param \TYPO3\Form\Core\Model\Renderable\RootRenderableInterface $renderable
     * @return string the rendered $renderable
     * @api
     */
    public function renderRenderable(\TYPO3\Form\Core\Model\Renderable\RootRenderableInterface $renderable);

    /**
     * @param \TYPO3\Form\Core\Runtime\FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function setFormRuntime(\TYPO3\Form\Core\Runtime\FormRuntime $formRuntime);

    /**
     * @return \TYPO3\Form\Core\Runtime\FormRuntime
     * @api
     */
    public function getFormRuntime();
}
