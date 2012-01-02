<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Form element
 */
class GenericFormElement extends AbstractFormElement {
	public function getRenderedContent() {
		$renderer = new \TYPO3\Form\Domain\Renderer\FluidRenderer($this);
		$renderer->setControllerContext($this->getControllerContext());

		// TODO: RendererResolver shall be called HERE
		return $renderer->render();
	}
}
?>