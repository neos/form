<?php
namespace Neos\Form\Core\Model\Renderable;

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
 * Interface which all Form Parts must adhere to **when they have sub elements**.
 * This includes especially "FormDefinition" and "Page".
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 *
 */
interface CompositeRenderableInterface extends RenderableInterface
{
    /**
     * Returns all RenderableInterface instances of this composite renderable recursively
     *
     * @return RenderableInterface[]
     * @internal
     */
    public function getRenderablesRecursively();
}
