<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * PUBLIC API to render a form
 *
 * @todo document
 */
class RenderViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	public function render($factoryClass) {
		$factory = $this->objectManager->get($factoryClass);

		$formDefinition = $factory->build(array(), 'Default');

		$form = $formDefinition->bind($this->controllerContext->getRequest());

		return $form->render();
	}
}
?>