<?php
namespace TYPO3\Form\Domain\View;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Page
 *
 * @todo check if we can use singleton at some point...
 */
class FluidRenderer extends \TYPO3\Fluid\View\TemplateView {

	public function renderRenderable(\TYPO3\Form\Domain\Model\RenderableInterface $renderable) {
		$renderableType = $renderable->getType();

		$renderablePathAndFilename = $this->getPathAndFilenameForRenderable($renderableType);
		$parsedRenderable = $this->getParsedRenderable($renderableType, $renderablePathAndFilename);

		if ($this->getCurrentRenderingContext() === NULL) {
				// We do not have a "current" rendering context yet, so we use the base rendering context
			$this->baseRenderingContext->setControllerContext($this->controllerContext);
			$renderingContext = $this->baseRenderingContext;
		} else {
			$renderingContext = clone $this->getCurrentRenderingContext();
		}

		$templateVariableContainer = new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer(array($renderable->getTemplateVariableName() => $renderable));
		$renderingContext->injectTemplateVariableContainer($templateVariableContainer);

		if ($parsedRenderable->hasLayout()) {
			$renderableLayoutName = $parsedRenderable->getLayoutName($renderingContext);
			$renderableLayoutPathAndFilename = $this->getPathAndFilenameForRenderableLayout($renderableLayoutName);
			$parsedLayout = $this->getParsedRenderable($renderableLayoutName, $renderableLayoutPathAndFilename);

			$this->startRendering(self::RENDERING_LAYOUT, $parsedRenderable, $renderingContext);
			$output = $parsedLayout->render($renderingContext);
			$this->stopRendering();
		} else {
			$this->startRendering(self::RENDERING_TEMPLATE, $parsedRenderable, $renderingContext);
			$output = $parsedRenderable->render($renderingContext);
			$this->stopRendering();
		}

		return $output;
	}

	protected function getPathAndFilenameForRenderable($renderableType) {
		list($packageKey, $shortRenderableType) = explode(':', $renderableType);
		return sprintf('resource://%s/Private/Form/%s.html', $packageKey, $shortRenderableType);
	}

	protected function getPathAndFilenameForRenderableLayout($renderableType) {
		list($packageKey, $shortRenderableType) = explode(':', $renderableType);
		return sprintf('resource://%s/Private/Form/Layout/%s.html', $packageKey, $shortRenderableType);
	}



	protected function getParsedRenderable($renderableType, $renderablePathAndFilename) {
		$templateModifiedTimestamp = \filemtime($renderablePathAndFilename);
		$renderableIdentifier = sprintf('renderable_%s_%s', str_replace(array('.', ':'), '_', $renderableType), sha1($renderablePathAndFilename . '|' . $templateModifiedTimestamp));

		if ($this->templateCompiler->has($renderableIdentifier)) {
			$parsedRenderable = $this->templateCompiler->get($renderableIdentifier);
		} else {
			$parsedRenderable = $this->templateParser->parse(file_get_contents($renderablePathAndFilename));
			if ($parsedRenderable->isCompilable()) {
				$this->templateCompiler->store($renderableIdentifier, $parsedRenderable);
			}
		}
		return $parsedRenderable;
	}
}
?>