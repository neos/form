<?php
namespace TYPO3\Form\Core\Model\Renderable;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Convenience base class which implements common functionality for most
 * classes which implement CompositeRenderableInterface, i.e. have **child renderable elements**.
 *
 * **This class should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 */
abstract class AbstractCompositeRenderable extends AbstractRenderable implements CompositeRenderableInterface {

	/**
	 * array of child renderables
	 *
	 * @var array<TYPO3\Form\Core\Model\RenderableInterface>
	 */
	protected $renderables = array();

	protected function addRenderable(RenderableInterface $renderable) {
		if ($renderable->getParentRenderable() !== NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The renderable with identifier "%s" is already added to another element (element identifier: "%s").', $renderable->getIdentifier(), $renderable->getParentRenderable()->getIdentifier()), 1325665144);
		}
		$renderable->setIndex(count($this->renderables));
		$renderable->setParentRenderable($this);
		$this->renderables[] = $renderable;
	}

	/**
	 * Move $renderableToMove before $referenceRenderable
	 *
	 * @param RenderableInterface $renderableToMove
	 * @param RenderableInterface $referenceRenderable
	 * @api
	 */
	protected function moveRenderableBefore(RenderableInterface $renderableToMove, RenderableInterface $referenceRenderable) {
		if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
		}

		$reorderedRenderables = array();
		$i = 0;
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToMove) continue;

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
	 * @param RenderableInterface $renderableToMove
	 * @param RenderableInterface $referenceRenderable
	 * @api
	 */
	protected function moveRenderableAfter(RenderableInterface $renderableToMove, RenderableInterface $referenceRenderable) {
		if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
		}

		$reorderedRenderables = array();
		$i = 0;
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToMove) continue;

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

	protected function removeRenderable(RenderableInterface $renderableToRemove) {
		if ($renderableToRemove->getParentRenderable() !== $this) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException('The renderable to be removed must be part of the calling parent renderable.', 1326090127);
		}

		$updatedRenderables = array();
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToRemove) continue;

			$updatedRenderables[] = $renderable;
		}
		$this->renderables = $updatedRenderables;

		$renderableToRemove->onRemoveFromParentRenderable();
	}

	public function registerInFormIfPossible() {
		parent::registerInFormIfPossible();
		foreach ($this->renderables as $renderable) {
			$renderable->registerInFormIfPossible();
		}
	}

	public function onRemoveFromParentRenderable() {
		foreach ($this->renderables as $renderable) {
			$renderable->onRemoveFromParentRenderable();
		}
		parent::onRemoveFromParentRenderable();
	}
}
?>