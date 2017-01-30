<?php

namespace Neos\Form\ViewHelpers\Form;

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

/**
 * Form Element Rootline Path.
 */
class FormElementRootlinePathViewHelper extends AbstractViewHelper
{
    /**
     * @param RenderableInterface $renderable
     *
     * @return string
     */
    public function render(RenderableInterface $renderable)
    {
        $path = $renderable->getIdentifier();
        while ($renderable = $renderable->getParentRenderable()) {
            $path = $renderable->getIdentifier().'/'.$path;
        }

        return $path;
    }
}
