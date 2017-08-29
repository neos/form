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
use Neos\Form\Core\Runtime\FormRuntime;

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
     * @var ControllerContext
     * @api
     */
    protected $controllerContext;

    /**
     * @var FormRuntime
     * @api
     */
    protected $formRuntime;

    /**
     * Set the controller context which should be used
     *
     * @param ControllerContext $controllerContext
     * @api
     */
    public function setControllerContext(ControllerContext $controllerContext)
    {
        $this->controllerContext = $controllerContext;
    }

    /**
     * @param FormRuntime $formRuntime
     * @return void
     * @api
     */
    public function setFormRuntime(FormRuntime $formRuntime)
    {
        $this->formRuntime = $formRuntime;
    }

    /**
     * @return FormRuntime
     * @api
     */
    public function getFormRuntime()
    {
        return $this->formRuntime;
    }
}
