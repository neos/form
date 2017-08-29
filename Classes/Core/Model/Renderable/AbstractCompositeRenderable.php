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

use Neos\Form\Exception\FormDefinitionConsistencyException;

/**
 * Convenience base class which implements common functionality for most
 * classes which implement CompositeRenderableInterface, i.e. have **child renderable elements**.
 *
 * **This class should not be subclassed by developers**, it is only
 * used for improving the internal code structure.
 */
abstract class AbstractCompositeRenderable extends AbstractRenderable implements CompositeRenderableInterface
{
    /**
     * array of child renderables
     *
     * @var RenderableInterface[]
     * @api
     */
    protected $renderables = [];

    /**
     * Add a renderable to the list of child renderables.
     *
     * This function will be wrapped by the subclasses, f.e. with an "addPage"
     * or "addElement" method with the correct type hint.
     *
     * @param RenderableInterface $renderable
     * @return void
     * @throws FormDefinitionConsistencyException
     * @internal
     */
    protected function addRenderable(RenderableInterface $renderable)
    {
        if ($renderable->getParentRenderable() !== null) {
            throw new FormDefinitionConsistencyException(sprintf('The renderable with identifier "%s" is already added to another element (element identifier: "%s").', $renderable->getIdentifier(), $renderable->getParentRenderable()->getIdentifier()), 1325665144);
        }
        $renderable->setIndex(count($this->renderables));
        $renderable->setParentRenderable($this);
        $this->renderables[] = $renderable;
    }

    /**
     * Move $renderableToMove before $referenceRenderable
     *
     * This function will be wrapped by the subclasses, f.e. with an "movePageBefore"
     * or "moveElementBefore" method with the correct type hint.
     *
     * @param RenderableInterface $renderableToMove
     * @param RenderableInterface $referenceRenderable
     * @return void
     * @throws FormDefinitionConsistencyException
     * @internal
     */
    protected function moveRenderableBefore(RenderableInterface $renderableToMove, RenderableInterface $referenceRenderable)
    {
        if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
            throw new FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
        }

        $reorderedRenderables = [];
        $i = 0;
        foreach ($this->renderables as $renderable) {
            if ($renderable === $renderableToMove) {
                continue;
            }

            if ($renderable === $referenceRenderable) {
                $reorderedRenderables[] = $renderableToMove;
                $renderableToMove->setIndex($i);
                $i++;
            }
            $reorderedRenderables[] = $renderable;
            $renderable->setIndex($i);
            $i++;
        }
        $this->renderables = $reorderedRenderables;
    }

    /**
     * Move $renderableToMove after $referenceRenderable
     *
     * This function will be wrapped by the subclasses, f.e. with an "movePageAfter"
     * or "moveElementAfter" method with the correct type hint.
     *
     * @param RenderableInterface $renderableToMove
     * @param RenderableInterface $referenceRenderable
     * @return void
     * @throws FormDefinitionConsistencyException
     * @internal
     */
    protected function moveRenderableAfter(RenderableInterface $renderableToMove, RenderableInterface $referenceRenderable)
    {
        if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
            throw new FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
        }

        $reorderedRenderables = [];
        $i = 0;
        foreach ($this->renderables as $renderable) {
            if ($renderable === $renderableToMove) {
                continue;
            }

            $reorderedRenderables[] = $renderable;
            $renderable->setIndex($i);
            $i++;

            if ($renderable === $referenceRenderable) {
                $reorderedRenderables[] = $renderableToMove;
                $renderableToMove->setIndex($i);
                $i++;
            }
        }
        $this->renderables = $reorderedRenderables;
    }

    /**
     * Returns all RenderableInterface instances of this composite renderable recursively
     *
     * @return RenderableInterface[]
     * @internal
     */
    public function getRenderablesRecursively()
    {
        $renderables = [];
        foreach ($this->renderables as $renderable) {
            $renderables[] = $renderable;
            if ($renderable instanceof CompositeRenderableInterface) {
                $renderables = array_merge($renderables, $renderable->getRenderablesRecursively());
            }
        }
        return $renderables;
    }

    /**
     * Remove a renderable from this renderable.
     *
     * This function will be wrapped by the subclasses, f.e. with an "removePage"
     * or "removeElement" method with the correct type hint.
     *
     * @param RenderableInterface $renderableToRemove
     * @return void
     * @throws FormDefinitionConsistencyException
     * @internal
     */
    protected function removeRenderable(RenderableInterface $renderableToRemove)
    {
        if ($renderableToRemove->getParentRenderable() !== $this) {
            throw new FormDefinitionConsistencyException('The renderable to be removed must be part of the calling parent renderable.', 1326090127);
        }

        $updatedRenderables = [];
        foreach ($this->renderables as $renderable) {
            if ($renderable === $renderableToRemove) {
                continue;
            }

            $updatedRenderables[] = $renderable;
        }
        $this->renderables = $updatedRenderables;

        $renderableToRemove->onRemoveFromParentRenderable();
    }

    /**
     * Register this element at the parent form, if there is a connection to the parent form.
     *
     * @internal
     * @return void
     */
    public function registerInFormIfPossible()
    {
        parent::registerInFormIfPossible();
        foreach ($this->renderables as $renderable) {
            if ($renderable instanceof AbstractRenderable) {
                $renderable->registerInFormIfPossible();
            }
        }
    }

    /**
     * This function is called after a renderable has been removed from its parent
     * renderable.
     * This just passes the event down to all child renderables of this composite renderable.
     *
     * @return void
     * @internal
     */
    public function onRemoveFromParentRenderable()
    {
        foreach ($this->renderables as $renderable) {
            $renderable->onRemoveFromParentRenderable();
        }
        parent::onRemoveFromParentRenderable();
    }
}
