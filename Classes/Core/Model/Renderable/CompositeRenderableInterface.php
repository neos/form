<?php
namespace TYPO3\Form\Core\Model\Renderable;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

/**
 * Interface which all Form Elements, and also the FormDefinition and Page
 * must adhere to **when they have sub elements**.
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 *
 */
interface CompositeRenderableInterface extends RenderableInterface {

}
?>