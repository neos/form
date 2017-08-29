<?php
namespace Neos\Form\Core\Renderer;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Form\Core\Model\Renderable\RootRenderableInterface;
use Neos\Form\Core\Runtime\FormRuntime;

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
     * @param ControllerContext $controllerContext
     * @api
     */
    public function setControllerContext(ControllerContext $controllerContext);

    /**
     * Render the passed $renderable and return the rendered Renderable.
     * Note: This method is expected to invoke the beforeRendering() callback on the $renderable
     *
     * @param RootRenderableInterface $renderable
     * @return string the rendered $renderable
     * @api
     */
    public function renderRenderable(RootRenderableInterface $renderable);

    /**
     * @param FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function setFormRuntime(FormRuntime $formRuntime);

    /**
     * @return FormRuntime
     * @api
     */
    public function getFormRuntime();
}
