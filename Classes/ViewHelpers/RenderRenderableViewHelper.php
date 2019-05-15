<?php
namespace Neos\Form\ViewHelpers;

/*
 * This file is part of the Neos.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\Form\Core\Model\Renderable\RenderableInterface;
use Neos\Form\Core\Renderer\RendererInterface;

/**
 * Render a renderable
 */
class RenderRenderableViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initialize the arguments.
     *
     * @return void
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('renderable', RenderableInterface::class, '', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /** @var RendererInterface $view */
        $view = $this->viewHelperVariableContainer->getView();
        return $view->renderRenderable($this->arguments['renderable']);
    }
}
