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

	/**
	 * Pattern to be resolved for "@templateRoot" in the other patterns.
	 * @var string
	 */
	protected $templatePathAndFilename = 'resource://TYPO3.Form/Private/FormMaster.html';

	public function renderRenderable(\TYPO3\Form\Domain\Model\RenderableInterface $renderable) {
		$renderableType = $renderable->getType();

		$renderablePathAndFilename = $this->getPathAndFilenameForRenderable($renderableType);
		$renderableIdentifier = $this->getRenderableIdentifier($renderableType, $renderablePathAndFilename);

		if ($this->templateCompiler->has($renderableIdentifier)) {
			$parsedRenderable = $this->templateCompiler->get($renderableIdentifier);
		} else {
			$parsedRenderable = $this->templateParser->parse(file_get_contents($renderablePathAndFilename));
			if ($parsedRenderable->isCompilable()) {
				$this->templateCompiler->store($renderableIdentifier, $parsedRenderable);
			}
		}

		$variableContainer = $this->objectManager->create('TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer', array($renderable->getTemplateVariableName() => $renderable));
		$renderingContext = clone $this->getCurrentRenderingContext();
		$renderingContext->injectTemplateVariableContainer($variableContainer);

		$this->startRendering(self::RENDERING_PARTIAL, $parsedRenderable, $renderingContext);

		$output = $parsedRenderable->render($renderingContext);

		$this->stopRendering();

		return $output;



	}

	protected function getPathAndFilenameForRenderable($renderableType) {
		list($packageKey, $shortRenderableType) = explode(':', $renderableType);
		return sprintf('resource://%s/Private/Form/%s.html', $packageKey, $shortRenderableType);
	}

	protected function getRenderableIdentifier($renderableType, $renderablePathAndFilename) {
		file_get_contents($renderablePathAndFilename);
		$templateModifiedTimestamp = \filemtime($renderablePathAndFilename);
		return sprintf('renderable_%s_%s', str_replace(array('.', ':'), '_', $renderableType), sha1($renderablePathAndFilename . '|' . $templateModifiedTimestamp));
	}
}
?>