<?php
namespace TYPO3\Form\ViewHelpers;

/*
 * This file is part of the TYPO3.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Form\Core\Model\Renderable\RenderableInterface;
use TYPO3\Form\Core\Renderer\RendererInterface;

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
