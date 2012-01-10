<?php
namespace TYPO3\Form\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Page, being part of a bigger FormDefinition.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
class Page extends AbstractSection {
	/**
	 * Constructor. Needs this Page's identifier
	 *
	 * @param string $identifier The Page's identifier
	 * @param string $type The Page's type
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier, $type = 'TYPO3.Form:Page') {
		parent::__construct($identifier, $type);
	}

	public function setParentRenderable(CompositeRenderableInterface $parentRenderable) {
		if (!($parentRenderable instanceof FormDefinition)) {
			throw new \Exception('TODO: parent renderable ....');
		}
		parent::setParentRenderable($parentRenderable);
	}
}
?>