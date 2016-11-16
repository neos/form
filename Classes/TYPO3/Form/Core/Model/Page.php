<?php
namespace TYPO3\Form\Core\Model;

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
 * A Page, being part of a bigger FormDefinition. It contains numerous FormElements
 * as children.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * A FormDefinition consists of multiple Pages, where only one page is visible
 * at any given time.
 *
 * Most of the API of this object is implemented in {@link AbstractSection},
 * so make sure to review this class as well.
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
class Page extends AbstractSection
{
    /**
     * Constructor. Needs this Page's identifier
     *
     * @param string $identifier The Page's identifier
     * @param string $type The Page's type
     * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was no non-empty string
     * @api
     */
    public function __construct($identifier, $type = 'TYPO3.Form:Page')
    {
        parent::__construct($identifier, $type);
    }

    /**
     * Set the parent renderable
     *
     * @param Renderable\CompositeRenderableInterface $parentRenderable
     * @return void
     * @throws \TYPO3\Form\Exception
     */
    public function setParentRenderable(Renderable\CompositeRenderableInterface $parentRenderable)
    {
        if (!($parentRenderable instanceof FormDefinition)) {
            throw new \TYPO3\Form\Exception(sprintf('The specified parentRenderable must be a FormDefinition, got "%s"', is_object($parentRenderable) ? get_class($parentRenderable) : gettype($parentRenderable)), 1329233747);
        }
        parent::setParentRenderable($parentRenderable);
    }
}
