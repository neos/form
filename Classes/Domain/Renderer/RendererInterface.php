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
interface RendererInterface extends \TYPO3\FLOW3\MVC\View\ViewInterface {

	#public function __construct(\TYPO3\Form\Domain\Model\RenderableInterface $renderable);

	public function setRenderableVariableName($variableName);
}
?>