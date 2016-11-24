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

/**
 * Abstract renderer which can be used as base class for custom renderers.
 *
 * **This class is meant to be subclassed by developers**.
 */
abstract class AbstractElementRenderer implements RendererInterface
{
    /**
     * The assigned controller context which might be needed by the renderer.
     *
     * @var \Neos\Flow\Mvc\Controller\ControllerContext
     * @api
     */
    protected $controllerContext;

    /**
     * @var \Neos\Form\Core\Runtime\FormRuntime
     * @api
     */
    protected $formRuntime;

    /**
     * Set the controller context which should be used
     *
     * @param \Neos\Flow\Mvc\Controller\ControllerContext $controllerContext
     * @api
     */
    public function setControllerContext(\Neos\Flow\Mvc\Controller\ControllerContext $controllerContext)
    {
        $this->controllerContext = $controllerContext;
    }

    /**
     * @param \Neos\Form\Core\Runtime\FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function setFormRuntime(\Neos\Form\Core\Runtime\FormRuntime $formRuntime)
    {
        $this->formRuntime = $formRuntime;
    }

    /**
     * @return \Neos\Form\Core\Runtime\FormRuntime
     * @api
     */
    public function getFormRuntime()
    {
        return $this->formRuntime;
    }
}
