<?php
namespace TYPO3\Form\ViewHelpers\Form;

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

/**
 * Form Element Rootline Path
 */
class FormElementRootlinePathViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param \TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable
     * @return string
     */
    public function render(\TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable)
    {
        $path = $renderable->getIdentifier();
        while ($renderable = $renderable->getParentRenderable()) {
            $path = $renderable->getIdentifier() . '/' . $path;
        }
        return $path;
    }
}
