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
class Page extends AbstractCompositeRenderable {


	/**
	 * We know that the parent renderable *must be* a form definition in this case
	 * (as we enforce it in setParentRenderable)
	 *
	 * @var FormDefinition
	 * @internal
	 */
	protected $parentRenderable;

	/**
	 * Constructor. Needs this Page's identifier
	 *
	 * @param string $identifier The Page's identifier
	 * @param string $type The Page's type
	 * @throws \TYPO3\Form\Exception\IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier, $type = 'TYPO3.Form:Page') {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new \TYPO3\Form\Exception\IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}

		$this->identifier = $identifier;
		$this->type = $type;
	}

	public function setParentRenderable(CompositeRenderableInterface $parentRenderable) {
		if (!($parentRenderable instanceof FormDefinition)) {
			throw new \Exception('TODO: parent renderable ....');
		}
		parent::setParentRenderable($parentRenderable);
	}

	/**
	 * Get the Page's Form Elements
	 *
	 * @return array<\TYPO3\Form\Domain\Model\FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElements() {
		return $this->renderables;
	}

	/**
	 * Add a new form element at the end of the page
	 *
	 * @param FormElementInterface $formElement The form element to add
	 * @throws \TYPO3\Form\Exception\FormDefinitionConsistencyException if FormElement is already added to a Page
	 * @api
	 */
	public function addElement(FormElementInterface $formElement) {
		$this->addRenderable($formElement);
	}

	/**
	 *
	 * @param string $identifier
	 * @param string $typeName
	 * @return \TYPO3\Form\Domain\Model\FormElementInterface
	 * @throws \Exception
	 */
	public function createElement($identifier, $typeName) {
		if ($this->parentRenderable === NULL) {
			throw new \TYPO3\Form\Exception\FormDefinitionConsistencyException(sprintf('The page "%s" is not attached to a parent form, thus createElement() cannot be called.', $this->identifier), 1325742259);
		}
		$typeDefinition = $this->parentRenderable->getFormFieldTypeManager()->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new \TYPO3\Form\Exception\TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$element = new $implementationClassName($identifier, $typeName);

		if (isset($typeDefinition['label'])) {
			$element->setLabel($typeDefinition['label']);
		}

		if (isset($typeDefinition['defaultValue'])) {
			$element->setDefaultValue($typeDefinition['defaultValue']);
		}

		if (isset($typeDefinition['properties'])) {
			foreach ($typeDefinition['properties'] as $key => $value) {
				$element->setProperty($key, $value);
			}
		}

		if (isset($typeDefinition['rendererClassName'])) {
			$element->setRendererClassName($typeDefinition['rendererClassName']);
		}

		if (isset($typeDefinition['renderingOptions'])) {
			foreach ($typeDefinition['renderingOptions'] as $key => $value) {
				$element->setRenderingOption($key, $value);
			}
		}

		\TYPO3\Form\Utility\Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label', 'defaultValue', 'properties', 'rendererClassName', 'renderingOptions'));

		$this->addElement($element);
		return $element;
	}

	/**
	 * Move FormElement $element before $referenceElement.
	 *
	 * Both $element and $referenceElement must be direct descendants of this $page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementBefore(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		$this->moveRenderableBefore($elementToMove, $referenceElement);
	}

	/**
	 * Move FormElement $element after $referenceElement
	 *
	 * Both $element and $referenceElement must be direct descendants of this $page.
	 *
	 * @param FormElementInterface $elementToMove
	 * @param FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementAfter(FormElementInterface $elementToMove, FormElementInterface $referenceElement) {
		$this->moveRenderableAfter($elementToMove, $referenceElement);
	}

	/**
	 * Remove $elementToRemove from Page
	 *
	 * @param FormElementInterface $elementToRemove
	 * @api
	 */
	public function removeElement(FormElementInterface $elementToRemove) {
		$this->removeRenderable($elementToRemove);
	}
}
?>