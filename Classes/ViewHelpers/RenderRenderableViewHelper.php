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
     * @param RenderableInterface $renderable
     * @return string
     */
    public function render(RenderableInterface $renderable)
    {
        /** @var RendererInterface $view */
        $view = $this->viewHelperVariableContainer->getView();
        return $view->renderRenderable($renderable);
    }
}
