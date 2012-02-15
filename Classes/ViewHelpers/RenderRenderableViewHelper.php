<?php
namespace TYPO3\Form\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Render a renderable
 */
class RenderRenderableViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param \TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable
	 * @return type
	 */
	public function render(\TYPO3\Form\Core\Model\Renderable\RenderableInterface $renderable) {
		return $this->viewHelperVariableContainer->getView()->renderRenderable($renderable);
	}
}
?>