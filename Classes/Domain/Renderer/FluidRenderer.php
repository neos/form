<?php
namespace TYPO3\Form\Domain\Renderer;

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
class FluidRenderer extends \TYPO3\Fluid\View\AbstractTemplateView implements RendererInterface {

	protected $renderableVariableName = 'element';

	/**
	 * @var \TYPO3\Form\Domain\Model\RenderableInterface
	 */
	protected $renderable;

	public function __construct(\TYPO3\Form\Domain\Model\RenderableInterface $renderable) {
		$this->renderable = $renderable;
	}

	public function render($action = NULL) {
		$this->assign($this->renderableVariableName, $this->renderable);
		return parent::render($action);
	}

	protected function getLayoutIdentifier($layoutName = 'Default') {

	}
	protected function getLayoutSource($layoutName = 'Default') {

	}
	protected function getPartialIdentifier($partialName) {

	}
	protected function getPartialSource($partialName) {

	}
	protected function getTemplateIdentifier($actionName = NULL) {
		return sha1(file_get_contents($this->getTemplatePath()));
	}
	protected function getTemplateSource($actionName = NULL) {
		return file_get_contents($this->getTemplatePath());
	}

	protected function getTemplatePath() {
		list($packageKey, $typeIdentifier) = explode(':', $this->renderable->getType(), 2);
		return strtr('resource://@package/Private/Templates/Form/Default/@type.html', array(
			'@package' => $packageKey,
			'@type' => str_replace('.', '/', $typeIdentifier)
		));
	}

	public function setRenderableVariableName($renderableVariableName) {
		$this->renderableVariableName = $renderableVariableName;
	}

}
?>